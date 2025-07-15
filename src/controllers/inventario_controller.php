<?php
require_once __DIR__ . '/../helpers/historial_actualizaciones.php';
// Inicialización de variables
$modoEdicion      = false;
$id_objeto        = null; // Esto contendrá el ID del objeto si estamos en modo edición
$errores          = [];
$registroExitoso  = false; // Para mostrar alerta tras inserción de nuevo objeto
$actualizacionExitoso = false; // Para mostrar alerta tras actualización de objeto existente

// Datos iniciales del formulario (para un nuevo registro)
$datos = [
    'nombre_objeto'      => '',
    'descripcion'        => '',
    'observacion'        => '',
    'categoria'          => '',
    'marca'              => '',
    'modelo'             => '',
    'numero_serie'       => '',
    'codigo_interno'     => '',
    'fecha_adquisicion'  => '',
    'valor_adquisicion'  => '',
    'estado'             => 'Operativo', // Estado por defecto para nuevos objetos
    'ubicacion'          => '',
];

// Cargar categorías para el select (siempre necesario)
$categorias = ['Audiovisuales', 'Informática', 'Mobiliario'];

// Estados permitidos y aquellos que requieren motivo
$estadosConMotivo = ['Mantenimiento', 'Préstamo', 'Inactivo', 'Baja'];
$estados = ['Operativo', 'Mantenimiento', 'Préstamo', 'Inactivo', 'Baja'];

// --- Lógica de Manejo de Solicitudes POST ---

// **IMPORTANTE: Mover la inicialización de $id_objeto y $modoEdicion al inicio
// para que el resto del script sepa si estamos en modo edición.**
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_objeto']) && !empty($_POST['id_objeto'])) {
        $id_objeto = (int) $_POST['id_objeto'];
        $modoEdicion = true;
    }
}

// 1. Manejo del POST de "Buscar"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $busqueda = trim($_POST['busqueda'] ?? '');
    if (empty($busqueda)) {
        $errores[] = "Debe ingresar un código interno o número de serie para buscar.";
    } else {
        $stmt = $pdo->prepare("
            SELECT * FROM inventario 
            WHERE codigo_interno = :b OR numero_serie = :b
        ");
        $stmt->execute(['b' => $busqueda]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $modoEdicion = true;
            $id_objeto   = $row['id_objeto'];
            $datos       = $row; // Cargar los datos del objeto encontrado
            // Nota: Aquí no reinicializamos $datos si la búsqueda tiene éxito.
            // La variable $datos ya contendrá el objeto buscado.
        } else {
            $errores[] = "No se encontró ningún objeto con ese código o número de serie.";
            // Si no se encuentra, resetear el formulario para un nuevo alta
            $id_objeto = null;
            $modoEdicion = false;
            // Reinicializar $datos para que el formulario quede limpio
            $datos = [
                'nombre_objeto'      => '', 'descripcion' => '', 'categoria' => '',
                'marca'              => '', 'modelo' => '', 'numero_serie' => '', 'codigo_interno' => '',
                'fecha_adquisicion'  => '', 'valor_adquisicion' => '', 'estado' => 'Operativo',
                'ubicacion'          => '', 'observacion' => ''
            ];
        }
    }
}

// 2. Procesar alta/edición (botón 'guardar')
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    // Captura los datos del formulario (esto sobrescribirá los datos cargados por la búsqueda)
    foreach ($datos as $k => &$v) {
        if (isset($_POST[$k])) {
            $v = trim($_POST[$k]);
        }
    }
    unset($v); // Romper la referencia al último elemento

    $estadoAnterior = null; // Inicializar para nuevo registro
    


    // --- Validaciones ---
    if (trim($datos['nombre_objeto']) === '') {
        $errores[] = "El nombre del objeto es obligatorio.";
    }
    if (trim($datos['categoria']) === '') {
        $errores[] = "Debe seleccionar una categoría.";
    }
    if (trim($datos['numero_serie']) === '') {
        $errores[] = "El número de serie es obligatorio.";
    }
    if (trim($datos['codigo_interno']) === '') {
        $errores[] = "El código interno es obligatorio.";
    }
    
    // Validar motivo si el estado lo requiere
    if (in_array($datos['estado'], $estadosConMotivo) && trim($_POST['motivo'] ?? '') === '') {
    $errores[] = "Debe proporcionar un motivo para el estado '{$datos['estado']}'.";
}

    // --- Validación de Duplicados (Mejorada para Edición) ---
    // Solo verifica duplicados de codigo_interno y numero_serie si son diferentes al objeto actual
    // Si $id_objeto es null (nuevo registro), usará 0 para que la condición != siempre sea verdadera.
    $stmtVerif = $pdo->prepare("
        SELECT id_objeto FROM inventario 
        WHERE (codigo_interno = :ci OR numero_serie = :ns) 
        AND id_objeto != :id_obj_actual
    ");
    $stmtVerif->execute([
        'ci'           => $datos['codigo_interno'],
        'ns'           => $datos['numero_serie'],
        'id_obj_actual' => $id_objeto ?? 0 // Excluye el ID del objeto actual si estamos editando
    ]);
    if ($stmtVerif->fetch()) { // Si encuentra alguna fila, significa que el código/serie ya lo tiene OTRO objeto.
        $errores[] = "Ya existe otro objeto con ese código interno o número de serie.";
    }

    // --- Procesamiento si no hay errores ---
    if (empty($errores)) {
        try {
            $pdo->beginTransaction(); // Iniciar transacción para asegurar atomicidad de operaciones

            if ($modoEdicion && $id_objeto) { // Es una EDICIÓN (UPDATE en inventario)
                //PRUEBA
                // ✅ Recuperar los valores anteriores ANTES del UPDATE
                //$stmtOldData = $pdo->prepare("SELECT estado, motivo, nombre_objeto, numero_serie, codigo_interno FROM inventario WHERE id_objeto = ?");
                $stmtOldData = $pdo->prepare("SELECT estado, nombre_objeto, numero_serie, codigo_interno FROM inventario WHERE id_objeto = ?");

                $stmtOldData->execute([$id_objeto]);
                $valoresAnteriores = $stmtOldData->fetch(PDO::FETCH_ASSOC);

                $stmtUltimoMotivo = $pdo->prepare("
                SELECT motivo FROM movimientos_inventario 
                WHERE id_objeto = ? 
                ORDER BY fecha_movimiento DESC 
                LIMIT 1
                ");
                $stmtUltimoMotivo->execute([$id_objeto]);
                $ultimoMovimiento = $stmtUltimoMotivo->fetch(PDO::FETCH_ASSOC);
                $motivoAnterior = $ultimoMovimiento['motivo'] ?? null;
           
                $estadoAnterior = $valoresAnteriores['estado'] ?? null;
                
                //FIN PRUEBA


                //PRUEBA 2
                // Comparar campos clave y registrar cambios en historial_actualizaciones
                $camposATrastrear = ['nombre_objeto', 'numero_serie', 'codigo_interno'];
                foreach ($camposATrastrear as $campo) {
                    $valorAnterior = $valoresAnteriores[$campo] ?? null;
                    $valorNuevo = $datos[$campo];
                    if ($valorAnterior !== $valorNuevo) {
                        $stmtHist = $pdo->prepare("
                        INSERT INTO historial_actualizaciones (
                        id_objeto, id_usuario, campo_modificado, valor_anterior, valor_nuevo) 
                        VALUES (:id_objeto, :id_usuario, :campo_modificado, :valor_anterior, :valor_nuevo)
                        ");
                        $stmtHist->execute([
                            'id_objeto'        => $id_objeto,
                            'id_usuario'       => $_SESSION['user_id'],
                            'campo_modificado' => $campo,
                            'valor_anterior'   => $valorAnterior,
                            'valor_nuevo'      => $valorNuevo
                        ]);
                    }
                }



                //FIN PRUEBA 2

                $sql = "UPDATE inventario SET
                            nombre_objeto      = :nombre_objeto,
                            descripcion        = :descripcion,
                            categoria          = :categoria,
                            marca              = :marca,
                            modelo             = :modelo,
                            numero_serie       = :numero_serie,
                            codigo_interno     = :codigo_interno,
                            fecha_adquisicion  = :fecha_adquisicion,
                            valor_adquisicion  = :valor_adquisicion,
                            estado             = :estado,
                            ubicacion          = :ubicacion,
                            observacion        = :observacion -- El motivo se guarda en inventario para el estado actual
                        WHERE id_objeto = :id_objeto";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_merge($datos, ['id_objeto' => $id_objeto]));
                //PRUEBA
                // Registrar movimiento si CAMBIÓ EL ESTADO
                if ($estadoAnterior !== null && $datos['estado'] !== $estadoAnterior) {
                    $tipoMovimiento = "Cambio de Estado a " . $datos['estado'];
                    $observacionesMovimiento = $_POST['motivo'] ?? '';
                    
                    $stmtMov = $pdo->prepare("
                        INSERT INTO movimientos_inventario (id_objeto, id_usuario, tipo_movimiento, motivo)
                        VALUES (:id_objeto, :id_usuario, :tipo_movimiento, :motivo)
                        ");
                        $stmtMov->execute([
                            'id_objeto'       => $id_objeto,
                            'id_usuario'      => $_SESSION['user_id'],
                            'tipo_movimiento' => $tipoMovimiento,
                            'motivo'          => $observacionesMovimiento
                        ]);
                    }
                // Registrar movimiento si SOLO CAMBIÓ EL MOTIVO (sin cambio de estado)
                elseif (
                    isset($_POST['motivo']) &&
                    $_POST['motivo'] !== $motivoAnterior &&
                    trim($_POST['motivo']) !== ''
                    ) 
                    {
                        $stmtMov = $pdo->prepare("
                        INSERT INTO movimientos_inventario (id_objeto, id_usuario, tipo_movimiento, motivo)
                        VALUES (:id_objeto, :id_usuario, :tipo_movimiento, :motivo)
                        ");
                        $stmtMov->execute([
                            'id_objeto'       => $id_objeto,
                            'id_usuario'      => $_SESSION['user_id'],
                            'tipo_movimiento' => 'Actualización de motivo',
                            'motivo'          => $_POST['motivo'] ?? ''
                        ]);
                    }

                //FIN PRUEBA

                
                $actualizacionExitoso = true; // Marca para el mensaje de éxito

                // *** LÓGICA CLAVE: Registrar movimiento si el estado ha cambiado ***

            } else { // Es un ALTA (INSERT en inventario)
                $sql = "INSERT INTO inventario (
                            nombre_objeto, descripcion, categoria,
                            marca, modelo, numero_serie, codigo_interno,
                            fecha_adquisicion, valor_adquisicion, estado,
                            ubicacion, observacion, usuario_creacion
                        ) VALUES (
                            :nombre_objeto, :descripcion, :categoria,
                            :marca, :modelo, :numero_serie, :codigo_interno,
                            :fecha_adquisicion, :valor_adquisicion, :estado,
                            :ubicacion, :observacion, :usuario_creacion
                        )";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_merge($datos, [
                    'usuario_creacion' => $_SESSION['user_id']
                ]));
                $id_objeto    = $pdo->lastInsertId(); // Obtiene el ID del nuevo objeto
                $modoEdicion  = true; // Después de insertar, pasamos a modo edición para ver el objeto recién creado
                $registroExitoso = true; // Marca para el mensaje de éxito

                // Registrar el movimiento de "Ingreso" o "Alta" para el nuevo objeto
                $stmtMov = $pdo->prepare("
                INSERT INTO movimientos_inventario (id_objeto, id_usuario, tipo_movimiento, motivo)
                VALUES (:id_objeto, :id_usuario, :tipo_movimiento, :motivo)
                ");
                $stmtMov->execute([
                    'id_objeto'       => $id_objeto,
                    'id_usuario'      => $_SESSION['user_id'],
                    'tipo_movimiento' => 'Ingreso', // O 'Alta'
                    'motivo'          => 'Objeto registrado en el inventario.'
                ]);
            }
            $pdo->commit(); // Confirmar la transacción
                        
            // Recargar los datos del objeto recién guardado/actualizado
            // Esto es CRUCIAL para que el formulario se muestre con los datos correctos
            // después de una operación exitosa (especialmente una edición)
            if ($id_objeto) {
                $stmt = $pdo->prepare("SELECT * FROM inventario WHERE id_objeto = ?");
                $stmt->execute([$id_objeto]);
                $datos = $stmt->fetch(PDO::FETCH_ASSOC);
            }


        } catch (PDOException $e) {
            $pdo->rollBack(); // Deshacer cambios si ocurre un error
            $errores[] = "Error en la base de datos al guardar/actualizar: " . $e->getMessage();
            error_log("Error en inventario_controller.php: " . $e->getMessage()); // Para logs del servidor
        }
    }
}

// Determinar si el campo de motivo debe mostrarse (se usa en la vista)
// Esto debe ser lo último en el controlador, ya que depende del valor final de $datos['estado']
$mostrarMovimiento = in_array($datos['estado'], $estadosConMotivo);