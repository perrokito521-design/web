<?php

// Función para verificar si el email ya existe (excepto para el usuario actual)
function email_exists($email, $pdo, $exclude_user_id = null) {
    $sql = "SELECT id FROM usuario WHERE email = ?";
    $params = [$email];
    
    if ($exclude_user_id) {
        $sql .= " AND id != ?";
        $params[] = $exclude_user_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() !== false;
}

// Función para validar contraseña
function validate_password($password) {
    if (strlen($password) < 6) {
        return "La contraseña debe tener al menos 6 caracteres.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "La contraseña debe tener al menos una letra mayúscula.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "La contraseña debe tener al menos una letra minúscula.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "La contraseña debe tener al menos un número.";
    }
    return true;
}

// Función para hashear contraseña
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Función para verificar contraseña
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Variables para la Edición/Actualización
$is_editing_user = false;
$edit_user_data = [];

// --- A. Lógica CRUD de Usuarios (POST) ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    $action = $_POST['action'];

    // Lógica para Crear/Actualizar Usuario
    if ($action === 'add_user' || $action === 'update_user') {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');
        $rol = $_POST['rol'] ?? 'AFILIADO';
        $user_id = (int)($_POST['user_id'] ?? 0);
        
        $errors = [];

        // Validaciones
        if (empty($email)) {
            $errors[] = 'El email es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido.';
        } elseif (email_exists($email, $pdo, $action === 'update_user' ? $user_id : null)) {
            $errors[] = 'El email ya está registrado.';
        }

        if ($action === 'add_user') {
            if (empty($password)) {
                $errors[] = 'La contraseña es obligatoria.';
            } else {
                $password_validation = validate_password($password);
                if ($password_validation !== true) {
                    $errors[] = $password_validation;
                }
            }
        } elseif ($action === 'update_user' && !empty($password)) {
            $password_validation = validate_password($password);
            if ($password_validation !== true) {
                $errors[] = $password_validation;
            }
        }

        if ($password !== $confirm_password) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        if (!in_array($rol, ['ADMIN', 'SOCIO', 'AFILIADO'])) {
            $errors[] = 'Rol no válido.';
        }

        if (empty($errors)) {
            try {
                if ($action === 'add_user') {
                    $hashed_password = hash_password($password);
                    $sql = 'INSERT INTO usuario (email, password_hash, rol) VALUES (?, ?, ?)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$email, $hashed_password, $rol]);
                    $_SESSION['success_message'] = 'Usuario creado con éxito.';
                } elseif ($action === 'update_user' && $user_id > 0) {
                    // Solo ADMIN puede editar otros usuarios
                    if ($_SESSION['user_role'] !== 'ADMIN' && $user_id !== $_SESSION['user_id']) {
                        $_SESSION['error_message'] = "Permiso denegado: No puedes editar este usuario.";
                        header('Location: usuarios.php');
                        exit();
                    }
                    
                    if (!empty($password)) {
                        $hashed_password = hash_password($password);
                        $sql = 'UPDATE usuario SET email = ?, password_hash = ?, rol = ? WHERE id = ?';
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$email, $hashed_password, $rol, $user_id]);
                    } else {
                        $sql = 'UPDATE usuario SET email = ?, rol = ? WHERE id = ?';
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$email, $rol, $user_id]);
                    }
                    
                    $_SESSION['success_message'] = 'Usuario actualizado con éxito.';
                }
                
                header("Location: usuarios.php");
                exit();

            } catch (PDOException $e) {
                $errors[] = 'Error en la base de datos: ' . htmlspecialchars($e->getMessage());
            }
        }

        if (!empty($errors)) {
            $_SESSION['error_message'] = implode('<br>', $errors);
        }
    }
}

// --- B. Lógica CRUD (GET - Cargar Edición y Eliminar) ---

// Cargar edición de usuario
if (isset($_GET['action']) && $_GET['action'] === 'edit_user' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Solo ADMIN puede editar otros usuarios, o el usuario puede editarse a sí mismo
    if ($_SESSION['user_role'] !== 'ADMIN' && $user_id !== $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Permiso denegado: No puedes editar este usuario.";
        header("Location: usuarios.php");
        exit();
    }
    
    try {
        $sql = 'SELECT id, email, rol, created_at FROM usuario WHERE id = ?';
        $stmt_edit = $pdo->prepare($sql);
        $stmt_edit->execute([$user_id]);
        $data = $stmt_edit->fetch();

        if ($data) {
            $is_editing_user = true;
            $edit_user_data = $data;
            $_POST = array_merge($_POST, $data);
            $_POST['action'] = 'update_user';
        } else {
            $_SESSION['error_message'] = 'Usuario no encontrado.';
            header("Location: usuarios.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error al cargar datos: ' . htmlspecialchars($e->getMessage());
        header("Location: usuarios.php");
        exit();
    }
}

// Eliminar usuario
if (isset($_GET['action']) && $_GET['action'] === 'delete_user' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Solo ADMIN puede eliminar usuarios
    if ($_SESSION['user_role'] !== 'ADMIN') {
        $_SESSION['error_message'] = "Permiso denegado: Solo los administradores pueden eliminar usuarios.";
        header('Location: usuarios.php');
        exit();
    }
    
    // No permitir eliminar el usuario actual
    if ($user_id === $_SESSION['user_id']) {
        $_SESSION['error_message'] = "No puedes eliminar tu propio usuario.";
        header('Location: usuarios.php');
        exit();
    }
    
    try {
        // Verificar si el usuario tiene vehículos asociados
        $stmt_vehicles = $pdo->prepare('SELECT COUNT(*) FROM vehiculo WHERE conductor_id = ?');
        $stmt_vehicles->execute([$user_id]);
        $vehicle_count = $stmt_vehicles->fetchColumn();
        
        if ($vehicle_count > 0) {
            $_SESSION['error_message'] = "No se puede eliminar el usuario. Tiene {$vehicle_count} vehículo(s) asociado(s).";
        } else {
            $sql = "DELETE FROM usuario WHERE id = ?";
            $stmt_delete = $pdo->prepare($sql);
            $stmt_delete->execute([$user_id]);

            if ($stmt_delete->rowCount() > 0) {
                $_SESSION['success_message'] = 'Usuario eliminado con éxito.';
            } else {
                $_SESSION['error_message'] = 'No se pudo eliminar el usuario.';
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error al eliminar: ' . htmlspecialchars($e->getMessage());
    }
    
    header("Location: usuarios.php");
    exit();
}

// --- C. Lógica de Lectura para Listados ---

$is_admin = ($_SESSION['user_role'] === 'ADMIN');

// Obtener lista de usuarios
$sql_users = "SELECT id, email, rol, created_at FROM usuario ORDER BY created_at DESC";

$params_users = [];
$where_clauses_users = [];

if (!$is_admin) {
    // Si no es admin, solo puede verse a sí mismo
    $where_clauses_users[] = "id = :user_id";
    $params_users['user_id'] = $_SESSION['user_id'];
}

if (!empty($where_clauses_users)) {
    $sql_users .= " WHERE " . implode(" AND ", $where_clauses_users);
}

$stmt_users = $pdo->prepare($sql_users);
$stmt_users->execute($params_users);
$users_list = $stmt_users->fetchAll();

// Estadísticas para Dashboard
$user_stats = [
    'total_users' => 0,
    'total_admins' => 0,
    'total_socios' => 0,
    'total_afiliados' => 0,
    'users_this_month' => 0
];

if ($is_admin) {
    // Contar usuarios por rol
    $sql_role_stats = "SELECT rol, COUNT(*) as count FROM usuario GROUP BY rol";
    $stmt_role_stats = $pdo->prepare($sql_role_stats);
    $stmt_role_stats->execute();
    $role_stats_data = $stmt_role_stats->fetchAll();
    
    foreach ($role_stats_data as $row) {
        $user_stats['total_users'] += (int)$row['count'];
        switch ($row['rol']) {
            case 'ADMIN':
                $user_stats['total_admins'] = (int)$row['count'];
                break;
            case 'SOCIO':
                $user_stats['total_socios'] = (int)$row['count'];
                break;
            case 'AFILIADO':
                $user_stats['total_afiliados'] = (int)$row['count'];
                break;
        }
    }
    
    // Contar usuarios creados este mes
    $sql_month_stats = "SELECT COUNT(*) as count FROM usuario WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
    $stmt_month_stats = $pdo->prepare($sql_month_stats);
    $stmt_month_stats->execute();
    $month_stats_data = $stmt_month_stats->fetch();
    $user_stats['users_this_month'] = (int)$month_stats_data['count'];
}
?>
