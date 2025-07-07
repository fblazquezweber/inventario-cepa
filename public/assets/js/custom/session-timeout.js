class SessionTimeout {
    constructor(options = {}) {
        this.config = {
            checkInterval: options.checkInterval || 30000, // 30 segundos para la comprobación del servidor
            warningThreshold: options.warningThreshold || 60, // 1 minuto para mostrar la advertencia
            logoutRedirect: options.logoutRedirect || 'index.html',
            // Nueva opción para el elemento del temporizador de sesión principal
            mainTimerElementId: options.mainTimerElementId || 'session-timer', 
        };

        this.sessionExpired = false;
        this.warningShown = false;

        this.timers = { check: null, countdown: null, mainDisplay: null }; // Añadimos 'mainDisplay'
        this.modals = { warning: null, expired: null };
        this.elements = { timeRemaining: null, mainTimer: null }; // Añadimos 'mainTimer'

        this.init();
    }

    init() {
        const warningModalEl = document.getElementById('sessionWarningModal');
        const expiredModalEl = document.getElementById('sessionExpiredModal');

        if (!warningModalEl || !expiredModalEl) {
            console.error('Los modales de sesión no se encontraron en el DOM. Asegúrate de tener #sessionWarningModal y #sessionExpiredModal.');
            return;
        }

        this.modals.warning = new bootstrap.Modal(warningModalEl);
        this.modals.expired = new bootstrap.Modal(expiredModalEl);
        this.elements.timeRemaining = document.getElementById('timeRemaining'); // Elemento dentro del modal de advertencia
        this.elements.mainTimer = document.getElementById(this.config.mainTimerElementId); // Tu elemento principal de la página

        if (!this.elements.mainTimer) {
             console.warn(`Elemento principal del temporizador con ID '${this.config.mainTimerElementId}' no encontrado. El temporizador visual de la sesión no se mostrará.`);
        }

        this._setupEventListeners();
        // Inicia la comprobación periódica del estado de la sesión con el servidor
        this.timers.check = setInterval(() => this.checkSessionStatus(), this.config.checkInterval);
        this.checkSessionStatus(); // Primera comprobación inmediata.
    }

    _setupEventListeners() {
        document.getElementById('renewSessionBtn').addEventListener('click', () => this.renewSession());
        document.getElementById('logoutSessionBtn').addEventListener('click', () => this.logout());

        // Ocultar modal de advertencia reinicia el temporizador de visualización principal
        this.modals.warning._element.addEventListener('hidden.bs.modal', () => {
            if (!this.sessionExpired) {
                 this._startMainDisplayCountdown(0); // Reinicia el display principal con 0 y deja que checkSessionStatus lo actualice
            }
        });
    }

    async checkSessionStatus() {
        if (this.sessionExpired) return;

        try {
            const response = await fetch('session_check.php?action=check');
            const data = await response.json();

            if (data.status === 'expired') {
                this.handleExpiredSession();
            } else if (data.status === 'active') {
                // Actualiza el temporizador principal de la UI con el tiempo restante del servidor
                this._startMainDisplayCountdown(data.remaining);

                if (data.remaining <= this.config.warningThreshold && !this.warningShown) {
                    this.showWarning(data.remaining);
                } else if (data.remaining > this.config.warningThreshold && this.warningShown) {
                    this.hideWarning();
                }
            }
        } catch (error) {
            console.error('Error verificando sesión:', error);
            // Considera cómo manejar errores de red o del servidor: ¿desactivar el temporizador? ¿mostrar un mensaje?
        }
    }

    showWarning(remainingSeconds) {
        this.warningShown = true;
        this.modals.warning.show();
        this._startLocalCountdown(remainingSeconds); // Temporizador en el modal de advertencia
        // Ajusta el color del temporizador principal si está visible
        if (this.elements.mainTimer && !this.sessionExpired) {
            this.elements.mainTimer.parentElement.classList.remove('bg-success');
            this.elements.mainTimer.parentElement.classList.add('bg-warning', 'text-dark');
        }
    }

    hideWarning() {
        this.warningShown = false;
        this._cleanupIntervals('countdown');
        this.modals.warning.hide();
         // Restaura el color original del temporizador principal si está visible
        if (this.elements.mainTimer && !this.sessionExpired) {
            this.elements.mainTimer.parentElement.classList.remove('bg-warning', 'text-dark');
            this.elements.mainTimer.parentElement.classList.add('bg-success');
        }
    }

    _startLocalCountdown(seconds) {
        this._cleanupIntervals('countdown');
        let remaining = seconds;

        const updateDisplay = () => {
            if (this.elements.timeRemaining) {
                const minutes = Math.floor(remaining / 60);
                const secs = remaining % 60;
                this.elements.timeRemaining.textContent = minutes > 0 ? `${minutes}m ${secs}s` : `${secs}s`;
            }
        };

        this.timers.countdown = setInterval(() => {
            remaining--;
            updateDisplay();
            if (remaining <= 0) {
                this.handleExpiredSession();
            }
        }, 1000);

        updateDisplay();
    }

    _startMainDisplayCountdown(initialSeconds) {
        if (!this.elements.mainTimer) return;

        this._cleanupIntervals('mainDisplay'); // Limpia el temporizador anterior
        let timeRemaining = initialSeconds;

        // Establece el color inicial según el tiempo restante
        if (timeRemaining < this.config.warningThreshold && !this.warningShown) { // Usar warningThreshold del servidor
            this.elements.mainTimer.parentElement.classList.remove('bg-success');
            this.elements.mainTimer.parentElement.classList.add('bg-warning', 'text-dark');
        } else if (!this.warningShown) { // Si no estamos en estado de advertencia, mantener verde
            this.elements.mainTimer.parentElement.classList.remove('bg-warning', 'text-dark', 'bg-danger');
            this.elements.mainTimer.parentElement.classList.add('bg-success');
        }


        const updateMainTimerDisplay = () => {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            const formattedTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            this.elements.mainTimer.textContent = `Sesión activa (${formattedTime})`;
        };

        updateMainTimerDisplay(); // Actualiza inmediatamente

        this.timers.mainDisplay = setInterval(() => {
            timeRemaining--;
            if (timeRemaining <= 0) {
                clearInterval(this.timers.mainDisplay);
                this.elements.mainTimer.textContent = 'Sesión expirada';
                this.elements.mainTimer.parentElement.classList.remove('bg-success', 'bg-warning', 'text-dark');
                this.elements.mainTimer.parentElement.classList.add('bg-danger');
                // La expiración real la maneja checkSessionStatus
            } else {
                updateMainTimerDisplay();
            }

            // Aquí podríamos agregar la lógica de cambio de color basada en el tiempo local si no hay modal de advertencia.
            // Pero lo ideal es que checkSessionStatus lo controle con el valor del servidor.
        }, 1000);
    }

    async renewSession() {
        this.hideWarning();
        try {
            await fetch('session_check.php?action=renew');
            console.log('Sesión renovada.');
            // Al renovar, volvemos a comprobar el estado para obtener el nuevo tiempo restante
            this.checkSessionStatus();
        } catch (error) {
            console.error('Error renovando sesión:', error);
        }
    }

    handleExpiredSession() {
        if (this.sessionExpired) return;
        this.sessionExpired = true;

        this._cleanupIntervals();
        if (this.warningShown) this.modals.warning.hide();
        this.modals.expired.show();

        // Actualiza el temporizador principal a "Sesión expirada"
        if (this.elements.mainTimer) {
            this.elements.mainTimer.textContent = 'Sesión expirada';
            this.elements.mainTimer.parentElement.classList.remove('bg-success', 'bg-warning', 'text-dark');
            this.elements.mainTimer.parentElement.classList.add('bg-danger');
        }

        setTimeout(() => this.logout(), 3000);
    }

    logout() {
        window.location.href = this.config.logoutRedirect;
    }

    _cleanupIntervals(type = 'all') {
        if (type === 'all' || type === 'countdown') {
            clearInterval(this.timers.countdown);
            this.timers.countdown = null;
        }
        if (type === 'all' || type === 'check') {
            clearInterval(this.timers.check);
            this.timers.check = null;
        }
        if (type === 'all' || type === 'mainDisplay') {
            clearInterval(this.timers.mainDisplay);
            this.timers.mainDisplay = null;
        }
    }

    destroy() {
        this._cleanupIntervals();
    }
}

// Inicialización del script.
document.addEventListener('DOMContentLoaded', () => {
    // Detecta si estamos en la página de login para no inicializar el temporizador allí
    const isLoginPage = window.location.pathname.includes('index.html') || window.location.pathname.endsWith('/');
    if (!isLoginPage) {
        // Pasa un ID para el elemento donde se mostrará el temporizador principal
        window.sessionTimeout = new SessionTimeout({
            mainTimerElementId: 'session-timer',
            // Puedes ajustar otras opciones aquí si lo necesitas
            // checkInterval: 30000, // Comprueba cada 30 segundos
            // warningThreshold: 60, // Muestra advertencia 60 segundos antes
            // logoutRedirect: 'login.html', // Redirige a login.html
        });
    }
});

window.addEventListener('beforeunload', () => {
    if (window.sessionTimeout) {
        window.sessionTimeout.destroy();
    }
});