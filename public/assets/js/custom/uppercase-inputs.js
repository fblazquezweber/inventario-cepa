document.addEventListener('DOMContentLoaded', function() {
  // Lista de nombres de campos a convertir a mayúsculas
  const campos = [
    'busqueda',
    'descripcion',
    'nombre_objeto',
    'marca',
    'modelo',
    'numero_serie',
    'codigo_interno',
    'ubicacion',
    'motivo'
  ];

  campos.forEach(function(name) {
    const input = document.querySelector(`input[name="${name}"], textarea[name="${name}"]`);
    if (input) {
      input.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
      });
    }
  });
});
