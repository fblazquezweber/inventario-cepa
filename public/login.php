<?php
session_start();

// Helpers requeridos
require_once __DIR__ . '/../src/helpers/db.php';
require_once __DIR__ . '/../src/helpers/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Validar campos vacíos
    if (!$email || !$password) {
        header('Location: index.php?error=Debe introducir el correo y la contraseña.');
        exit;
    }

    $pdo = getDbConnection();

    // Buscar usuario activo
    $stmt = $pdo->prepare('SELECT id_usuario, email, contrasena_hash, nombre, apellido FROM usuarios WHERE LOWER(email) = ? AND activo = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Usuario no encontrado o inactivo
    if (!$user) {
        header('Location: index.php?error=Usuario no encontrado o inactivo.');
        exit;
    }

    // Contraseña incorrecta
    if (!password_verify($password, $user['contrasena_hash'])) {
        header('Location: index.php?error=Contraseña incorrecta.');
        exit;
    }

    // Regenerar ID de sesión por seguridad
    session_regenerate_id(true);

    // Guardar datos en sesión
    $_SESSION['user_id'] = $user['id_usuario'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['nombre'];

    // Evitar caché en login
    sendNoCacheHeaders();

    // Redirigir al dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // Si accede por GET, redirigir al formulario
    header('Location: index.php');
    exit;
}
