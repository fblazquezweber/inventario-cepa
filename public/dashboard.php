<?php
require_once __DIR__ . '/../src/helpers/auth.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="es">
<head>
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
            </div>
        </div>
    </nav>

    <main class="container">
        <h1>Dashboard</h1>
        <p>Bienvenido <?= htmlspecialchars($_SESSION['user_name']); ?>, has iniciado sesión correctamente.</p>
        <p>Aquí puedes poner tu contenido privado: inventario, reportes, etc.</p>
    </main>

    <!-- Bootstrap JS Bundle CDN (opcional) -->
    <script src="assets/js/custom/disable-bfcache.js"></script>
</body>
</html>
