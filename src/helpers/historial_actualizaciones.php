<?php
function registrarHistorialCambios($pdo, $id_objeto, $id_usuario, $datosAnteriores, $datosNuevos) {
    $campos = ['nombre_objeto', 'numero_serie', 'codigo_interno'];

    foreach ($campos as $campo) {
        $valorViejo = trim($datosAnteriores[$campo] ?? '');
        $valorNuevo = trim($datosNuevos[$campo] ?? '');

        if ($valorViejo !== $valorNuevo) {
            $stmt = $pdo->prepare("
                INSERT INTO historial_actualizaciones (id_objeto, id_usuario, campo_modificado, valor_anterior, valor_nuevo)
                VALUES (:id_objeto, :id_usuario, :campo, :anterior, :nuevo)
            ");
            $stmt->execute([
                'id_objeto' => $id_objeto,
                'id_usuario' => $id_usuario,
                'campo' => $campo,
                'anterior' => $valorViejo,
                'nuevo' => $valorNuevo
            ]);
        }
    }
}