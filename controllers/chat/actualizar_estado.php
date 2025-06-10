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

try {
    $usuario = new Usuario();
    $resultado = $usuario->actualizarActividad($_SESSION['usuario_id']);
    
    echo json_encode(['success' => $resultado]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al actualizar estado: ' . $e->getMessage()]);
} 