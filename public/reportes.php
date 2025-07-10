<?php
require_once __DIR__ . '/../src/helpers/session.php'; // Control de tiempo inactivo
require_once __DIR__ . '/../src/helpers/auth.php';    // Verifica login
requireLogin();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reportes - Inventario CEPA</title>
  <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/vendor/all.min.css">
  <link rel="stylesheet" href="assets/css/custom/estilos.css">
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">
      <i class="fas fa-boxes me-2"></i> Inventario CEPA
    </a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <span class="nav-link">
            <i class="fas fa-user me-1"></i>
            <?= htmlspecialchars($_SESSION['user_name']); ?>
          </span>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">
            <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<main class="container">
  <h1 class="h3 mb-4">
    <i class="fas fa-chart-line text-success me-2"></i> Reportes
  </h1>

  <div class="alert alert-info">
    Aquí irían tus gráficas, tablas de reportes o estadísticas.
  </div>

  <a href="dashboard.php" class="btn btn-secondary">
    <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
  </a>
</main>

<?php include __DIR__ . '/../src/views/modals-session.php'; ?>

<script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
<script src="assets/js/vendor/all.min.js"></script>
<script src="assets/js/custom/disable-bfcache.js"></script>
<script>
  window.serverTimeRemaining = <?= ($session_duration - $elapsed_time) ?>;
</script>
<script src="assets/js/custom/session-timeout.js"></script>
</body>
</html>
