document.addEventListener('DOMContentLoaded', function () {
  // Duración de la sesión en segundos (60 minutos)
  const sessionDuration = 20;
  let timeRemaining = sessionDuration;
  // Elemento donde se mostrará el temporizador
  const timerElement = document.getElementById('session-timer');
  if (timerElement) {
    // Inicia el intervalo para actualizar el contador cada segundo
    const countdownInterval = setInterval(() => {
      if (timeRemaining <= 0) {
        // Cuando el tiempo se acaba
        clearInterval(countdownInterval);
        timerElement.textContent = 'Sesión expirada';
        timerElement.parentElement.classList.remove('bg-success');
        timerElement.parentElement.classList.add('bg-danger');
        // Opcional: Redirigir a la página de logout
        // window.location.href = 'logout.php';
        return;
      }
      // Decrementa el tiempo
      timeRemaining--;
      // Calcula minutos y segundos
      const minutes = Math.floor(timeRemaining / 60);
      const seconds = timeRemaining % 60;
      // Formatea la salida a MM:SS
      const formattedTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
      // Actualiza el texto
      timerElement.textContent = `Sesión activa (${formattedTime})`;
      // Opcional: Cambiar el color cuando queda poco tiempo (ej. 1 minuto)
      if (timeRemaining < 60) {
        timerElement.parentElement.classList.remove('bg-success');
        timerElement.parentElement.classList.add('bg-warning', 'text-dark');
      }
    }, 1000);
  }
});