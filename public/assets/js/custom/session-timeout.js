// session-timeout.js
// Control de timeout de sesión simplificado - 60 minutos para todos

class SessionTimeout {
    constructor() {
        this.checkInterval = null;
        this.warningShown = false;
        this.warningModal = null;
        this.expiredModal = null; // Modal de sesión expirada
        this.lastActivity = Date.now();
        this.countdownInterval = null; // Para el contador local
        this.sessionEndTime = null; // Timestamp de cuando expira la sesión
        this.sessionExpired = false; // Flag para evitar múltiples ejecuciones
        
        this.init();
    }
    
    init() {
        this.setupActivityListeners();
        this.startPeriodicCheck();
        this.createWarningModal();
        this.createExpiredModal();
    }
    
    // Detecta actividad del usuario
    setupActivityListeners() {
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, () => {
                this.updateActivity();
            }, true);
        });
    }
    
    // Actualiza timestamp de última actividad
    updateActivity() {
        this.lastActivity = Date.now();
        
        // NO ocultar advertencia si ya está mostrada
        // La advertencia solo se oculta al renovar la sesión
    }
    
    // Inicia verificación periódica cada 2 segundos para pruebas
    startPeriodicCheck() {
        this.checkInterval = setInterval(() => {
            this.checkSessionStatus();
        }, 2000); // 2 segundos para pruebas
    }
    
    // Verifica estado de sesión con el servidor
    async checkSessionStatus() {
        try {
            const response = await fetch('session_check.php?action=check');
            const data = await response.json();
            
            if (data.status === 'expired') {
                this.handleExpiredSession();
                return;
            }
            
            if (data.status === 'active') {
                const remaining = data.remaining;
                
                // Mostrar advertencia 5 segundos antes para pruebas
                // Solo mostrar si no está ya mostrada
                if (remaining <= 5 && !this.warningShown) {
                    // Calcular timestamp exacto de expiración basado en el servidor
                    this.sessionEndTime = Date.now() + (remaining * 1000);
                    this.showWarning(remaining);
                } else if (remaining > 5 && this.warningShown) {
                    // Si el servidor dice que hay más de 5 segundos pero el warning está mostrado
                    // significa que se renovó la sesión, ocultar warning
                    this.hideWarning();
                }
            }
            
        } catch (error) {
            console.error('Error verificando sesión:', error);
        }
    }
    
    // Maneja sesión expirada
    handleExpiredSession() {
        // Evitar múltiples ejecuciones
        if (this.sessionExpired) {
            return;
        }
        
        this.sessionExpired = true;
        
        // Limpiar todos los intervalos
        clearInterval(this.checkInterval);
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
        }
        
        // Ocultar modal de advertencia si está abierto
        if (this.warningShown && this.warningModal) {
            this.warningModal.hide();
        }
        
        // Mostrar modal de sesión expirada
        this.showExpiredModal();
    }
    
    // Muestra advertencia de expiración
    showWarning(remainingSeconds) {
        this.warningShown = true;
        
        // Mostrar modal
        this.warningModal.show();
        
        // Iniciar contador local que se actualiza cada segundo
        this.startLocalCountdown();
        
        // Auto-cerrar si no hay respuesta en el tiempo restante
        setTimeout(() => {
            if (this.warningShown) {
                this.handleExpiredSession();
            }
        }, remainingSeconds * 1000);
    }
    
    // Inicia contador local que se actualiza cada segundo
    startLocalCountdown() {
        // Limpiar contador anterior si existe
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
        }
        
        this.countdownInterval = setInterval(() => {
            if (!this.sessionEndTime || !this.warningShown) {
                clearInterval(this.countdownInterval);
                this.countdownInterval = null;
                return;
            }
            
            const now = Date.now();
            const remaining = Math.max(0, Math.ceil((this.sessionEndTime - now) / 1000));
            
            // Actualizar display
            this.updateCountdownDisplay(remaining);
            
            // Si llegó a 0, manejar expiración
            if (remaining <= 0) {
                clearInterval(this.countdownInterval);
                this.countdownInterval = null;
                this.handleExpiredSession();
            }
        }, 1000); // Actualizar cada segundo
        
        // Actualizar inmediatamente al inicio
        const now = Date.now();
        const remaining = Math.max(0, Math.ceil((this.sessionEndTime - now) / 1000));
        this.updateCountdownDisplay(remaining);
    }
    
    // Actualiza el display del contador
    updateCountdownDisplay(remainingSeconds) {
        const timeElement = document.getElementById('timeRemaining');
        if (timeElement) {
            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;
            const timeText = minutes > 0 ? `${minutes}m ${seconds}s` : `${seconds}s`;
            timeElement.textContent = timeText;
        }
    }
    
    // Oculta advertencia
    hideWarning() {
        this.warningShown = false;
        this.sessionEndTime = null;
        
        // Limpiar contador local
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
            this.countdownInterval = null;
        }
        
        if (this.warningModal) {
            this.warningModal.hide();
        }
    }
    
    // Renueva sesión (60 minutos más)
    async renewSession() {
        try {
            const response = await fetch('session_check.php?action=renew');
            const data = await response.json();
            
            if (data.status === 'renewed') {
                // Resetear flag de expiración
                this.sessionExpired = false;
                
                // Ocultar warning inmediatamente
                this.hideWarning();
                this.updateActivity();
                console.log('Sesión renovada por 15 segundos más');
                
                // Opcional: Forzar una verificación inmediata para sincronizar
                setTimeout(() => {
                    this.checkSessionStatus();
                }, 1000);
            } else {
                this.handleExpiredSession();
            }
            
        } catch (error) {
            console.error('Error renovando sesión:', error);
            this.handleExpiredSession();
        }
    }
    
    // Crea modal de sesión expirada
    createExpiredModal() {
        const modalHTML = `
            <div class="modal fade" id="sessionExpiredModal" tabindex="-1" aria-labelledby="sessionExpiredModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="sessionExpiredModalLabel">
                                <i class="fas fa-times-circle me-2"></i>
                                Sesión Expirada
                            </h5>
                        </div>
                        <div class="modal-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-clock text-danger" style="font-size: 3rem;"></i>
                            </div>
                            <h5>Su sesión ha expirado</h5>
                            <p class="text-muted mb-0">Será redirigido al login automáticamente...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.expiredModal = new bootstrap.Modal(document.getElementById('sessionExpiredModal'));
    }
    
    // Muestra modal de sesión expirada
    showExpiredModal() {
        this.expiredModal.show();
        
        // Redirigir después de 2 segundos
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 3000);
    }
    
    // Crea modal de advertencia
    createWarningModal() {
        // Crear HTML del modal
        const modalHTML = `
            <div class="modal fade" id="sessionWarningModal" tabindex="-1" aria-labelledby="sessionWarningModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title" id="sessionWarningModalLabel">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Sesión por expirar
                            </h5>
                        </div>
                        <div class="modal-body text-center">
                            <p>Su sesión expirará en:</p>
                            <h3 class="text-danger" id="timeRemaining">5s</h3>
                            <p class="text-muted">¿Desea continuar con su sesión por 15 segundos más?</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cerrar sesión
                            </button>
                            <button type="button" class="btn btn-primary" id="renewSessionBtn">
                                <i class="fas fa-refresh me-1"></i>
                                Continuar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Insertar en el DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Inicializar modal de Bootstrap
        this.warningModal = new bootstrap.Modal(document.getElementById('sessionWarningModal'));
        
        // Event listeners
        document.getElementById('renewSessionBtn').addEventListener('click', () => {
            this.renewSession();
        });
        
        // Si cierra el modal sin renovar, cerrar sesión
        document.getElementById('sessionWarningModal').addEventListener('hidden.bs.modal', () => {
            if (this.warningShown) {
                this.handleExpiredSession();
            }
        });
    }
    
    // Destructor
    destroy() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
        }
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    // Solo inicializar si no estamos en la página de login
    if (!window.location.pathname.includes('index.html') && !window.location.pathname.endsWith('/')) {
        window.sessionTimeout = new SessionTimeout();
    }
});

// Limpiar al salir de la página
window.addEventListener('beforeunload', () => {
    if (window.sessionTimeout) {
        window.sessionTimeout.destroy();
    }
});