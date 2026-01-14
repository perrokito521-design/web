<?php

function is_owner_or_admin(int $record_id, string $table, string $owner_column, $pdo): bool {
    // Administradores tienen acceso completo
    if ($_SESSION['user_role'] === 'ADMIN') {
        return true;
    }

    // Usuarios regulares deben ser los propietarios
    $stmt = $pdo->prepare(
        "SELECT {$owner_column} FROM {$table} WHERE id = ?"
    );
    $stmt->execute([$record_id]);
    $owner_id = $stmt->fetchColumn();

    return (int)$owner_id === (int)$_SESSION['user_id'];
}

// Variables para la Edición/Actualización
$is_editing = false;
$edit_vehicle_data = [];

// --- A. Lógica CRUD de Vehículos (POST) ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    $action = $_POST['action'];
    
    // 1. Sanear y obtener datos comunes
    $placa = trim($_POST['placa'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $anio = (int)($_POST['anio'] ?? 0);
    $capacidad = (int)($_POST['capacidad'] ?? 0);
    $estado = $_POST['estado'] ?? 'Activo';
    $color = trim($_POST['color'] ?? 'Sin especificar');
    $conductor_id = $_SESSION['user_id'];
    $vehicle_id = (int)($_POST['vehicle_id'] ?? 0); 
    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');
    
    $errors = [];

    // Validaciones
    if ($action === 'add_vehicle' || $action === 'update_vehicle') {
        if (empty($placa) || empty($marca) || empty($modelo) || $anio < 1950 || $capacidad < 1) {
            $errors[] = 'Todos los campos son obligatorios y deben ser válidos.';
        }
        if (!in_array($estado, ['Activo', 'Mantenimiento', 'Inactivo'])) {
            $errors[] = 'Estado no válido.';
        }
    }
    
    if (empty($errors)) {
        try {
            if ($action === 'add_vehicle') {
                // Verificar unicidad de la placa
                $stmt_check = $pdo->prepare('SELECT id FROM vehiculo WHERE placa = ?');
                $stmt_check->execute([$placa]);
                if ($stmt_check->fetch()) {
                    $errors[] = 'Ya existe un vehículo registrado con esta placa.';
                } else {
                    // Inserción
                    $sql = 'INSERT INTO vehiculo 
                        (placa, marca, modelo, anio, capacidad, estado, color, conductor_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$placa, $marca, $modelo, $anio, $capacidad, $estado, $color, $conductor_id]);
                    $_SESSION['success_message'] = 'Vehículo **' . htmlspecialchars($placa) . '** agregado con éxito.';
                    header("Location: vehicles.php");
                    exit();
                }
            
            } elseif ($action === 'update_vehicle' && $vehicle_id > 0) {

                if (!is_owner_or_admin($vehicle_id, 'vehiculo', 'conductor_id', $pdo)) {
                    // Denegar si no es dueño ni ADMIN
                    $_SESSION['error_message'] = "Permiso denegado: No puedes editar este vehículo.";
                    header('Location: vehicles.php');
                    exit();
                }

                // Actualización
                if ($is_admin) {
                    $sql = 'UPDATE vehiculo SET 
                        placa = ?, marca = ?, modelo = ?, anio = ?, capacidad = ?, estado = ?, color = ?
                        WHERE id = ?';
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$placa, $marca, $modelo, $anio, $capacidad, $estado, $color, $vehicle_id]);
                } else {
                    $sql = 'UPDATE vehiculo SET 
                        placa = ?, marca = ?, modelo = ?, anio = ?, capacidad = ?, estado = ?, color = ?
                        WHERE id = ? AND conductor_id = ?';
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$placa, $marca, $modelo, $anio, $capacidad, $estado, $color, $vehicle_id, $conductor_id]);
                }
                
                $_SESSION['success_message'] = 'Vehículo **' . htmlspecialchars($placa) . '** actualizado con éxito.';
                header("Location: vehicles.php");
                exit();
            }

        } catch (PDOException $e) {
            $errors[] = 'Error en la base de datos: ' . htmlspecialchars($e->getMessage());
        }
    }
    
    // Manejo de errores de POST
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        // Si hay error en POST, recargar para mostrar, manteniendo los datos en $_POST
    }
}

// --- B. Lógica CRUD de Vehículos (GET - Cargar Edición y Eliminar) ---

// 1. Lógica para CARGAR datos del vehículo a EDITAR (CRUD: UPDATE - GET)
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $vehicle_id = (int)$_GET['id'];
    $conductor_id = $_SESSION['user_id'];
    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');
    
    try {
        if ($is_admin) {
            $sql = 'SELECT * FROM vehiculo WHERE id = ?';
            $stmt_edit = $pdo->prepare($sql);
            $stmt_edit->execute([$vehicle_id]);
        } else {
            $sql = 'SELECT * FROM vehiculo WHERE id = ? AND conductor_id = ?';
            $stmt_edit = $pdo->prepare($sql);
            $stmt_edit->execute([$vehicle_id, $conductor_id]);
        }
        $data = $stmt_edit->fetch();

        if ($data) {
            $is_editing = true;
            $edit_vehicle_data = $data;
            // Precargar $_POST para que el formulario se llene automáticamente
            $_POST = array_merge($_POST, $data); 
            $_POST['action'] = 'update_vehicle'; 
        } else {
            $_SESSION['error_message'] = 'Vehículo no encontrado o no tienes permiso para editarlo.';
            header("Location: vehicles.php"); // Redirigir si no se encuentra
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error al cargar datos de edición: ' . htmlspecialchars($e->getMessage());
        header("Location: vehicles.php");
        exit();
    }
}

// 2. Lógica para ELIMINAR Vehículos (CRUD: DELETE - GET)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $vehicle_id = (int)$_GET['id'];

    if (!is_owner_or_admin($vehicle_id, 'vehiculo', 'conductor_id', $pdo)) {
        // Denegar si no es dueño ni ADMIN
        $_SESSION['error_message'] = "Permiso denegado: No puedes eliminar este vehículo.";
        header('Location: vehicles.php');
        exit();
    }
    $conductor_id = $_SESSION['user_id'];
    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');
    
    try {
        // Se añade una restricción de seguridad
        if ($is_admin) {
            $sql = 'DELETE FROM vehiculo WHERE id = ?';
            $stmt_delete = $pdo->prepare($sql);
            $stmt_delete->execute([$vehicle_id]);
        } else {
            $sql = 'DELETE FROM vehiculo WHERE id = ? AND conductor_id = ?';
            $stmt_delete = $pdo->prepare($sql);
            $stmt_delete->execute([$vehicle_id, $conductor_id]);
        }

        if ($stmt_delete->rowCount() > 0) {
            $_SESSION['success_message'] = 'Vehículo eliminado con éxito.';
        } else {
            $_SESSION['error_message'] = 'No se pudo eliminar el vehículo o no tienes permiso para hacerlo.';
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error en la base de datos al eliminar: ' . htmlspecialchars($e->getMessage());
    }
    
    header("Location: vehicles.php");
    exit();
}

// --- C. Lógica de Lectura para la Tabla de Listado (Necesaria para vehicles.php) ---

$is_admin = ($_SESSION['user_role'] === 'ADMIN');

$sql = "SELECT v.*, u.email AS conductor_nombre
        FROM vehiculo v
        JOIN usuario u ON v.conductor_id = u.id";

$params = [];
$where_clauses = [];

if (!$is_admin) {
    $where_clauses[] = "v.conductor_id = :user_id";
    $params['user_id'] = $_SESSION['user_id'];
}

// Si es ADMIN, $where_clauses queda vacío y se listan todos los vehículos.

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY v.placa ASC";

$stmt_list = $pdo->prepare($sql);
$stmt_list->execute($params);
$user_vehicles_list = $stmt_list->fetchAll();