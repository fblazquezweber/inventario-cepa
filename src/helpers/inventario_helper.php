<?php

// Lista de campos válidos en el inventario
function getCamposInventario() {
    return [
        'nombre_objeto', 'descripcion', 'observacion', 'categoria',
        'marca', 'modelo', 'numero_serie', 'codigo_interno',
        'fecha_adquisicion', 'valor_adquisicion', 'estado', 'ubicacion'
    ];
}

// Captura y limpia los datos del formulario desde $_POST
function capturarDatosInventario($origen = []) {
    $datos = [];
    foreach (getCamposInventario() as $campo) {
        $datos[$campo] = trim($origen[$campo] ?? '');
    }
    return $datos;
}