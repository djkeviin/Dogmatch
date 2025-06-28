<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['perro_id']) || !isset($data['escribiendo'])) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

try {
    require_once '../../config/conexion.php';
    $db = Conexion::getConexion();
    
    $usuario_id = $_SESSION['usuario_id'];
    $perro_id = intval($data['perro_id']);
    $escribiendo = $data['escribiendo'] ? 1 : 0;
    
    // Actualizar el estado de escritura en la base de datos
    $sql = "INSERT INTO estado_escritura (usuario_id, perro_id, escribiendo, timestamp) 
            VALUES (?, ?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE 
            escribiendo = VALUES(escribiendo), 
            timestamp = VALUES(timestamp)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$usuario_id, $perro_id, $escribiendo]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 