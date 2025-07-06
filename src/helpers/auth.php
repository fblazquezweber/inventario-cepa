<?php
// auth.php
// Funciones para manejo de sesiones y autenticación con timeout simple

function startSessionIfNotStarted() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSessionIfNotStarted();
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    startSessionIfNotStarted();
    if (!isLoggedIn()) {
        header('Location: index.html');
        exit;
    }
}

function logout() {
    startSessionIfNotStarted();
    $_SESSION = [];
    session_destroy();
    header('Location: index.html');
    exit;
}

function sendNoCacheHeaders() {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// ===============================
// SISTEMA DE TIMEOUT SIMPLIFICADO
// ===============================
//PRUEBA const SESSION_TIMEOUT = 60 * 60;
// Timeout fijo de 60 minutos para todos los usuarios
const SESSION_TIMEOUT = 15; // 60 minutos en segundos

/**
 * Inicializa el timeout de sesión
 */
function initSessionTimeout() {
    startSessionIfNotStarted();
    
    if (!isLoggedIn()) {
        return;
    }
    
    // Establecer tiempo de última actividad
    $_SESSION['last_activity'] = time();
}

/**
 * Verifica si la sesión ha expirado
 */
function checkSessionTimeout() {
    startSessionIfNotStarted();
    
    if (!isLoggedIn()) {
        return false;
    }
    
    $lastActivity = $_SESSION['last_activity'] ?? time();
    
    // Si ha pasado el tiempo límite, cerrar sesión
    if ((time() - $lastActivity) > SESSION_TIMEOUT) {
        logout();
        return true;
    }
    
    return false;
}

/**
 * Renueva la actividad de la sesión
 */
function renewSessionActivity() {
    startSessionIfNotStarted();
    
    if (isLoggedIn()) {
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Obtiene el tiempo restante de sesión
 */
function getRemainingSessionTime() {
    startSessionIfNotStarted();
    
    if (!isLoggedIn()) {
        return 0;
    }
    
    $lastActivity = $_SESSION['last_activity'] ?? time();
    $remainingTime = SESSION_TIMEOUT - (time() - $lastActivity);
    
    return max(0, $remainingTime);
}

/**
 * Endpoint AJAX para verificar estado de sesión
 */
function handleSessionCheck() {
    header('Content-Type: application/json');
    
    if (!isLoggedIn()) {
        echo json_encode(['status' => 'expired']);
        exit;
    }
    
    $remainingTime = getRemainingSessionTime();
    
    if ($remainingTime <= 0) {
        logout();
        echo json_encode(['status' => 'expired']);
        exit;
    }
    
    echo json_encode([
        'status' => 'active',
        'remaining' => $remainingTime
    ]);
    exit;
}

/**
 * Endpoint AJAX para renovar sesión
 */
function handleSessionRenew() {
    header('Content-Type: application/json');
    
    if (!isLoggedIn()) {
        echo json_encode(['status' => 'expired']);
        exit;
    }
    
    renewSessionActivity();
    $remainingTime = getRemainingSessionTime();
    
    echo json_encode([
        'status' => 'renewed',
        'remaining' => $remainingTime
    ]);
    exit;
}
?>