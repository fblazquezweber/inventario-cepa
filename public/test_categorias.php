<?php
require_once __DIR__ . '/../src/helpers/db.php';

try {
    $pdo = getDbConnection('inventario');
    $stmt = $pdo->query('SELECT * FROM categorias');
    $categorias = $stmt->fetchAll();
    echo "<pre>";
    print_r($categorias);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
