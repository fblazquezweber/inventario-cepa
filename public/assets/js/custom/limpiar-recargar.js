document.getElementById('btnLimpiarTodo').addEventListener('click', () => {
  // Limpia todos los formularios de la página
  document.querySelectorAll('form').forEach(form => form.reset());
  // Recarga la página sin query params para eliminar mensajes
  window.location.href = window.location.pathname;
});
