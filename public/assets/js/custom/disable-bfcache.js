// disable-bfcache.js
// Previene que páginas protegidas se muestren desde el Back-Forward Cache (BFCache)
window.addEventListener('pageshow', function (event) {
  if (event.persisted) {
    window.location.reload();
  }
});
