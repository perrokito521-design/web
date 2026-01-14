<?php
session_start();

// Destruir todos los datos de la sesión
session_destroy();

// Eliminar cookies de sesión si existen
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
    unset($_COOKIE[session_name()]);
}

// Limpiar variables de sesión
$_SESSION = array();

// Redirigir al login
header("Location: index.php");
exit();
?>
