<?php

function validarInventario(array $datos, ?string $motivo, array $estadosConMotivo): array {
    $errores = [];

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
    if (in_array($datos['estado'], $estadosConMotivo) && trim($motivo ?? '') === '') {
        $errores[] = "Debe proporcionar un motivo para el estado '{$datos['estado']}'.";
    }

    return $errores;
}

function existeDuplicadoInventario(PDO $pdo, array $datos, int $idActual = 0): bool {
    $stmt = $pdo->prepare("
        SELECT id_objeto FROM inventario 
        WHERE (codigo_interno = :ci OR numero_serie = :ns) 
        AND id_objeto != :id_actual
    ");
    $stmt->execute([
        'ci'        => $datos['codigo_interno'],
        'ns'        => $datos['numero_serie'],
        'id_actual' => $idActual
    ]);
    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}
