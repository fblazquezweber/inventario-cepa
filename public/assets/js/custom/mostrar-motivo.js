document.addEventListener('DOMContentLoaded', () => {
  const estadosConMotivo = window.estadosConMotivo || [];
  const estadoSelect = document.getElementById('estadoSelect');
  const movimientosSection = document.getElementById('movimientosSection');

  if (!estadoSelect || !movimientosSection) return;

  estadoSelect.addEventListener('change', function() {
    if (estadosConMotivo.includes(this.value)) {
      movimientosSection.style.display = 'block';
      movimientosSection.querySelector('textarea[name="motivo"]').setAttribute('required', 'required');
    } else {
      movimientosSection.style.display = 'none';
      movimientosSection.querySelector('textarea[name="motivo"]').removeAttribute('required');
    }
  });

  // Dispara el evento una vez al cargar para aplicar el estado actual
  estadoSelect.dispatchEvent(new Event('change'));
});