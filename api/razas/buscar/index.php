<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../../../models/RazaPerro.php';

try {
    $razaModel = new RazaPerro();
    $query = $_GET['q'] ?? '';
    
    $razas = $razaModel->buscarRazas($query);
    
    echo json_encode($razas);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage()
    ]);
} 