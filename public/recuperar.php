<?php
require_once __DIR__ . '/../src/helpers/db.php';

$mensaje = '';
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if ($email) {
    $pdo = getDbConnection();

    // Buscar usuario activo por email
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE LOWER(email) = :email AND activo = 1");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Generar nueva contraseña aleatoria
        $nueva = generarContrasenaProvisional();

        // Hashear y actualizar
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET contrasena_hash = :hash WHERE id_usuario = :id");
        $stmt->execute(['hash' => $hash, 'id' => $usuario['id_usuario']]);

        // Mostrar mensaje directamente (sin intentar enviar mail)
        $mensaje = "⚠️ Estás en entorno local. La nueva contraseña provisional es: 
        <strong>$nueva</strong><br>Úsala para iniciar sesión y cámbiala luego.";
        $exito = true;

    } else {
        $mensaje = "❌ No se encontró un usuario activo con ese correo.";
    }
} else {
    $mensaje = "❌ Debes ingresar un correo válido.";
}


    
}


// Función para generar contraseña aleatoria
function generarContrasenaProvisional(int $longitud = 8): string {
    $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    return substr(str_shuffle(str_repeat($caracteres, 5)), 0, $longitud);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar Contraseña</title>
  <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/vendor/all.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
  <div class="card shadow-sm p-4" style="width: 100%; max-width: 420px;">
    <h5 class="text-center mb-3"><i class="fas fa-key text-primary me-1"></i> Recuperar contraseña</h5>

    <?php if ($mensaje): ?>
      <div class="alert <?= $exito ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
        <?= $mensaje ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    <?php endif; ?>

    <form method="POST" class="mb-3">
      <div class="mb-3">
        <label class="form-label">Correo electrónico</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-paper-plane me-1"></i> Enviar nueva contraseña
        </button>
      </div>
    </form>

    <div class="text-center small">
      <a href="index.php"><i class="fas fa-arrow-left me-1"></i>Volver al login</a>
    </div>
  </div>

  <script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
  <script src="assets/js/vendor/all.min.js"></script>
</body>
</html>
