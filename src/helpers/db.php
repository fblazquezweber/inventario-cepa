<?php
function getDbConnection() {
    //db.php
    // Ruta absoluta de la base de datos SQLite
    $dbPath = __DIR__ . '/../database/usuarios_ocana.db';

    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch (PDOException $e) {
        // En producción podrías registrar el error en lugar de mostrarlo
        die('Error al conectar a la base de datos: ' . htmlspecialchars($e->getMessage()));
    }
}
