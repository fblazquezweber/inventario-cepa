<?php

/**
 * Recupera los valores anteriores de un objeto antes de ser editado.
 */
function obtenerValoresAnteriores(PDO $pdo, int $id_objeto): ?array {
    $stmt = $pdo->prepare("SELECT estado, nombre_objeto, numero_serie, codigo_interno FROM inventario WHERE id_objeto = ?");
    $stmt->execute([$id_objeto]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Recupera el último motivo registrado en movimientos para un objeto.
 */
function obtenerUltimoMotivo(PDO $pdo, int $id_objeto): ?string {
    $stmt = $pdo->prepare("
        SELECT motivo FROM movimientos_inventario 
        WHERE id_objeto = ? 
        ORDER BY fecha_movimiento DESC 
        LIMIT 1
    ");
    $stmt->execute([$id_objeto]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['motivo'] ?? null;
}

/**
 * Compara campos clave y registra los cambios en historial_actualizaciones.
 */
function registrarCambiosHistorial(PDO $pdo, int $id_objeto, int $id_usuario, array $antes, array $despues, array $camposClave): void {
    $stmt = $pdo->prepare("
        INSERT INTO historial_actualizaciones (
            id_objeto, id_usuario, campo_modificado, valor_anterior, valor_nuevo
        ) VALUES (
            :id_objeto, :id_usuario, :campo, :anterior, :nuevo
        )
    ");

    foreach ($camposClave as $campo) {
        $valorAnterior = $antes[$campo] ?? null;
        $valorNuevo    = $despues[$campo] ?? null;

        if ($valorAnterior !== $valorNuevo) {
            $stmt->execute([
                'id_objeto'  => $id_objeto,
                'id_usuario' => $id_usuario,
                'campo'      => $campo,
                'anterior'   => $valorAnterior,
                'nuevo'      => $valorNuevo
            ]);
        }
    }
}
