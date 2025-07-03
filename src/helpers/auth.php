<?php
/**
 * auth.php
 * Funciones reutilizables para manejo de sesiones y autenticación segura.
 */

// Inicia sesión solo si aún no ha sido iniciada
function startSessionIfNotStarted() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Envía cabeceras para prevenir caché en páginas protegidas
function sendNoCacheHeaders() {
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
}

// Comprueba si el usuario tiene sesión activa
function isLoggedIn() {
    startSessionIfNotStarted();
    return isset($_SESSION['user_id']);
}

// Protege la página: obliga a login y envía cabeceras de no caché
function requireLogin() {
    startSessionIfNotStarted();
    sendNoCacheHeaders();

    if (!isLoggedIn()) {
        header('Location: /inventario-cepa/public/index.html');
        exit;
    }
}

// Cierra la sesión completamente y redirige a login
function logout() {
    startSessionIfNotStarted();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    sendNoCacheHeaders();
    header('Location: /inventario-cepa/public/index.html');
    exit;
}

