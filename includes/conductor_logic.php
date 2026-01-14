<?php

function is_conductor_owner_or_admin(int $conductor_id, $pdo): bool {
    // Administradores tienen acceso completo
    if ($_SESSION['user_role'] === 'ADMIN') {
        return true;
    }

    // Usuarios regulares deben ser los propietarios
    $stmt = $pdo->prepare('SELECT usuario_id FROM conductores WHERE id = ?');
    $stmt->execute([$conductor_id]);
    $owner_id = $stmt->fetchColumn();

    return (int)$owner_id === (int)$_SESSION['user_id'];
}

// Variables para la Edición/Actualización de Conductores
$is_editing_conductor = false;
$edit_conductor_data = [];

// --- A. Lógica CRUD de Conductores (POST) ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    $action = $_POST['action'];

    // 1. Sanear y obtener datos comunes
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $licencia_tipo = trim($_POST['licencia_tipo'] ?? '');
    $licencia_emision = trim($_POST['licencia_emision'] ?? '');
    $licencia_vencimiento = trim($_POST['licencia_vencimiento'] ?? '');
    $usuario_id = $_SESSION['user_id'];
    $conductor_id = (int)($_POST['conductor_id'] ?? 0); 
    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');

    $errors = [];

    // 2. Validación y Seguridad
    if ($action === 'add_conductor' || $action === 'update_conductor') {
        if (empty($nombre) || empty($apellido) || empty($cedula) || empty($licencia_tipo)) {
            $errors[] = 'Todos los campos marcados son obligatorios.';
        }
        
        // Validar formato de cédula (ej: V-12345678)
        if (!preg_match('/^[VE]-?\d{7,9}$/', $cedula)) {
            $errors[] = 'La cédula debe tener formato V-12345678 o E-12345678.';
        }
        
        // Validar fechas
        $emision_date = DateTime::createFromFormat('Y-m-d', $licencia_emision);
        $vencimiento_date = DateTime::createFromFormat('Y-m-d', $licencia_vencimiento);
        
        if (!$emision_date || !$vencimiento_date) {
            $errors[] = 'Las fechas de licencia no son válidas.';
        } elseif ($vencimiento_date <= $emision_date) {
            $errors[] = 'La fecha de vencimiento debe ser posterior a la de emisión.';
        }
    }
    
    // Validación de unicidad de cédula (solo para nuevos registros)
    if ($action === 'add_conductor' && empty($errors)) {
        $stmt_check = $pdo->prepare('SELECT id FROM conductores WHERE cedula = ?');
        $stmt_check->execute([$cedula]);
        if ($stmt_check->fetch()) {
            $errors[] = 'Ya existe un conductor registrado con esta cédula.';
        }
    }

    if (empty($errors)) {
        try {
            if ($action === 'add_conductor') {
                // Inserción (CREATE)
                $sql = 'INSERT INTO conductores 
                    (nombre, apellido, cedula, licencia_tipo, licencia_emision, licencia_vencimiento, usuario_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)';
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $apellido, $cedula, $licencia_tipo, $licencia_emision, $licencia_vencimiento, $usuario_id]);
                
                $_SESSION['success_message'] = 'Conductor **' . htmlspecialchars($nombre . ' ' . $apellido) . '** agregado con éxito.';
            
            } elseif ($action === 'update_conductor' && $conductor_id > 0) {

                if (!is_conductor_owner_or_admin($conductor_id, $pdo)) {
                    $_SESSION['error_message'] = "Permiso denegado: No puedes editar este conductor.";
                    header('Location: conductores.php');
                    exit();
                }

                // Verificar que la cédula no esté duplicada (excepto el mismo registro)
                $stmt_check = $pdo->prepare('SELECT id FROM conductores WHERE cedula = ? AND id != ?');
                $stmt_check->execute([$cedula, $conductor_id]);
                if ($stmt_check->fetch()) {
                    $errors[] = 'Ya existe otro conductor con esta cédula.';
                } else {
                    // Actualización (UPDATE)
                    if ($is_admin) {
                        $sql = 'UPDATE conductores SET 
                            nombre = ?, apellido = ?, cedula = ?, licencia_tipo = ?, licencia_emision = ?, licencia_vencimiento = ?
                            WHERE id = ?';
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$nombre, $apellido, $cedula, $licencia_tipo, $licencia_emision, $licencia_vencimiento, $conductor_id]);
                    } else {
                        $sql = 'UPDATE conductores SET 
                            nombre = ?, apellido = ?, cedula = ?, licencia_tipo = ?, licencia_emision = ?, licencia_vencimiento = ?
                            WHERE id = ? AND usuario_id = ?';
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$nombre, $apellido, $cedula, $licencia_tipo, $licencia_emision, $licencia_vencimiento, $conductor_id, $usuario_id]);
                    }
                    
                    $_SESSION['success_message'] = 'Conductor **' . htmlspecialchars($nombre . ' ' . $apellido) . '** actualizado con éxito.';
                }
            }
            
            header("Location: conductores.php");
            exit();

        } catch (PDOException $e) {
            $errors[] = 'Error en la base de datos: ' . htmlspecialchars($e->getMessage());
        }
    }

    // Manejo de errores de POST
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
}

// --- B. Lógica CRUD de Conductores (GET - Cargar Edición y Eliminar) ---

// 1. Lógica para CARGAR datos del conductor a EDITAR (CRUD: UPDATE - GET)
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $conductor_id = (int)$_GET['id'];
    $usuario_id = $_SESSION['user_id'];
    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');
    
    try {
        if ($is_admin) {
            $sql = 'SELECT * FROM conductores WHERE id = ?';
            $stmt_edit = $pdo->prepare($sql);
            $stmt_edit->execute([$conductor_id]);
        } else {
            $sql = 'SELECT * FROM conductores WHERE id = ? AND usuario_id = ?';
            $stmt_edit = $pdo->prepare($sql);
            $stmt_edit->execute([$conductor_id, $usuario_id]);
        }
        $data = $stmt_edit->fetch();

        if ($data) {
            $is_editing_conductor = true;
            $edit_conductor_data = $data;
            // Precargar $_POST para llenar el formulario
            $_POST = array_merge($_POST, $data); 
            $_POST['action'] = 'update_conductor'; 
        } else {
            $_SESSION['error_message'] = 'Conductor no encontrado o no autorizado.';
            header("Location: conductores.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error al cargar datos de edición: ' . htmlspecialchars($e->getMessage());
        header("Location: conductores.php");
        exit();
    }
}

// 2. Lógica para ELIMINAR conductores (CRUD: DELETE - GET)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $conductor_id = (int)$_GET['id'];

    if (!is_conductor_owner_or_admin($conductor_id, $pdo)) {
        $_SESSION['error_message'] = "Permiso denegado: No puedes eliminar este conductor.";
        header('Location: conductores.php');
        exit;
    }
    
    $usuario_id = $_SESSION['user_id'];
    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');
    
    try {
        // Verificar si el conductor tiene vehículos asociados
        $stmt_check_vehicles = $pdo->prepare('SELECT COUNT(*) FROM vehiculo WHERE conductor_id = ?');
        $stmt_check_vehicles->execute([$conductor_id]);
        $vehicle_count = $stmt_check_vehicles->fetchColumn();
        
        if ($vehicle_count > 0) {
            $_SESSION['error_message'] = 'No se puede eliminar el conductor porque tiene vehículos asociados.';
        } else {
            // Eliminar conductor
            if ($is_admin) {
                $sql = 'DELETE FROM conductores WHERE id = ?';
                $stmt_delete = $pdo->prepare($sql);
                $stmt_delete->execute([$conductor_id]);
            } else {
                $sql = 'DELETE FROM conductores WHERE id = ? AND usuario_id = ?';
                $stmt_delete = $pdo->prepare($sql);
                $stmt_delete->execute([$conductor_id, $usuario_id]);
            }

            if ($stmt_delete->rowCount() > 0) {
                $_SESSION['success_message'] = 'Conductor eliminado con éxito.';
            } else {
                $_SESSION['error_message'] = 'No se pudo eliminar el conductor o no tienes permiso para hacerlo.';
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error en la base de datos al eliminar el conductor: ' . htmlspecialchars($e->getMessage());
    }
    
    header("Location: conductores.php");
    exit();
}

// --- C. Lógica de Lectura para la Tabla de Listado (Necesaria para conductores.php) ---

$is_admin = ($_SESSION['user_role'] === 'ADMIN');

$sql = "SELECT c.*, u.email AS usuario_email
        FROM conductores c
        JOIN usuario u ON c.usuario_id = u.id";

$params = [];
$where_clauses = [];

if (!$is_admin) {
    $where_clauses[] = "c.usuario_id = :user_id";
    $params['user_id'] = $_SESSION['user_id'];
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY c.apellido ASC, c.nombre ASC";

$stmt_list = $pdo->prepare($sql);
$stmt_list->execute($params);
$conductores_list = $stmt_list->fetchAll();
?>
