document.addEventListener('DOMContentLoaded', function () {
  // Convertir a mayúsculas estos campos:
  const camposMayusculas = [
    'busqueda',
    'descripcion',
    'nombre_objeto',
    'marca',
    'modelo',
    'numero_serie',
    'codigo_interno',
    'ubicacion',
    'motivo',
    'observacion',
    'nombre',
    'apellido'
  ];

  camposMayusculas.forEach(function (name) {
    const input = document.querySelector(`input[name="${name}"], textarea[name="${name}"]`);
    if (input) {
      input.addEventListener('input', function () {
        this.value = this.value.toUpperCase();
      });
    }
  });

  // Convertir a minúsculas el campo de correo
  const emailInput = document.querySelector('input[name="email"]');
  if (emailInput) {
    emailInput.addEventListener('input', function () {
      this.value = this.value.toLowerCase();
    });
  }
});
