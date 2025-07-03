window.addEventListener('pageshow', function (event) {
  // Detecta si la página se carga desde el back-forward cache (bfcache)
  if (event.persisted) {
    console.log('La página se cargó desde bfcache, limpiando campos...');
  }

  const emailInput = document.querySelector('input[name="email"]');
  const passwordInput = document.querySelector('input[name="password"]');

  if (emailInput) emailInput.value = "";
  if (passwordInput) passwordInput.value = "";
});
