<?php
require_once __DIR__ . '/../src/helpers/auth.php';    // Verifica login
require_once __DIR__ . '/../src/helpers/db.php';      // Conexión a BD
requireLogin();
// Conexión a la base de datos de inventario
$pdo = getDbConnection('inventario');

// Inicializar variables
$modoEdicion      = false;
$id_objeto        = null;
$errores          = [];
$datos            = [
    'nombre_objeto'      => '',
    'descripcion'        => '',
    'id_categoria'       => '',
    'marca'              => '',
    'modelo'             => '',
    'numero_serie'       => '',
    'codigo_interno'     => '',
    'fecha_adquisicion'  => '',
    'valor_adquisicion'  => '',
    'estado'             => 'Operativo',
    'ubicacion'          => '',
    'motivo'             => '', // Campo motivo para estados que lo requieren
];

// Cargar categorías para el select
$stmtCat = $pdo->query("SELECT id_categoria, nombre_categoria FROM categorias ORDER BY nombre_categoria");
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Estados permitidos
$estadosConMotivo = ['Mantenimiento', 'Préstamo', 'Inactivo', 'Baja'];
$estados = ['Operativo', 'Mantenimiento', 'Préstamo', 'Inactivo', 'Baja'];

// Búsqueda por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $busqueda = trim($_POST['busqueda'] ?? '');
    $stmt = $pdo->prepare("
        SELECT * FROM inventario 
        WHERE codigo_interno = :b OR numero_serie = :b
    ");
    $stmt->execute(['b' => $busqueda]);
    if ($row = $stmt->fetch()) {
        $modoEdicion = true;
        $id_objeto   = $row['id_objeto'];
        $datos       = $row;
    } else {
        $errores[] = "No se encontró ningún objeto con ese código o número de serie.";
    }
}

// Procesar alta/edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    // Recoger campos
    foreach ($datos as $k => &$v) {
        if (isset($_POST[$k])) {
            $v = trim($_POST[$k]);
        }
    }

    // Validar motivo si el estado requiere
    if (in_array($datos['estado'], $estadosConMotivo) && empty($datos['motivo'])) {
        $errores[] = "Debe proporcionar un motivo para el estado '{$datos['estado']}'.";
    }

    if (empty($errores)) {
        // Si viene id_objeto: UPDATE
        if (!empty($_POST['id_objeto'])) {
            $modoEdicion = true;
            $id_objeto   = (int) $_POST['id_objeto'];
            $sql = "UPDATE inventario SET
                        nombre_objeto      = :nombre_objeto,
                        descripcion        = :descripcion,
                        id_categoria       = :id_categoria,
                        marca              = :marca,
                        modelo             = :modelo,
                        numero_serie       = :numero_serie,
                        codigo_interno     = :codigo_interno,
                        fecha_adquisicion  = :fecha_adquisicion,
                        valor_adquisicion  = :valor_adquisicion,
                        estado             = :estado,
                        ubicacion          = :ubicacion,
                        motivo             = :motivo
                    WHERE id_objeto = :id_objeto";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge($datos, ['id_objeto' => $id_objeto]));
        }
        // Si no: INSERT
        else {
            $sql = "INSERT INTO inventario (
                        nombre_objeto, descripcion, id_categoria,
                        marca, modelo, numero_serie, codigo_interno,
                        fecha_adquisicion, valor_adquisicion, estado,
                        ubicacion, motivo, usuario_creacion
                    ) VALUES (
                        :nombre_objeto, :descripcion, :id_categoria,
                        :marca, :modelo, :numero_serie, :codigo_interno,
                        :fecha_adquisicion, :valor_adquisicion, :estado,
                        :ubicacion, :motivo, :usuario_creacion
                    )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge($datos, [
                'usuario_creacion' => $_SESSION['user_id']
            ]));
            $id_objeto    = $pdo->lastInsertId();
            $modoEdicion  = true;
        }
    }
}

// Mostrar sección movimientos solo para estados que requieren motivo
$mostrarMovimiento = in_array($datos['estado'], $estadosConMotivo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $modoEdicion ? 'Editar' : 'Nuevo' ?> Objeto — Inventario CEPA</title>
  <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/vendor/all.min.css">
  <link rel="stylesheet" href="assets/css/custom/estilos.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">
      <i class="fas fa-boxes me-2"></i> Inventario CEPA
    </a>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item">
        <span class="nav-link"><i class="fas fa-user me-1"></i><?= htmlspecialchars($_SESSION['user_name']); ?></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Cerrar sesión</a>
      </li>
    </ul>
  </div>
</nav>

<main class="container">
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
      <input name="nombre_objeto" type="text" class="form-control"
             value="<?= htmlspecialchars($datos['nombre_objeto']) ?>" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Categoría</label>
      <select name="id_categoria" class="form-select" required>
        <option value="">— Selecciona —</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id_categoria'] ?>"
            <?= $cat['id_categoria']==$datos['id_categoria'] ? 'selected' : '' ?>>
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
      <input name="numero_serie" type="text" class="form-control"
             value="<?= htmlspecialchars($datos['numero_serie']) ?>">
    </div>

    <div class="col-md-6">
      <label class="form-label">Código interno</label>
      <input name="codigo_interno" type="text" class="form-control"
             value="<?= htmlspecialchars($datos['codigo_interno']) ?>" required>
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
          <option value="<?= $est ?>"
            <?= $est == $datos['estado'] ? 'selected' : '' ?>>
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
      <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</main>

<script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
<script src="assets/js/vendor/all.min.js"></script>
<script src="assets/js/custom/disable-bfcache.js"></script>

<script>
// Mostrar/ocultar sección de movimientos al cambiar estado
const estadosConMotivo = <?= json_encode($estadosConMotivo) ?>;
document.getElementById('estadoSelect').addEventListener('change', function() {
  const sec = document.getElementById('movimientosSection');
  if (estadosConMotivo.includes(this.value)) {
    sec.style.display = 'block';
    sec.querySelector('textarea[name="motivo"]').setAttribute('required', 'required');
  } else {
    sec.style.display = 'none';
    sec.querySelector('textarea[name="motivo"]').removeAttribute('required');
  }
});
</script>

</body>
</html>
