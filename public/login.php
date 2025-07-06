<?php
// login.php
// Procesa la autenticación del usuario con timeout simple
session_start();

// Cargar helpers
require_once __DIR__ . '/../src/helpers/db.php';
require_once __DIR__ . '/../src/helpers/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        die('Debe enviar email y contraseña.');
    }

    $pdo = getDbConnection();

    // Buscar usuario activo por email
    $stmt = $pdo->prepare('SELECT id_usuario, email, contrasena_hash, nombre, apellido FROM Usuarios WHERE LOWER(email) = ? AND activo = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('Usuario no encontrado o inactivo.');
    }

    // Verificar contraseña
    if (!password_verify($password, $user['contrasena_hash'])) {
        die('Contraseña incorrecta.');
    }

    // Regenerar ID de sesión para evitar fijación de sesión
    session_regenerate_id(true);

    // Guardar datos en sesión
    $_SESSION['user_id'] = $user['id_usuario'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['nombre'];
    
    // Inicializar timeout de sesión (60 minutos)
    initSessionTimeout();

    // Forzar no-caché en respuesta de login
    sendNoCacheHeaders();

    // Redirigir a dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // Acceso directo sin POST: redirigir a formulario
    header('Location: index.html');
    exit;
}
?>