<?php
// src/helpers/session.php

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar duración máxima de inactividad: 60 minutos
$session_timeout = 15; // segundos PRUEBA

// Inicializa la variable de inicio si no existe
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

// Calcula tiempo de inactividad
$elapsed_time = time() - $_SESSION['last_activity'];

if ($elapsed_time > $session_timeout) {
    // Expira sesión
    session_destroy();
    header('Location: ../public/logout.php');
    exit;
} else {
    // Si no ha expirado, renueva
    $_SESSION['last_activity'] = time();
}

// Variable para JS
$session_duration = $session_timeout;
// $elapsed_time la usas igual que antes
