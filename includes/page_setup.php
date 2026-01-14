<?php
session_start();

// *****************************************************************
// 3. ADICIÓN CLAVE: Función Simplificada de Verificación de Sesión
// *****************************************************************

/**
 * Verifica si el usuario tiene una sesión activa
 * Si no tiene sesión, redirige al login
 */
function require_session() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "Tu sesión ha expirado. Por favor inicia sesión nuevamente.";
        header("Location: index.php");
        exit();
    }
}

// Inclusión de la configuración de la base de datos
require_once 'db_config.php'; 

// 1. Verificar que el usuario inició sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

try {
    $pdo = getPDO();
} catch (Exception $e) {
    die('<p style="color:red; text-align:center;">Error al obtener la conexión a la BD.</p>');
}

// Inicialización de variables de mensajes (para pasar entre archivos)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;

// Limpiar mensajes después de cargarlos
unset($_SESSION['success_message'], $_SESSION['error_message']);

// *****************************************************************
// 2. ADICIÓN CLAVE: Función Centralizada de Verificación de Roles
// *****************************************************************

/**
 * Verifica si el rol del usuario actual tiene permiso.
 * Si no lo tiene, redirige a una página segura (ej: dashboard).
 * @param array $allowed_roles Arreglo de strings con los roles permitidos (ej: ['ADMIN', 'SOCIO']).
 * @param string $redirect_page La página a la que redirigir si no hay permiso.
 */
function check_permission(array $allowed_roles, string $redirect_page = 'dashboard.php') {
    // Si el rol NO está definido en la sesión, forzar el cierre de sesión por seguridad.
    if (!isset($_SESSION['user_role'])) {
        // Asumiendo que esta acción redirigirá al login
        header('Location: index.php?action=logout'); 
        exit;
    }

    $current_role = $_SESSION['user_role'];
    
    // Comprobar si el rol actual está en la lista de roles permitidos
    if (!in_array($current_role, $allowed_roles)) {
        // Si no tiene permiso, redirigir y terminar la ejecución
        $_SESSION['error_message'] = "Acceso denegado. No tiene permisos para realizar esta acción.";
        header("Location: $redirect_page");
        exit;
    }
}

/**
 * Verifica si el usuario puede gestionar sus propios recursos
 * @param string $resource_type Tipo de recurso ('vehicle', 'maintenance', 'conductor', 'solvencia')
 * @param int $resource_id ID del recurso a verificar
 * @return bool True si puede gestionar el recurso
 */
function can_manage_own_resource(string $resource_type, int $resource_id = null): bool {
    // Los administradores pueden gestionar todo
    if ($_SESSION['user_role'] === 'ADMIN') {
        return true;
    }
    
    // Si no hay ID de recurso, solo verificar rol
    if ($resource_id === null) {
        return in_array($_SESSION['user_role'], ['SOCIO', 'AFILIADO']);
    }
    
    global $pdo;
    
    try {
        switch ($resource_type) {
            case 'vehicle':
                $stmt = $pdo->prepare('SELECT conductor_id FROM vehiculo WHERE id = ?');
                $stmt->execute([$resource_id]);
                $vehicle = $stmt->fetch();
                return $vehicle && (int)$vehicle['conductor_id'] === (int)$_SESSION['user_id'];
                
            case 'maintenance':
                $stmt = $pdo->prepare('
                    SELECT v.conductor_id 
                    FROM mantenimiento m 
                    JOIN vehiculo v ON m.vehiculo_id = v.id 
                    WHERE m.id = ?
                ');
                $stmt->execute([$resource_id]);
                $maintenance = $stmt->fetch();
                return $maintenance && (int)$maintenance['conductor_id'] === (int)$_SESSION['user_id'];
                
            case 'conductor':
                $stmt = $pdo->prepare('SELECT usuario_id FROM conductores WHERE id = ?');
                $stmt->execute([$resource_id]);
                $conductor = $stmt->fetch();
                return $conductor && (int)$conductor['usuario_id'] === (int)$_SESSION['user_id'];
                
            case 'solvencia':
                $stmt = $pdo->prepare('SELECT usuario_id FROM solvencia_financiera WHERE id = ? UNION SELECT usuario_id FROM solvencia_fiscal WHERE id = ?');
                $stmt->execute([$resource_id, $resource_id]);
                $solvencia = $stmt->fetch();
                return $solvencia && (int)$solvencia['usuario_id'] === (int)$_SESSION['user_id'];
                
            default:
                return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Verifica si el usuario puede ver recursos de otros usuarios
 * @return bool True si puede ver recursos de otros
 */
function can_view_others_resources(): bool {
    // Solo los administradores pueden ver todo
    if ($_SESSION['user_role'] === 'ADMIN') {
        return true;
    }
    
    // Los socios pueden ver otros recursos (solo lectura)
    return $_SESSION['user_role'] === 'SOCIO';
}

/**
 * Obtiene el ID del usuario actual
 * @return int|null ID del usuario o null si no hay sesión
 */
function get_current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

// Lógica de Lectura para SELECT de Mantenimiento 
$is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN');

if ($is_admin) {
    $sql_user_vehicles = '
        SELECT 
            v.id, v.placa, v.marca, v.modelo
        FROM 
            vehiculo v
        ORDER BY 
            v.placa ASC';
    
    $stmt_user_vehicles = $pdo->prepare($sql_user_vehicles);
    $stmt_user_vehicles->execute();
    $user_vehicles = $stmt_user_vehicles->fetchAll();
} else {
    $sql_user_vehicles = '
        SELECT 
            id, placa, marca, modelo 
        FROM 
            vehiculo 
        WHERE 
            conductor_id = ? 
        ORDER BY 
            placa ASC';

    $stmt_user_vehicles = $pdo->prepare($sql_user_vehicles);
    $stmt_user_vehicles->execute([$_SESSION['user_id']]);
    $user_vehicles = $stmt_user_vehicles->fetchAll();
}
?>