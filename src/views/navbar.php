<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">
      <i class="fas fa-boxes me-2"></i> Inventario CEPA
    </a>

    <!-- Botón hamburguesa -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContenido"
      aria-controls="navbarContenido" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Contenido colapsable -->
    <div class="collapse navbar-collapse" id="navbarContenido">
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