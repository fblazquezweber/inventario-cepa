<?php
<<<<<<< Updated upstream
require_once __DIR__ . '/../src/helpers/auth.php';
requireLogin();
=======
require_once __DIR__ . '/../src/helpers/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}
>>>>>>> Stashed changes
?>

<!DOCTYPE html>
<html lang="es">
<head>
<<<<<<< Updated upstream
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard</title>
    <!-- Bootstrap CSS CDN -->
    <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/vendor/all.min.css">
    <link rel="stylesheet" href="assets/css/custom/estilos.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">Inventario Cepa</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Hola, <?= htmlspecialchars($_SESSION['user_name']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar sesión</a>
                    </li>
                </ul>
=======
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - Inventario CEPA</title>
  <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/vendor/all.min.css">
  <link rel="stylesheet" href="assets/css/custom/estilos.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="#">
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
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">
          <i class="fas fa-tachometer-alt me-2 text-primary"></i> Dashboard
        </h1>
        <div class="badge bg-success">
          <i class="fas fa-circle me-1"></i>
          Sesión activa
        </div>
      </div>

      <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Bienvenido, <?= htmlspecialchars($_SESSION['user_name']); ?>!</strong><br>
        Puede comenzar a gestionar el inventario, reportes y usuarios desde aquí.
      </div>

      <div class="row">
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="fas fa-box fa-3x text-primary mb-3"></i>
              <h5 class="card-title">Inventario</h5>
              <p class="card-text">Gestión de productos y recursos</p>
              <a href="inventario.php" class="btn btn-primary">
                <i class="fas fa-eye me-1"></i> Ver inventario
              </a>
>>>>>>> Stashed changes
            </div>
        </div>
    </nav>

<<<<<<< Updated upstream
    <main class="container">
        <h1>Dashboard</h1>
        <p>Bienvenido <?= htmlspecialchars($_SESSION['user_name']); ?>, has iniciado sesión correctamente.</p>
        <p>Aquí puedes poner tu contenido privado: inventario, reportes, etc.</p>
    </main>
=======
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
              <h5 class="card-title">Reportes</h5>
              <p class="card-text">Estadísticas y análisis</p>
              <a href="reportes.php" class="btn btn-success">
                <i class="fas fa-chart-bar me-1"></i> Ver reportes
              </a>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="fas fa-users fa-3x text-warning mb-3"></i>
              <h5 class="card-title">Usuarios</h5>
              <p class="card-text">Gestión de accesos</p>
              <a href="usuarios.php" class="btn btn-warning">
                <i class="fas fa-user-cog me-1"></i> Gestionar
              </a>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>

<script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
<script src="assets/js/vendor/all.min.js"></script>
<script src="assets/js/custom/disable-bfcache.js"></script>
>>>>>>> Stashed changes

    <!-- Bootstrap JS Bundle CDN (opcional) -->
    <script src="assets/js/custom/disable-bfcache.js"></script>
</body>
</html>
