<?php
session_start();
require_once '../../models/MatchPerro.php';
require_once '../../models/perro.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

try {
    $perroModel = new Perro();
    // Obtener todos los perros del usuario
    $perros = $perroModel->obtenerPorUsuario($_SESSION['usuario_id']);
    $ids = array_column($perros, 'id');
    if (empty($ids)) {
        echo json_encode(['success' => true, 'pendientes' => []]);
        exit;
    }
    $in = str_repeat('?,', count($ids) - 1) . '?';
    $sql = "SELECT s.*, p1.nombre as nombre_interesado, p1.foto as foto_interesado, p2.nombre as nombre_perro
            FROM solicitudes_match s
            JOIN perros p1 ON s.interesado_id = p1.id
            JOIN perros p2 ON s.perro_id = p2.id
            WHERE s.perro_id IN ($in) AND s.estado = 'pendiente'";
    $db = (new \ReflectionClass($perroModel))->getProperty('db');
    $db->setAccessible(true);
    $db = $db->getValue($perroModel);
    $stmt = $db->prepare($sql);
    $stmt->execute($ids);
    $pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'pendientes' => $pendientes]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 