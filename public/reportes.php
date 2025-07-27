<?php
require_once __DIR__ . '/../src/helpers/session.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/db.php';
requireLogin();
include __DIR__ . '/../src/views/navbar.php';

$pdo = getDbConnection('inventario_ocana');

$categoriasDisponibles = ['AUDIOVISUALES', 'INFORMÁTICA', 'MOBILIARIO', 'HERRAMIENTAS', 'LABORATORIO', 'LIBROS', 'OTROS'];
$estadosDisponibles    = ['OPERATIVO', 'MANTENIMIENTO', 'PRÉSTAMO', 'INACTIVO', 'BAJA'];
/* ----------  filtros comunes (solo para la sección inventario) ---------- */
$categoria = $_GET['categoria'] ?? '';
$estado    = $_GET['estado']    ?? '';
$anio      = $_GET['anio']      ?? '';

$sql = "SELECT nombre_objeto, categoria, estado, fecha_adquisicion, valor_adquisicion,
               numero_serie, codigo_interno
        FROM inventario WHERE 1=1";

$params = [];
if ($categoria !== '') { $sql .= " AND categoria = :categoria"; $params['categoria']=$categoria; }
if ($estado    !== '') { $sql .= " AND estado    = :estado";    $params['estado']=$estado;    }
if ($anio      !== '') { $sql .= " AND strftime('%Y',fecha_adquisicion)= :anio"; $params['anio']=$anio; }

$stmt = $pdo->prepare($sql); $stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ----------  sección seleccionada ---------- */
$seccion = $_GET['seccion'] ?? 'inventario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reportes — Inventario CEPA</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/vendor/all.min.css">
</head>
<body>

<main class="container mt-4">
  <h1 class="h3 mb-3"><i class="fas fa-chart-bar text-success me-2"></i>Reportes del Inventario</h1>

  <!-- barra de pestañas -->
  <div class="btn-group mb-4" role="group">
    <a href="reportes.php?seccion=inventario" class="btn btn-outline-primary   <?= $seccion==='inventario' ? 'active' : ''?>"><i class="fas fa-box me-1"></i>Inventario</a>
    <a href="reportes.php?seccion=movimientos" class="btn btn-outline-secondary <?= $seccion==='movimientos' ? 'active' : ''?>"><i class="fas fa-exchange-alt me-1"></i>Movimientos</a>
    <a href="reportes.php?seccion=cambios"     class="btn btn-outline-dark      <?= $seccion==='cambios'     ? 'active' : ''?>"><i class="fas fa-history me-1"></i>Cambios</a>
    <a href="reportes.php?seccion=grafico"     class="btn btn-outline-success   <?= $seccion==='grafico'     ? 'active' : ''?>"><i class="fas fa-chart-line me-1"></i>Mostrar gráfico</a>
    <a href="reportes.php?seccion=detalle" class="btn btn-outline-info <?= $seccion === 'detalle' ? 'active' : '' ?>"> <i class="fas fa-search me-1"></i> Detalle</a>
  </div>

<?php if ($seccion === 'inventario'): ?>

  <p class="text-muted">Filtra y consulta la información del inventario.</p>

  <!-- filtros -->
  <form method="GET" class="row g-3 mb-4">
    <input type="hidden" name="seccion" value="inventario">
    <div class="col-md-4">
      <label class="form-label">Categoría</label>
      <select name="categoria" class="form-select">
        <option value="">Todas</option>
        <?php foreach ($categoriasDisponibles as $cat): ?>
            <option value="<?= $cat ?>" <?= $categoria === $cat ? 'selected' : '' ?>>
                <?= $cat ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Estado</label>
      <select name="estado" class="form-select">
        <option value="">Todos</option>
        <?php foreach ($estadosDisponibles as $est): ?>
            <option value="<?= $est ?>" <?= $estado === $est ? 'selected' : '' ?>>
                <?= $est ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Año de adquisición</label>
      <input type="number" name="anio" min="2000" max="2100" class="form-control" value="<?= htmlspecialchars($anio) ?>">
    </div>
    <div class="col-12 d-flex justify-content-between">
      <a href="reportes.php?seccion=inventario" class="btn btn-outline-secondary"><i class="fas fa-eraser me-1"></i>Limpiar</a>
      <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Aplicar</button>
    </div>
  </form>

  <!-- tabla inventario -->
  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="table-light"><tr>
        <th>Nombre</th>
        <th>Categoría</th>
        <th>Estado</th>
        <th>Fecha</th>
        <th>Nº de Serie</th>
        <th>Código Interno</th>
        <th>Valor (€)</th></tr>
    </thead>
      <tbody>
        <?php if ($resultados): foreach ($resultados as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['nombre_objeto']) ?></td>
              <td><?= htmlspecialchars($r['categoria']) ?></td>
              <td><?= htmlspecialchars($r['estado']) ?></td>
              <td><?= substr($r['fecha_adquisicion'], 0, 10) ?></td>
              <td><?= htmlspecialchars($r['numero_serie'] ?? '-') ?></td>
              <td><?= htmlspecialchars($r['codigo_interno'] ?? '-') ?></td>
              <td><?= number_format($r['valor_adquisicion'],2,',','.') ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5" class="text-center text-muted">Sin resultados.</td></tr>
        <?php endif;?>
      </tbody>
    </table>
  </div>

<?php elseif ($seccion === 'movimientos'): ?>

  <h5><i class="fas fa-exchange-alt me-2 text-secondary"></i>Historial de Movimientos</h5>
  <?php
    $movs=$pdo->query("SELECT m.*,i.nombre_objeto FROM movimientos_inventario m
                        JOIN inventario i ON i.id_objeto=m.id_objeto
                        ORDER BY m.fecha_movimiento DESC LIMIT 100")->fetchAll();
  ?>
  <div class="table-responsive mt-3">
    <table class="table table-bordered table-sm">
      <thead class="table-light"><tr><th>Objeto</th><th>Tipo</th><th>Motivo</th><th>Fecha</th></tr></thead>
      <tbody>
        <?php foreach ($movs as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m['nombre_objeto']) ?></td>
            <td><?= htmlspecialchars($m['tipo_movimiento']) ?></td>
            <td><?= htmlspecialchars($m['motivo']) ?></td>
            <td><?= htmlspecialchars(substr($m['fecha_movimiento'], 0, 10)) ?></td>
          </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>

<?php elseif ($seccion === 'cambios'): ?>

  <h5><i class="fas fa-history me-2 text-secondary"></i>Cambios en Campos Clave</h5>
  <?php
    $chg=$pdo->query("SELECT h.*,i.nombre_objeto FROM historial_actualizaciones h
                       JOIN inventario i ON i.id_objeto=h.id_objeto
                       ORDER BY h.fecha_modificacion DESC LIMIT 100")->fetchAll();
  ?>
  <div class="table-responsive mt-3">
    <table class="table table-bordered table-sm">
      <thead class="table-light"><tr><th>Objeto</th><th>Campo</th><th>Antes</th><th>Después</th><th>Fecha</th></tr></thead>
      <tbody>
        <?php foreach ($chg as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c['nombre_objeto']) ?></td>
            <td><?= htmlspecialchars($c['campo_modificado']) ?></td>
            <td><?= htmlspecialchars($c['valor_anterior']) ?></td>
            <td><?= htmlspecialchars($c['valor_nuevo']) ?></td>
            <td><?= htmlspecialchars(substr($c['fecha_modificacion'], 0, 10)) ?></td>
          </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>

  <?php elseif ($seccion === 'grafico'): ?>
  <?php
  $pdo = getDbConnection('inventario_ocana');
  $anioValor = $_GET['anio_valor'] ?? '';
  $trimestreValor = $_GET['trimestre_valor'] ?? '';
  $datosValor = [];
  
  $sqlValor = "SELECT categoria, SUM(valor_adquisicion) as total
            FROM inventario
            WHERE fecha_adquisicion IS NOT NULL";
  $paramsValor = [];

if ($anioValor !== '') {
    $sqlValor .= " AND strftime('%Y', fecha_adquisicion) = :anio";
    $paramsValor['anio'] = $anioValor;
}

if ($trimestreValor !== '') {
    $rangos = [
        '1' => ['01', '03'],
        '2' => ['04', '06'],
        '3' => ['07', '09'],
        '4' => ['10', '12'],
    ];
    if (isset($rangos[$trimestreValor])) {
        $sqlValor .= " AND strftime('%m', fecha_adquisicion) BETWEEN :mes_ini AND :mes_fin";
        $paramsValor['mes_ini'] = $rangos[$trimestreValor][0];
        $paramsValor['mes_fin'] = $rangos[$trimestreValor][1];
    }
}

$sqlValor .= " GROUP BY categoria ORDER BY total DESC";
$stmtValor = $pdo->prepare($sqlValor);
$stmtValor->execute($paramsValor);
$datosValor = $stmtValor->fetchAll(PDO::FETCH_ASSOC);

?>

<h5><i class="fas fa-chart-bar me-2 text-secondary"></i>Valor total por Categoría</h5>

<form method="GET" class="row g-3 mb-4">
  <input type="hidden" name="seccion" value="grafico">

  <div class="col-md-4">
  <label class="form-label">Año</label>
  <?php
  // Obtener años únicos desde la base de datos
  $aniosDisponibles = $pdo->query("
      SELECT DISTINCT strftime('%Y', fecha_adquisicion) as anio 
      FROM inventario 
      WHERE fecha_adquisicion IS NOT NULL 
      ORDER BY anio DESC
  ")->fetchAll(PDO::FETCH_COLUMN);
  ?>
  <select name="anio_valor" class="form-select">
    <option value="">— Todos los años —</option>
    <?php foreach ($aniosDisponibles as $a): ?>
      <option value="<?= $a ?>" <?= $anioValor === $a ? 'selected' : '' ?>><?= $a ?></option>
    <?php endforeach; ?>
  </select>
</div>

  <div class="col-md-4">
    <label class="form-label">Trimestre (opcional)</label>
    <select name="trimestre_valor" class="form-select">
      <option value="">— Todos —</option>
      <option value="1" <?= $trimestreValor==='1' ? 'selected' : '' ?>>1º trimestre</option>
      <option value="2" <?= $trimestreValor==='2' ? 'selected' : '' ?>>2º trimestre</option>
      <option value="3" <?= $trimestreValor==='3' ? 'selected' : '' ?>>3º trimestre</option>
      <option value="4" <?= $trimestreValor==='4' ? 'selected' : '' ?>>4º trimestre</option>
    </select>
  </div>

  <div class="col-12">
    <button type="submit" class="btn btn-success">
      <i class="fas fa-chart-bar me-1"></i> Mostrar gráfico
    </button>
  </div>
</form>

<?php if (!empty($datosValor)): ?>
  <div class="mt-5">
    <canvas id="graficoValoresCategoria" height="100"></canvas>
  </div>

  <?php if ($seccion === 'grafico' && !empty($datosValor)): ?>
<script>
  window.addEventListener('DOMContentLoaded', () => {
    const etiquetasValor = <?= json_encode(array_column($datosValor, 'categoria')) ?>;
    const valoresValor   = <?= json_encode(array_map('floatval', array_column($datosValor, 'total'))) ?>;
    renderGraficoValoresPorCategoria(etiquetasValor, valoresValor);
  });
</script>
<?php endif; ?>


  <div class="mt-3 text-center">
  <div class="alert alert-success d-inline-block fw-bold">
    Total acumulado: <?= number_format(array_sum(array_column($datosValor, 'total')), 2, ',', '.') ?> €
  </div>
</div>

  <script>
    const etiquetasValor = <?= json_encode(array_column($datosValor, 'categoria')) ?>;
    const valoresValor   = <?= json_encode(array_map('floatval', array_column($datosValor, 'total'))) ?>;

    const ctx = document.getElementById('graficoValoresCategoria').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: etiquetasValor,
        datasets: [{
          label: 'Valor total (€)',
          data: valoresValor,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: context => `${context.parsed.y.toLocaleString()} €`
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: value => value.toLocaleString() + ' €'
            },
            title: { display: true, text: 'Valor (€)' }
          },
          x: {
            title: { display: true, text: 'Categoría' }
          }
        }
      }
    });
  </script>

<?php else: ?>
  <div class="alert alert-warning mt-4">No hay datos disponibles para los filtros seleccionados.</div>
<?php endif; ?>

<?php endif; ?>

<?php if ($seccion === 'detalle'): ?>
  <!-- 🟡 Sección de búsqueda detallada -->
  <h5><i class="fas fa-search me-2 text-secondary"></i> Buscar Detalles de Objeto</h5>

  <form method="GET" class="row g-3 mb-4">
    <input type="hidden" name="seccion" value="detalle">

    <div class="col-md-6">
      <label class="form-label">Buscar por Código Interno o Número de Serie</label>
      <input type="text" name="busqueda" class="form-control" required
       style="text-transform: uppercase;"
       value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
    </div>

    <div class="col-12">
      <button type="submit" class="btn btn-info">
        <i class="fas fa-search me-1"></i> Buscar
      </button>
    </div>
  </form>

  <?php
  $detalle = null;
  $busqueda = trim($_GET['busqueda'] ?? '');
  if ($busqueda !== '') {
    $pdoInv = getDbConnection('inventario_ocana');
    $stmt = $pdoInv->prepare("
        SELECT *
        FROM inventario
        WHERE codigo_interno = :valor OR numero_serie = :valor
        LIMIT 1
    ");
    $stmt->execute(['valor' => $busqueda]);
    $detalle = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($detalle) {
        // Obtener el nombre del usuario desde la otra base de datos
        $pdoUsu = getDbConnection('usuarios_ocana');
        $stmt2 = $pdoUsu->prepare("SELECT nombre, apellido FROM Usuarios WHERE id_usuario = :id");
        $stmt2->execute(['id' => $detalle['usuario_creacion'] ?? 0]);
        $usuario = $stmt2->fetch();
        $detalle['usuario_nombre'] = $usuario ? $usuario['nombre'] . ' ' . $usuario['apellido'] : 'Desconocido';
      }
    }
?>

  <?php if ($detalle): ?>
    <div class="card border-info">
      <div class="card-header bg-info text-white">
        <strong><?= htmlspecialchars($detalle['nombre_objeto']) ?></strong>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush">
          <li class="list-group-item"><strong>Categoría:</strong> <?= htmlspecialchars($detalle['categoria']) ?></li>
          <li class="list-group-item"><strong>Marca:</strong> <?= htmlspecialchars($detalle['marca']) ?></li>
          <li class="list-group-item"><strong>Modelo:</strong> <?= htmlspecialchars($detalle['modelo']) ?></li>
          <li class="list-group-item"><strong>Número de serie:</strong> <?= htmlspecialchars($detalle['numero_serie']) ?></li>
          <li class="list-group-item"><strong>Código interno:</strong> <?= htmlspecialchars($detalle['codigo_interno']) ?></li>
          <li class="list-group-item"><strong>Fecha de adquisición:</strong> <?= htmlspecialchars($detalle['fecha_adquisicion']) ?></li>
          <li class="list-group-item"><strong>Valor (€):</strong> <?= number_format((float)$detalle['valor_adquisicion'], 2, ',', '.') ?></li>
          <li class="list-group-item"><strong>Estado:</strong> <?= htmlspecialchars($detalle['estado']) ?></li>
          <li class="list-group-item"><strong>Ubicación:</strong> <?= htmlspecialchars($detalle['ubicacion']) ?></li>
          <li class="list-group-item"><strong>Observación:</strong> <?= nl2br(htmlspecialchars($detalle['observacion'])) ?></li>
          <li class="list-group-item"><strong>Registrado por:</strong> <?= htmlspecialchars($detalle['usuario_nombre'] ?? 'Desconocido') ?></li>
          <li class="list-group-item"><strong>Fecha de registro:</strong> <?= substr($detalle['fecha_creacion'], 0, 10) ?></li>
        </ul>
      </div>
    </div>
  <?php elseif ($busqueda !== ''): ?>
    <div class="alert alert-warning">No se encontró ningún objeto con ese código interno o número de serie.</div>
  <?php endif; ?>

<?php endif; /* --------- FIN de todas las secciones --------- */ ?>

</main>

<script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
<script src="assets/js/vendor/all.min.js"></script>
<script src="assets/js/vendor/chart.js"></script>
<script src="assets/js/custom/grafico-adquisiciones.js"></script>


</body>
</html>
