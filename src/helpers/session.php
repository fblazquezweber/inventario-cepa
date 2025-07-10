<?php
session_start();

// Duración de la sesión en segundos
$session_duration = 15;

// Última actividad
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

$elapsed_time = time() - $_SESSION['last_activity'];

if ($elapsed_time > $session_duration) {
    session_unset();
    session_destroy();
    header("Location: /public/logout.php");
    exit;
}

// Mantener viva la sesión en cada carga
$_SESSION['last_activity'] = time();
?>

