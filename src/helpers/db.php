<?php
function getDbConnection($dbName = 'usuarios_ocana') {
    $dbPath = __DIR__ . "/../database/{$dbName}.db";

    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $pdo;
    } catch (PDOException $e) {
        die('Error al conectar a la base de datos: ' . htmlspecialchars($e->getMessage()));
    }
}
