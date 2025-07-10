<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $modoEdicion ? 'Editar' : 'Nuevo' ?> Objeto — Inventario CEPA</title>
  <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/vendor/all.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">
      <i class="fas fa-boxes me-2"></i> Inventario CEPA
    </a>

    <!-- Botón hamburguesa -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContenido"
      aria-controls="navbarContenido" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Contenido colapsable -->
    <div class="collapse navbar-collapse" id="navbarContenido">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <span class="nav-link">
            <i class="fas fa-user me-1"></i>
            <?= htmlspecialchars($_SESSION['user_name']); ?>
          </span>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">
            <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>


<main class="container">
  <?php if (!empty($registroExitoso) && $registroExitoso === true): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <strong>¡Éxito!</strong> Objeto registrado correctamente.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>

  <h1 class="h3 mb-4">
    <i class="fas fa-box text-primary me-2"></i>
    <?= $modoEdicion ? 'Editar Objeto' : 'Registrar Nuevo Objeto' ?>
  </h1>

  <?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errores as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach ?>
      </ul>
    </div>
  <?php endif ?>

  <form class="row g-2 mb-4" method="POST">
    <div class="col-auto">
      <input type="text" name="busqueda" class="form-control"
             placeholder="Código interno o Nº serie" required>
    </div>
    <div class="col-auto">
      <button name="buscar" type="submit" class="btn btn-outline-primary">Buscar</button>
    </div>
  </form>

  <form method="POST" class="row g-3">
    <?php if ($modoEdicion): ?>
      <input type="hidden" name="id_objeto" value="<?= $id_objeto ?>">
    <?php endif ?>

    <div class="col-md-6">
      <label class="form-label">Nombre del objeto</label>
      <input 
        name="nombre_objeto" 
        type="text" 
        class="form-control"
        value="<?= htmlspecialchars($datos['nombre_objeto']) ?>"
        required 
        pattern="^(?=.*[^\s])[A-ZÑ0-9 ._\-]+$"
        title="Solo letras mayúsculas, números, espacios, puntos y guiones">    
    </div>

    <div class="col-md-6">
      <label class="form-label">Categoría</label>
      <select name="id_categoria" class="form-select" required>
        <option value="">— Selecciona —</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id_categoria'] ?>"
            <?= $cat['id_categoria'] == $datos['id_categoria'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['nombre_categoria']) ?>
          </option>
        <?php endforeach ?>
      </select>
    </div>

    <div class="col-12">
      <label class="form-label">Descripción</label>
      <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($datos['descripcion']) ?></textarea>
    </div>

    <div class="col-md-4">
      <label class="form-label">Marca</label>
      <input name="marca" type="text" class="form-control"
             value="<?= htmlspecialchars($datos['marca']) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Modelo</label>
      <input name="modelo" type="text" class="form-control"
             value="<?= htmlspecialchars($datos['modelo']) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Nº de serie</label>
      <input 
        name="numero_serie" 
        type="text" 
        class="form-control"
        value="<?= htmlspecialchars($datos['numero_serie']) ?>"
        required 
        pattern="^(?=.*[^\s])[A-ZÑ0-9 ._\-]+$" 
        title="Este campo no puede estar vacío ni contener solo espacios">
    </div>

    <div class="col-md-6">
      <label class="form-label">Código interno</label>
      <input 
      name="codigo_interno" 
      type="text" 
      class="form-control"
      value="<?= htmlspecialchars($datos['codigo_interno']) ?>"
      required 
      pattern="^(?=.*[^\s])[A-ZÑ0-9 ._\-]+$" 
      title="Este campo no puede estar vacío ni contener solo espacios">
    </div>
    <div class="col-md-6">
      <label class="form-label">Ubicación</label>
      <input name="ubicacion" type="text" class="form-control"
             value="<?= htmlspecialchars($datos['ubicacion']) ?>">
    </div>

    <div class="col-md-4">
      <label class="form-label">Fecha de adquisición</label>
      <input name="fecha_adquisicion" type="date" class="form-control"
             value="<?= htmlspecialchars($datos['fecha_adquisicion']) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Valor de adquisición</label>
      <input name="valor_adquisicion" type="number" step="0.01" class="form-control"
             value="<?= htmlspecialchars($datos['valor_adquisicion']) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Estado</label>
      <select id="estadoSelect" name="estado" class="form-select" required>
        <?php foreach ($estados as $est): ?>
          <option value="<?= $est ?>" <?= $est == $datos['estado'] ? 'selected' : '' ?>>
            <?= $est ?>
          </option>
        <?php endforeach ?>
      </select>
    </div>

    <div id="movimientosSection" class="col-12 mt-3" style="display: <?= $mostrarMovimiento ? 'block' : 'none' ?>;">
      <div class="card border-warning">
        <div class="card-header bg-warning text-dark">
          Registrar movimiento: <?= htmlspecialchars($datos['estado']) ?>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Motivo</label>
            <textarea name="motivo" class="form-control" rows="2" <?= in_array($datos['estado'], $estadosConMotivo) ? 'required' : '' ?>><?= htmlspecialchars($datos['motivo']) ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 text-end">
      <button name="guardar" type="submit" class="btn btn-primary">
        <?= $modoEdicion ? 'Actualizar' : 'Registrar' ?>
      </button>
      <button type="button" id="btnLimpiarTodo" class="btn btn-secondary">Limpiar</button>
      <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</main>

<script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
<script src="assets/js/vendor/all.min.js"></script>
<script src="assets/js/custom/disable-bfcache.js"></script>
<script src="assets/js/custom/uppercase-inputs.js"></script>
<script src="assets/js/custom/limpiar-recargar.js"></script>

<script>
  window.estadosConMotivo = <?= json_encode($estadosConMotivo) ?>;
</script>
<script src="assets/js/custom/mostrar-motivo.js"></script>


</body>
</html>
