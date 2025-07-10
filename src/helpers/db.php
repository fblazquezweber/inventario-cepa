<?php
/**
 * db.php
 * Devuelve conexión PDO a la base de datos SQLite indicada.
 *
 * @param string $base  'usuarios' o 'inventario'
 * @return PDO
 */
function getDbConnection(string $base = 'usuarios'): PDO {
    // Selección de fichero según tipo de base
    switch (strtolower($base)) {
        case 'inventario':
            $filename = 'inventario_ocana.db';
            break;
        case 'usuarios':
        default:
            $filename = 'usuarios_ocana.db';
            break;
    }

    // Ruta absoluta al directorio de bases de datos
    $dbPath = __DIR__ . '/../database/' . $filename;

    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch (PDOException $e) {
        // En producción podrías registrar el error en lugar de mostrarlo
        die('Error al conectar a la base de datos (' . htmlspecialchars($filename) . '): ' 
            . htmlspecialchars($e->getMessage()));
    }
}
