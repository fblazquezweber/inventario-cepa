<?php
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/db.php';
requireLogin();

$pdo = getDbConnection('inventario_ocana');

require_once __DIR__ . '/../src/controllers/inventario_controller.php';
require_once __DIR__ . '/../src/views/inventario_form.php';
