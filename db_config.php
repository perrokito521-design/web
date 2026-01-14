<?php
$dbHost = '127.0.0.1';
$dbName = 'bschiwa_db';
$dbUser = 'root';
$dbPass = '';

/**
 * Devuelve un objeto PDO para la conexión a la base de datos.
 * @return PDO
 */
function getPDO()
{
    global $dbHost, $dbName, $dbUser, $dbPass;
    static $pdo = null;
    
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // En caso de error crítico de conexión, detenemos la ejecución
        die('Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()));
    }
}
?>