<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../models/Usuario.php';

header('Content-Type: application/json');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Obtener datos de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['usuarios']) || !is_array($data['usuarios'])) {
    echo json_encode(['error' => 'Lista de usuarios no proporcionada']);
    exit;
}

try {
    $usuario = new Usuario();
    $estados = $usuario->obtenerEstadosEnLinea($data['usuarios']);
    
    echo json_encode($estados);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener estados: ' . $e->getMessage()]);
} 