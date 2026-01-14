<?php

function is_maint_owner_or_admin(int $maint_id, $pdo): bool {
    // Administradores tienen acceso completo
    if ($_SESSION['user_role'] === 'ADMIN') {
        return true;
    }

    // Obtener el ID del conductor del vehículo asociado al mantenimiento
    $stmt = $pdo->prepare("
        SELECT v.conductor_id
        FROM mantenimiento m
        JOIN vehiculo v ON m.vehiculo_id = v.id
        WHERE m.id = ?
    ");
    $stmt->execute([$maint_id]);
    $conductor_id = $stmt->fetchColumn();

    // Comparar con el ID del usuario logueado
    return (int)$conductor_id === (int)$_SESSION['user_id'];
}

// Variables para la Edición/Actualización de Mantenimiento
$is_editing_maint = false;
$edit_maint_data = [];

// --- A. Lógica CRUD de Mantenimiento (POST) ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    $action = $_POST['action'];

    // 1. Sanear y obtener datos comunes
    $vehiculo_id = (int)($_POST['vehiculo_id'] ?? 0);
    $fecha_servicio = trim($_POST['fecha_servicio'] ?? '');
    $tipo_servicio = trim($_POST['tipo_servicio'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $costo = (float)($_POST['costo'] ?? 0.00);
    $kilometraje = (int)($_POST['kilometraje'] ?? 0);
    $usuario_id = $_SESSION['user_id'];
    $mantenimiento_id = (int)($_POST['mantenimiento_id'] ?? 0); 
    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');

    $errors = [];

    // 2. Validación y Seguridad (Común a CREATE y UPDATE)
    if ($vehiculo_id === 0 || empty($fecha_servicio) || empty($tipo_servicio) || $costo < 0 || $kilometraje < 0) {
        $errors[] = 'Todos los campos marcados son obligatorios y deben ser válidos.';
    }
    
    // Validación de Seguridad: Asegurar que el vehículo existe y pertenece al usuario (excepto ADMIN)
    if ($vehiculo_id > 0) {
        if ($is_admin) {
            $stmt_check = $pdo->prepare('SELECT id FROM vehiculo WHERE id = ?');
            $stmt_check->execute([$vehiculo_id]);
            if (!$stmt_check->fetch()) {
                 $errors[] = 'Vehículo inválido o no autorizado para esta acción.';
            }
        } else {
            $stmt_check = $pdo->prepare('SELECT id FROM vehiculo WHERE id = ? AND conductor_id = ?');
            $stmt_check->execute([$vehiculo_id, $usuario_id]);
            if (!$stmt_check->fetch()) {
                 $errors[] = 'Vehículo inválido o no autorizado para esta acción.';
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($action === 'add_maintenance') {
                // Inserción (CREATE)
                $sql = 'INSERT INTO mantenimiento 
                    (vehiculo_id, usuario_id, fecha_servicio, tipo_servicio, descripcion, costo, kilometraje) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)';
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$vehiculo_id, $usuario_id, $fecha_servicio, $tipo_servicio, $descripcion, $costo, $kilometraje]);
                
                $_SESSION['success_message'] = 'Registro de mantenimiento agregado con éxito.';
            
            } elseif ($action === 'update_maintenance' && $mantenimiento_id > 0) {

                $mantenimiento_id = (int)$_POST['mantenimiento_id'];
                if (!is_maint_owner_or_admin($mantenimiento_id, $pdo)) {
                    // Denegar si no es dueño ni ADMIN
                    $_SESSION['error_message'] = "Permiso denegado: No puedes editar este registro.";
                    header('Location: maintenance.php');
                    exit();
                }

                // Actualización (UPDATE)
                if ($is_admin) {
                    $sql = 'UPDATE mantenimiento SET 
                        vehiculo_id = ?, fecha_servicio = ?, tipo_servicio = ?, descripcion = ?, costo = ?, kilometraje = ?
                        WHERE id = ?';
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$vehiculo_id, $fecha_servicio, $tipo_servicio, $descripcion, $costo, $kilometraje, $mantenimiento_id]);
                } else {
                    $sql = 'UPDATE mantenimiento SET 
                        vehiculo_id = ?, fecha_servicio = ?, tipo_servicio = ?, descripcion = ?, costo = ?, kilometraje = ?
                        WHERE id = ? AND usuario_id = ?'; // Se valida que el usuario que lo registró sea el mismo
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$vehiculo_id, $fecha_servicio, $tipo_servicio, $descripcion, $costo, $kilometraje, $mantenimiento_id, $usuario_id]);
                }
                
                $_SESSION['success_message'] = 'Registro de mantenimiento actualizado con éxito.';
            }
            
            header("Location: maintenance.php");
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

// --- B. Lógica CRUD de Mantenimiento (GET - Cargar Edición y Eliminar) ---

// 1. Lógica para CARGAR datos del registro a EDITAR (CRUD: UPDATE - GET)
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $mantenimiento_id = (int)$_GET['id'];
    $usuario_id = $_SESSION['user_id'];
    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');
    
    try {
        if ($is_admin) {
            $sql = 'SELECT m.* FROM mantenimiento m WHERE m.id = ?';
            $stmt_edit = $pdo->prepare($sql);
            $stmt_edit->execute([$mantenimiento_id]);
        } else {
            // Se valida que el registro pertenezca a un vehículo del usuario
            $sql = 'SELECT m.* FROM mantenimiento m JOIN vehiculo v ON m.vehiculo_id = v.id WHERE m.id = ? AND v.conductor_id = ?';
            $stmt_edit = $pdo->prepare($sql);
            $stmt_edit->execute([$mantenimiento_id, $usuario_id]);
        }
        $data = $stmt_edit->fetch();

        if ($data) {
            $is_editing_maint = true;
            $edit_maint_data = $data;
            // Precargar $_POST para llenar el formulario
            $_POST = array_merge($_POST, $data); 
            $_POST['action'] = 'update_maintenance'; 
        } else {
            $_SESSION['error_message'] = 'Registro no encontrado o no autorizado.';
            header("Location: maintenance.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error al cargar datos de edición: ' . htmlspecialchars($e->getMessage());
        header("Location: maintenance.php");
        exit();
    }
}

// 2. Lógica para ELIMINAR registros (CRUD: DELETE - GET)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $mantenimiento_id = (int)$_GET['id'];

    if (!is_maint_owner_or_admin($mantenimiento_id, $pdo)) {
        // Denegar si no es dueño ni ADMIN
        $_SESSION['error_message'] = "Permiso denegado: No puedes eliminar este registro.";
        header('Location: maintenance.php');
        exit;
    }
    
    $usuario_id = $_SESSION['user_id'];
    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');
    
    try {
        // Eliminar registro, validando la propiedad del vehículo
        if ($is_admin) {
            $sql = 'DELETE FROM mantenimiento WHERE id = ?';
            $stmt_delete = $pdo->prepare($sql);
            $stmt_delete->execute([$mantenimiento_id]);
        } else {
            $sql = 'DELETE m FROM mantenimiento m JOIN vehiculo v ON m.vehiculo_id = v.id WHERE m.id = ? AND v.conductor_id = ?';
            $stmt_delete = $pdo->prepare($sql);
            $stmt_delete->execute([$mantenimiento_id, $usuario_id]);
        }

        if ($stmt_delete->rowCount() > 0) {
            $_SESSION['success_message'] = 'Registro de mantenimiento eliminado con éxito.';
        } else {
            $_SESSION['error_message'] = 'No se pudo eliminar el registro o no tienes permiso para hacerlo.';
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error en la base de datos al eliminar el mantenimiento: ' . htmlspecialchars($e->getMessage());
    }
    
    header("Location: maintenance.php");
    exit();
}

// --- C. Lógica de Lectura para la Tabla de Listado (Necesaria para maintenance.php) ---

$is_admin = ($_SESSION['user_role'] === 'ADMIN');

$sql = "SELECT m.*, v.placa, v.modelo, u.email AS conductor_nombre
        FROM mantenimiento m
        JOIN vehiculo v ON m.vehiculo_id = v.id
        JOIN usuario u ON v.conductor_id = u.id";

$params = [];
$where_clauses = [];

if (!$is_admin) {
    $where_clauses[] = "v.conductor_id = :user_id";
    $params['user_id'] = $_SESSION['user_id'];
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY m.fecha_servicio DESC";

$stmt_read_maint = $pdo->prepare($sql);
$stmt_read_maint->execute($params);
$maintenance_records = $stmt_read_maint->fetchAll();