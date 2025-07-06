<?php
// session_check.php
// Endpoints AJAX para manejo de sesión simplificado

require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/db.php';

// Determinar acción según parámetro
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'check':
        handleSessionCheck();
        break;
        
    case 'renew':
        handleSessionRenew();
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Acción no válida']);
        exit;
}
?>