<?php

function is_solvencia_owner_or_admin(int $record_id, string $table, string $owner_column, $pdo): bool {
    // Administradores tienen acceso completo
    if ($_SESSION['user_role'] === 'ADMIN') {
        return true;
    }

    // Usuarios regulares deben ser los propietarios
    $stmt = $pdo->prepare("SELECT {$owner_column} FROM {$table} WHERE id = ?");
    $stmt->execute([$record_id]);
    $owner_id = $stmt->fetchColumn();

    return (int)$owner_id === (int)$_SESSION['user_id'];
}

// Variables para la Edición/Actualización
$is_editing_financiera = false;
$edit_financiera_data = [];
$is_editing_fiscal = false;
$edit_fiscal_data = [];

// --- A. Lógica CRUD de Solvencia Financiera (POST) ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    $action = $_POST['action'];

    // Lógica para Solvencia Financiera
    if ($action === 'add_financiera' || $action === 'update_financiera') {
        $anio = (int)($_POST['anio'] ?? date('Y'));
        $mes = (int)($_POST['mes'] ?? 0);
        $estado = $_POST['estado'] ?? 'PENDIENTE';
        $monto = (float)($_POST['monto'] ?? 0.00);
        $fecha_pago = trim($_POST['fecha_pago'] ?? '');
        $usuario_id = $_SESSION['user_id'];
        $financiera_id = (int)($_POST['financiera_id'] ?? 0);
        $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');

        $errors = [];

        // Validaciones
        if ($mes < 1 || $mes > 12) {
            $errors[] = 'El mes debe estar entre 1 y 12.';
        }
        if ($anio < 2020 || $anio > (date('Y') + 1)) {
            $errors[] = 'El año debe ser válido.';
        }
        if (!in_array($estado, ['PENDIENTE', 'PAGADO', 'ATRASADO'])) {
            $errors[] = 'Estado no válido.';
        }
        if ($monto < 0) {
            $errors[] = 'El monto no puede ser negativo.';
        }
        if (!empty($fecha_pago) && !DateTime::createFromFormat('Y-m-d', $fecha_pago)) {
            $errors[] = 'Fecha de pago no válida.';
        }

        if (empty($errors)) {
            try {
                if ($action === 'add_financiera') {
                    // Verificar unicidad
                    $stmt_check = $pdo->prepare('SELECT id FROM solvencia_financiera WHERE usuario_id = ? AND anio = ? AND mes = ?');
                    $stmt_check->execute([$usuario_id, $anio, $mes]);
                    if ($stmt_check->fetch()) {
                        $errors[] = 'Ya existe un registro para este mes y año.';
                    } else {
                        $sql = 'INSERT INTO solvencia_financiera 
                            (usuario_id, anio, mes, estado, monto, fecha_pago) 
                            VALUES (?, ?, ?, ?, ?, ?)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$usuario_id, $anio, $mes, $estado, $monto, $fecha_pago ?: null]);
                        $_SESSION['success_message'] = 'Registro financiero agregado con éxito.';
                    }
                } elseif ($action === 'update_financiera' && $financiera_id > 0) {
                    if (!is_solvencia_owner_or_admin($financiera_id, 'solvencia_financiera', 'usuario_id', $pdo)) {
                        $_SESSION['error_message'] = "Permiso denegado: No puedes editar este registro.";
                        header('Location: solvencia.php');
                        exit();
                    }

                    $sql = 'UPDATE solvencia_financiera SET 
                        anio = ?, mes = ?, estado = ?, monto = ?, fecha_pago = ?
                        WHERE id = ?';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$anio, $mes, $estado, $monto, $fecha_pago ?: null, $financiera_id]);
                    $_SESSION['success_message'] = 'Registro financiero actualizado con éxito.';
                }
                
                header("Location: solvencia.php");
                exit();

            } catch (PDOException $e) {
                $errors[] = 'Error en la base de datos: ' . htmlspecialchars($e->getMessage());
            }
        }

        if (!empty($errors)) {
            $_SESSION['error_message'] = implode('<br>', $errors);
        }
    }

    // Lógica para Solvencia Fiscal
    if ($action === 'add_fiscal' || $action === 'update_fiscal') {
        $dia_semana = (int)($_POST['dia_semana'] ?? 0);
        $semana = trim($_POST['semana'] ?? '');
        $estado = $_POST['estado'] ?? 'PENDIENTE';
        $observaciones = trim($_POST['observaciones'] ?? '');
        $usuario_id = $_SESSION['user_id'];
        $fiscal_id = (int)($_POST['fiscal_id'] ?? 0);
        $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');

        $errors = [];

        // Validaciones
        if ($dia_semana < 1 || $dia_semana > 7) {
            $errors[] = 'El día de la semana debe estar entre 1 y 7.';
        }
        if (!preg_match('/^\d{4}-\d{2}$/', $semana)) {
            $errors[] = 'El formato de semana debe ser YYYY-WW (ej: 2024-01).';
        }
        if (!in_array($estado, ['PENDIENTE', 'CUMPLIDO', 'AUSENTE'])) {
            $errors[] = 'Estado no válido.';
        }

        if (empty($errors)) {
            try {
                if ($action === 'add_fiscal') {
                    // Verificar unicidad
                    $stmt_check = $pdo->prepare('SELECT id FROM solvencia_fiscal WHERE usuario_id = ? AND dia_semana = ? AND semana = ?');
                    $stmt_check->execute([$usuario_id, $dia_semana, $semana]);
                    if ($stmt_check->fetch()) {
                        $errors[] = 'Ya existe un registro para este día y semana.';
                    } else {
                        $sql = 'INSERT INTO solvencia_fiscal 
                            (usuario_id, dia_semana, semana, estado, observaciones) 
                            VALUES (?, ?, ?, ?, ?)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$usuario_id, $dia_semana, $semana, $estado, $observaciones]);
                        $_SESSION['success_message'] = 'Registro fiscal agregado con éxito.';
                    }
                } elseif ($action === 'update_fiscal' && $fiscal_id > 0) {
                    if (!is_solvencia_owner_or_admin($fiscal_id, 'solvencia_fiscal', 'usuario_id', $pdo)) {
                        $_SESSION['error_message'] = "Permiso denegado: No puedes editar este registro.";
                        header('Location: solvencia.php');
                        exit();
                    }

                    $sql = 'UPDATE solvencia_fiscal SET 
                        dia_semana = ?, semana = ?, estado = ?, observaciones = ?
                        WHERE id = ?';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$dia_semana, $semana, $estado, $observaciones, $fiscal_id]);
                    $_SESSION['success_message'] = 'Registro fiscal actualizado con éxito.';
                }
                
                header("Location: solvencia.php");
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

// Cargar edición financiera
if (isset($_GET['action']) && $_GET['action'] === 'edit_financiera' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $financiera_id = (int)$_GET['id'];
    $usuario_id = $_SESSION['user_id'];
    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');
    
    try {
        if ($is_admin) {
            $sql = 'SELECT * FROM solvencia_financiera WHERE id = ?';
            $stmt_edit = $pdo->prepare($sql);
            $stmt_edit->execute([$financiera_id]);
        } else {
            $sql = 'SELECT * FROM solvencia_financiera WHERE id = ? AND usuario_id = ?';
            $stmt_edit = $pdo->prepare($sql);
            $stmt_edit->execute([$financiera_id, $usuario_id]);
        }
        $data = $stmt_edit->fetch();

        if ($data) {
            $is_editing_financiera = true;
            $edit_financiera_data = $data;
            $_POST = array_merge($_POST, $data);
            $_POST['action'] = 'update_financiera';
        } else {
            $_SESSION['error_message'] = 'Registro no encontrado o no autorizado.';
            header("Location: solvencia.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error al cargar datos: ' . htmlspecialchars($e->getMessage());
        header("Location: solvencia.php");
        exit();
    }
}

// Cargar edición fiscal
if (isset($_GET['action']) && $_GET['action'] === 'edit_fiscal' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $fiscal_id = (int)$_GET['id'];
    $usuario_id = $_SESSION['user_id'];
    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');
    
    try {
        if ($is_admin) {
            $sql = 'SELECT * FROM solvencia_fiscal WHERE id = ?';
            $stmt_edit = $pdo->prepare($sql);
            $stmt_edit->execute([$fiscal_id]);
        } else {
            $sql = 'SELECT * FROM solvencia_fiscal WHERE id = ? AND usuario_id = ?';
            $stmt_edit = $pdo->prepare($sql);
            $stmt_edit->execute([$fiscal_id, $usuario_id]);
        }
        $data = $stmt_edit->fetch();

        if ($data) {
            $is_editing_fiscal = true;
            $edit_fiscal_data = $data;
            $_POST = array_merge($_POST, $data);
            $_POST['action'] = 'update_fiscal';
        } else {
            $_SESSION['error_message'] = 'Registro no encontrado o no autorizado.';
            header("Location: solvencia.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error al cargar datos: ' . htmlspecialchars($e->getMessage());
        header("Location: solvencia.php");
        exit();
    }
}

// Eliminar registros
if (isset($_GET['action']) && ($_GET['action'] === 'delete_financiera' || $_GET['action'] === 'delete_fiscal') && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $record_id = (int)$_GET['id'];
    $table = $_GET['action'] === 'delete_financiera' ? 'solvencia_financiera' : 'solvencia_fiscal';
    
    if (!is_solvencia_owner_or_admin($record_id, $table, 'usuario_id', $pdo)) {
        $_SESSION['error_message'] = "Permiso denegado: No puedes eliminar este registro.";
        header('Location: solvencia.php');
        exit;
    }
    
    try {
        $sql = "DELETE FROM {$table} WHERE id = ?";
        $stmt_delete = $pdo->prepare($sql);
        $stmt_delete->execute([$record_id]);

        if ($stmt_delete->rowCount() > 0) {
            $_SESSION['success_message'] = 'Registro eliminado con éxito.';
        } else {
            $_SESSION['error_message'] = 'No se pudo eliminar el registro.';
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error al eliminar: ' . htmlspecialchars($e->getMessage());
    }
    
    header("Location: solvencia.php");
    exit();
}

// --- C. Lógica de Lectura para Listados ---

$is_admin = ($_SESSION['user_role'] === 'ADMIN');

// Solvencia Financiera
$sql_financiera = "SELECT sf.*, u.email AS usuario_email
        FROM solvencia_financiera sf
        JOIN usuario u ON sf.usuario_id = u.id";

$params_financiera = [];
$where_clauses_financiera = [];

if (!$is_admin) {
    $where_clauses_financiera[] = "sf.usuario_id = :user_id";
    $params_financiera['user_id'] = $_SESSION['user_id'];
}

if (!empty($where_clauses_financiera)) {
    $sql_financiera .= " WHERE " . implode(" AND ", $where_clauses_financiera);
}

$sql_financiera .= " ORDER BY sf.anio DESC, sf.mes DESC";

$stmt_financiera = $pdo->prepare($sql_financiera);
$stmt_financiera->execute($params_financiera);
$financiera_records = $stmt_financiera->fetchAll();

// Solvencia Fiscal
$sql_fiscal = "SELECT sf.*, u.email AS usuario_email
        FROM solvencia_fiscal sf
        JOIN usuario u ON sf.usuario_id = u.id";

$params_fiscal = [];
$where_clauses_fiscal = [];

if (!$is_admin) {
    $where_clauses_fiscal[] = "sf.usuario_id = :user_id";
    $params_fiscal['user_id'] = $_SESSION['user_id'];
}

if (!empty($where_clauses_fiscal)) {
    $sql_fiscal .= " WHERE " . implode(" AND ", $where_clauses_fiscal);
}

$sql_fiscal .= " ORDER BY sf.semana DESC, sf.dia_semana ASC";

$stmt_fiscal = $pdo->prepare($sql_fiscal);
$stmt_fiscal->execute($params_fiscal);
$fiscal_records = $stmt_fiscal->fetchAll();

// Estadísticas para Dashboard
$solvencia_stats = [
    'financiera_pagados' => 0,
    'financiera_pendientes' => 0,
    'financiera_atrasados' => 0,
    'fiscal_cumplidos' => 0,
    'fiscal_pendientes' => 0,
    'fiscal_ausentes' => 0
];

if ($is_admin) {
    // Contar estados financieros
    $sql_financiera_stats = "SELECT estado, COUNT(*) as count FROM solvencia_financiera GROUP BY estado";
    $stmt_financiera_stats = $pdo->prepare($sql_financiera_stats);
    $stmt_financiera_stats->execute();
    $financiera_stats_data = $stmt_financiera_stats->fetchAll();
    
    foreach ($financiera_stats_data as $row) {
        $solvencia_stats['financiera_' . strtolower($row['estado'])] = (int)$row['count'];
    }
    
    // Contar estados fiscales
    $sql_fiscal_stats = "SELECT estado, COUNT(*) as count FROM solvencia_fiscal GROUP BY estado";
    $stmt_fiscal_stats = $pdo->prepare($sql_fiscal_stats);
    $stmt_fiscal_stats->execute();
    $fiscal_stats_data = $stmt_fiscal_stats->fetchAll();
    
    foreach ($fiscal_stats_data as $row) {
        $solvencia_stats['fiscal_' . strtolower($row['estado'])] = (int)$row['count'];
    }
}
?>
