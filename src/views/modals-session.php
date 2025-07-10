<!-- modals-session.php -->

<div class="modal fade" id="sessionWarningModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">
          <i class="fas fa-exclamation-triangle me-2"></i> Sesión por Expirar
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
          <i class="fas fa-sync-alt me-1"></i> Continuar
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
          <i class="fas fa-times-circle me-2"></i> Sesión Expirada
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
