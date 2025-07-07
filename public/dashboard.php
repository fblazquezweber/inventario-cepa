<?php
require_once __DIR__ . '/../src/helpers/auth.php';

// Verificar login y timeout
requireLogin();
checkSessionTimeout();

// Renovar actividad
renewSessionActivity();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - Inventario CEPA</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/vendor/all.min.css">
    <link rel="stylesheet" href="assets/css/custom/estilos.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-boxes me-2"></i>
                Inventario CEPA
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
                            <i class="fas fa-sign-out-alt me-1"></i>
                            Cerrar sesión
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
                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                        Dashboard
                    </h1>
                    <div class="badge bg-success">
                        <i class="fas fa-circle me-1"></i>
                        <span id="session-timer">Sesión activa (60:00)</span>
                        <!--Sesión activa (60 min) PRUEBA Sesión activa (60 min) -->
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Bienvenido, <?= htmlspecialchars($_SESSION['user_name']); ?>!</strong>
                    <br>
                    Su sesión se mantendrá activa por 60 minutos de inactividad. El sistema le avisará 1 minuto antes de expirar.
                </div>
                
                <div class="row">
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-box fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Inventario</h5>
                                <p class="card-text">Gestión de productos y recursos</p>
                                <a href="#" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>
                                    Ver inventario
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                                <h5 class="card-title">Reportes</h5>
                                <p class="card-text">Estadísticas y análisis</p>
                                <a href="#" class="btn btn-success">
                                    <i class="fas fa-chart-bar me-1"></i>
                                    Ver reportes
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
                                <a href="#" class="btn btn-warning">
                                    <i class="fas fa-user-cog me-1"></i>
                                    Gestionar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div class="modal fade" id="sessionWarningModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Sesión por Expirar
                </h5>
            </div>
            <div class="modal-body text-center">
                <p>Tu sesión expirará en:</p>
                <h3 class="text-danger" id="timeRemaining"></h3>
                <p class="text-muted">¿Deseas continuar en la sesión?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" id="logoutSessionBtn">Cerrar Sesión</button>
                <button type="button" class="btn btn-primary" id="renewSessionBtn">
                    <i class="fas fa-sync-alt me-1"></i>Continuar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sessionExpiredModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle me-2"></i>Sesión Expirada
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3"><i class="fas fa-clock text-danger" style="font-size: 3rem;"></i></div>
                <h5>Tu sesión ha expirado por inactividad.</h5>
                <p class="text-muted mb-0">Serás redirigido al inicio de sesión.</p>
            </div>
        </div>
    </div>
</div>
    

    <!-- Bootstrap JS -->
    <script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="assets/js/vendor/all.min.js"></script>
    
    <!-- Scripts personalizados <script src="assets/js/custom/session-timer.js"></script> -->
    <script src="assets/js/custom/disable-bfcache.js"></script>
    <script src="assets/js/custom/session-timeout.js"></script>
    
</body>
</html>