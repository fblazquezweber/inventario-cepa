<?php
require_once __DIR__ . '/../src/helpers/session.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/db.php';
requireLogin();
include __DIR__ . '/../src/views/navbar.php';  // ✅ Asegura la inclusión de la barra

$pdo = getDbConnection('usuarios_ocana');
$seccion = $_GET['accion'] ?? 'crear';

// Función para verificar si existe un username
function usernameExiste(string $username, PDO $pdo): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetchColumn() > 0;
}

// Generador automático de username
function generarUsername(string $nombre, string $apellido, PDO $pdo): string {
    $norm = fn(string $s): string =>
        preg_replace('/[^A-Z]/', '', strtoupper(iconv('UTF-8','ASCII//TRANSLIT',$s)));
    $base = substr($norm($nombre), 0, 1) . substr($norm($apellido), 0, 3);
    $suf  = 0;
    $user = $base . '00';
    while (usernameExiste($user, $pdo)) {
        if (++$suf > 99) throw new RuntimeException("Sin combinaciones libres para $base");
        $user = $base . str_pad((string)$suf, 2, '0', STR_PAD_LEFT);
    }
    return $user;
}

// Procesar formulario CREAR
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'crear') {
    $nombre    = strtoupper(trim($_POST['nombre']));
    $apellido  = strtoupper(trim($_POST['apellido']));
    $email     = strtolower(trim($_POST['email']));
    $pass1     = $_POST['contrasena'];
    $pass2     = $_POST['contrasena2'];

    if (strlen($pass1) < 6) {
        $mensaje = '❌ La contraseña debe tener al menos 6 caracteres.';
    } elseif ($pass1 !== $pass2) {
      $mensaje = '❌ Las contraseñas no coinciden.';
    } else {
      $passHash = password_hash($pass1, PASSWORD_DEFAULT);
      $activo    = 1;
      try {
        $username = generarUsername($nombre, $apellido, $pdo);
        $stmt = $pdo->prepare("INSERT INTO usuarios (email, contrasena_hash, nombre, apellido, username, activo) VALUES (:email, :hash, :nombre, :apellido, :username, :activo)");
        $stmt->execute([
          'email' => $email,
          'hash'  => $passHash,
          'nombre'=> $nombre,
          'apellido'=>$apellido,
          'username'=>$username,
          'activo'=> $activo
        ]);
        // Registrar auditoría
            $pdo->prepare("INSERT INTO usuarios_auditoria 
                           (id_usuario_objetivo, username_objetivo, accion, motivo, id_usuario_ejecutor)
                           VALUES ((SELECT id_usuario FROM usuarios WHERE email = :email), :username, 'CREADO', :motivo, :id_ejec)")
                ->execute([
                    'email'    => $email,
                    'username' => $username,
                    'motivo'   => 'Creación inicial',
                    'id_ejec'  => $_SESSION['user_id']
                ]);
            $mensaje = "✅ Usuario creado correctamente. Username: <strong>$username</strong>";
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed: usuarios.email')) {
              $mensaje = '❌ El correo electrónico ya está registrado.';
            } elseif (str_contains($e->getMessage(), 'Sin combinaciones libres para')) {
              $mensaje = '❌ Error: No se pudo generar un nombre de usuario automático único. Cambie el nombre o apellido.';
            } else {
              $mensaje = '❌ Error inesperado: ' . $e->getMessage();
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Usuarios</title>
  <link rel="stylesheet" href="assets/css/vendor/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/vendor/all.min.css">
</head>
<body>
  <main class="container mt-4">
    <h1 class="h3 mb-3"><i class="fas fa-user-cog text-warning me-2"></i>Gestión de Usuarios</h1>

  <!-- Navegación -->
  <div class="btn-group mb-4" role="group">
    <a href="?accion=crear" class="btn btn-outline-primary <?= $seccion==='crear' ? 'active' : '' ?>"><i class="fas fa-plus"></i> Crear</a>
    <a href="?accion=actualizar" class="btn btn-outline-warning <?= $seccion==='actualizar' ? 'active' : '' ?>"><i class="fas fa-edit"></i> Actualizar</a>
    <a href="?accion=auditoria" class="btn btn-outline-dark <?= $seccion==='auditoria' ? 'active' : '' ?>"><i class="fas fa-clipboard-list"></i> Auditoría</a>
  </div>

<?php if ($seccion === 'crear'): ?>
  <h5 class="mb-3">Crear Nuevo Usuario</h5>

  <?php if ($mensaje): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <?= $mensaje ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>

  <form method="POST" class="row g-3">
    <input type="hidden" name="accion" value="crear">

  <!-- Fila 1 -->
  <div class="col-md-6">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control mayuscula" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Apellido</label>
    <input type="text" name="apellido" class="form-control mayuscula" required>
  </div>

  <!-- Fila 2 -->
  <div class="col-md-12">
    <label class="form-label">Correo electrónico</label>
    <input type="email" name="email" class="form-control minuscula" required>
  </div>

  <!-- Fila 3 -->
  <div class="col-md-6">
    <label class="form-label">Contraseña</label>
    <input type="password" name="contrasena" class="form-control" required minlength="6">

  </div>
  <div class="col-md-6">
    <label class="form-label">Repetir contraseña</label>
    <input type="password" name="contrasena2" class="form-control" required minlength="6">
  </div>

  <!-- Botón -->
   <div class="col-12 d-flex justify-content-start gap-2">
  <button type="submit" class="btn btn-success">
    <i class="fas fa-save me-1"></i> Guardar
  </button>
  <button type="reset" class="btn btn-outline-warning">
    <i class="fas fa-eraser me-1"></i> Limpiar
  </button>
  <a href="dashboard.php" class="btn btn-secondary">
    <i class="fas fa-times me-1"></i> Cancelar
  </a>
</div>

</form>

<?php elseif ($seccion === 'auditoria'): ?>
  <h5 class="mb-3">Auditoría de Usuarios</h5>

  <?php
    $filtro = $_GET['filtro'] ?? '';
    
    $sql = "SELECT 
            a.*, 
            u_obj.email AS email_objetivo, 
            u_ejec.username AS ejecutado_por
        FROM usuarios_auditoria a
        JOIN usuarios u_obj ON u_obj.id_usuario = a.id_usuario_objetivo
        JOIN usuarios u_ejec ON u_ejec.id_usuario = a.id_usuario_ejecutor";


    if (in_array($filtro, ['CREADO', 'ELIMINADO', 'ACTUALIZADO'])) {
        $sql .= " WHERE a.accion = :filtro";
        $stmt = $pdo->prepare($sql . " ORDER BY fecha DESC LIMIT 100");
        $stmt->execute(['filtro' => $filtro]);
    } else {
        $stmt = $pdo->query($sql . " ORDER BY fecha DESC LIMIT 100");
    }
    $auditorias = $stmt->fetchAll();
  ?>

  <form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="accion" value="auditoria">
    <div class="col-md-4">
      <select name="filtro" class="form-select">
        <option value="">— Todos los registros —</option>
        <option value="CREADO" <?= $filtro==='CREADO' ? 'selected' : '' ?>>Usuarios creados</option>
        <option value="ACTUALIZADO" <?= $filtro==='ACTUALIZADO' ? 'selected' : '' ?>>Usuarios actualizados</option>
        <option value="ELIMINADO" <?= $filtro==='ELIMINADO' ? 'selected' : '' ?>>Usuarios eliminados</option>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-dark"><i class="fas fa-filter me-1"></i> Filtrar</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-bordered">
      <thead class="table-light">
  <tr>
    <th>Username</th>
    <th>Acción</th>
    <th>Motivo</th>
    <th>Ejecutado por</th>
    <th>Fecha</th>
  </tr>
</thead>
<tbody>
  <?php foreach ($auditorias as $a): ?>
  <tr>
    <td><?= htmlspecialchars($a['username_objetivo']) ?></td>
    <td><?= $a['accion'] ?></td>
    <td><?= nl2br(htmlspecialchars($a['motivo'])) ?></td>
    <td><?= htmlspecialchars($a['ejecutado_por']) ?></td>
    <td><?= substr($a['fecha'], 0, 10) ?></td>
  </tr>
  <?php endforeach; ?>
</tbody>

    </table>
  </div>

  <?php elseif ($seccion === 'actualizar'): ?>
  <h5 class="mb-3">Buscar, Actualizar o Desactivar Usuario</h5>

  <?php
    $busqueda = trim($_GET['busqueda'] ?? '');
    $usuarioEncontrado = null;
    $mensajeActualizacion = '';

    if ($busqueda) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :q OR LOWER(email) = LOWER(:q)");
        $stmt->execute(['q' => strtolower($busqueda)]);
        $usuarioEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$usuarioEncontrado) {
            echo '<div class="alert alert-danger">❌ No se encontró ningún usuario con ese username o email.</div>';
        }
    }

    // Procesar actualización
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'actualizar') {
        $idUsuario = $_POST['id_usuario'];
        $nombre = strtoupper(trim($_POST['nombre']));
        $apellido = strtoupper(trim($_POST['apellido']));
        $email = strtolower(trim($_POST['email']));
        $pass = $_POST['contrasena'];

        $nuevoUsername = $usuarioEncontrado['username'];
        $params = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'username' => $nuevoUsername,
            'id' => $idUsuario
        ];

        $sql = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, email = :email, username = :username";
        if (!empty($pass)) {
            $sql .= ", contrasena_hash = :hash";
            $params['hash'] = password_hash($pass, PASSWORD_DEFAULT);
        }
        $sql .= " WHERE id_usuario = :id";

        $pdo->prepare($sql)->execute($params);

        // Auditoría
        $pdo->prepare("INSERT INTO usuarios_auditoria 
                       (id_usuario_objetivo, username_objetivo, accion, motivo, id_usuario_ejecutor)
                       VALUES (:id, :username, 'ACTUALIZADO', 'Datos del usuario actualizados', :id_ejec)")
            ->execute([
                'id'       => $idUsuario,
                'username' => $nuevoUsername,
                'id_ejec'  => $_SESSION['user_id']
            ]);

        $mensajeActualizacion = '✅ Usuario actualizado correctamente. Nuevo username: <strong>' . $nuevoUsername . '</strong>';
        $usuarioEncontrado = $pdo->query("SELECT * FROM usuarios WHERE id_usuario = $idUsuario")->fetch(PDO::FETCH_ASSOC);
    }


    // Procesar activación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'activar') {
    $idUsuario = $_POST['id_usuario'] ?? null;
    if ($idUsuario) {
        $pdo->prepare("UPDATE usuarios SET activo = 1 WHERE id_usuario = :id")
            ->execute(['id' => $idUsuario]);

        // Registrar en auditoría como ACTUALIZADO
        $pdo->prepare("INSERT INTO usuarios_auditoria 
                       (id_usuario_objetivo, username_objetivo, accion, motivo, id_usuario_ejecutor)
                       VALUES (:id, :username, 'ACTUALIZADO', 'Reactivación manual del usuario', :id_ejec)")
            ->execute([
                'id'       => $idUsuario,
                'username' => $_POST['username'],
                'id_ejec'  => $_SESSION['user_id']
            ]);

        // Recargar usuario actualizado desde la BD
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = :id");
        $stmt->execute(['id' => $idUsuario]);
        $usuarioEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);

        $mensajeActualizacion = '✅ Usuario activado correctamente.';
    }
}

// Procesar desactivación-
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'desactivar') {
    $idUsuario = $_POST['id_usuario'] ?? null;
    if ($idUsuario) {
        $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id_usuario = :id")
            ->execute(['id' => $idUsuario]);

        // Registrar en auditoría como ELIMINADO (aunque no se elimina físicamente)
        $pdo->prepare("INSERT INTO usuarios_auditoria 
                       (id_usuario_objetivo, username_objetivo, accion, motivo, id_usuario_ejecutor)
                       VALUES (:id, :username, 'ELIMINADO', 'Desactivación manual del usuario', :id_ejec)")
            ->execute([
                'id'       => $idUsuario,
                'username' => $_POST['username'],
                'id_ejec'  => $_SESSION['user_id']
            ]);

        // Recargar datos del usuario
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = :id");
        $stmt->execute(['id' => $idUsuario]);
        $usuarioEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);

        $mensajeActualizacion = '✅ Usuario desactivado correctamente.';
    }
}

  ?>

  <!-- Formulario de búsqueda -->
  <form method="GET" class="row g-3 mb-4">
    <input type="hidden" name="accion" value="actualizar">
    <div class="col-md-6">
      <input type="text" name="busqueda" class="form-control" placeholder="Buscar por username o email" value="<?= htmlspecialchars($busqueda) ?>" required>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Buscar</button>
    </div>
  </form>

  <?php if ($mensajeActualizacion): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= $mensajeActualizacion ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>

  <?php if ($usuarioEncontrado): ?>
    <form method="POST" class="row g-3">
      <input type="hidden" name="accion" value="actualizar">
      <input type="hidden" name="id_usuario" value="<?= $usuarioEncontrado['id_usuario'] ?>">

      <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control mayuscula" value="<?= htmlspecialchars($usuarioEncontrado['nombre']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Apellido</label>
        <input type="text" name="apellido" class="form-control mayuscula" value="<?= htmlspecialchars($usuarioEncontrado['apellido']) ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Username (no editable)</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($usuarioEncontrado['username']) ?>" readonly>
      </div>

      <div class="col-md-6">
        <label class="form-label">Correo electrónico</label>
        <input type="email" name="email" class="form-control minuscula" value="<?= htmlspecialchars($usuarioEncontrado['email']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Nueva contraseña (opcional)</label>
        <input type="password" name="contrasena" class="form-control" minlength="6">
      </div>

      <div class="col-12 d-flex gap-2">
  <button type="submit" class="btn btn-success">
    <i class="fas fa-save me-1"></i> Guardar cambios
  </button>
  <a href="?accion=actualizar" class="btn btn-outline-secondary">
    <i class="fas fa-eraser me-1"></i> Limpiar
  </a>
</div>
    </form>

    <?php if ($usuarioEncontrado['activo']): ?>
      <form method="POST" class="mt-3">
        <input type="hidden" name="accion" value="desactivar">
        <input type="hidden" name="id_usuario" value="<?= $usuarioEncontrado['id_usuario'] ?>">
        <input type="hidden" name="username" value="<?= $usuarioEncontrado['username'] ?>">
        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('¿Desactivar este usuario?')">
          <i class="fas fa-user-slash me-1"></i> Desactivar usuario
        </button>
      </form>
    <?php endif; ?>
    <?php if (!$usuarioEncontrado['activo']): ?>
  <form method="POST" class="mt-3">
    <input type="hidden" name="accion" value="activar">
    <input type="hidden" name="id_usuario" value="<?= $usuarioEncontrado['id_usuario'] ?>">
    <input type="hidden" name="username" value="<?= $usuarioEncontrado['username'] ?>">
    <button type="submit" class="btn btn-outline-success" onclick="return confirm('¿Activar este usuario?')">
      <i class="fas fa-user-check me-1"></i> Activar usuario
    </button>
  </form>
<?php endif; ?>

  <?php endif; ?>
<?php endif; ?>

    <script src="assets/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="assets/js/vendor/all.min.js"></script>
    <script src="assets/js/custom/disable-bfcache.js"></script>
    <script src="assets/js/custom/uppercase-inputs.js"></script>
  </main>
</body>
</html>
