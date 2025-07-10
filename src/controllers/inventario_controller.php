<?php
$modoEdicion      = false;
$id_objeto        = null;
$errores          = [];
$registroExitoso = false; // ✅ Agregado para mostrar alerta tras inserción
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
    foreach ($datos as $k => &$v) {
        if (isset($_POST[$k])) {
            $v = trim($_POST[$k]);
        }
    }

    // Validaciones de campos obligatorios y sin solo espacios
    if (trim($datos['nombre_objeto']) === '') {
        $errores[] = "El nombre del objeto es obligatorio.";
    }
    if (trim($datos['id_categoria']) === '') {
        $errores[] = "Debe seleccionar una categoría.";
    }
    if (trim($datos['numero_serie']) === '') {
        $errores[] = "El número de serie es obligatorio.";
    }

    if (trim($datos['codigo_interno']) === '') {
        $errores[] = "El código interno es obligatorio.";
    }
    
    if (in_array($datos['estado'], $estadosConMotivo) && trim($datos['motivo']) === '') {
        $errores[] = "Debe proporcionar un motivo para el estado '{$datos['estado']}'.";
    }


    // Verificar duplicados antes de insertar
    $stmtVerif = $pdo->prepare("SELECT COUNT(*) FROM inventario WHERE codigo_interno = :ci OR numero_serie = :ns");
    $stmtVerif->execute([
        'ci' => $datos['codigo_interno'],
        'ns' => $datos['numero_serie']
    ]);
    if ($stmtVerif->fetchColumn() > 0) {
        $errores[] = "Ya existe un objeto con ese código interno o número de serie.";
    }

    if (empty($errores)) {
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
        } else {
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
            $registroExitoso = true; // ✅ Marca que el alta fue exitosa
        }
    }
    //PRUEBA
    $datos = [
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
        'motivo'             => '',
    ];
    $modoEdicion = false;
    $id_objeto = null;
}

$mostrarMovimiento = in_array($datos['estado'], $estadosConMotivo);