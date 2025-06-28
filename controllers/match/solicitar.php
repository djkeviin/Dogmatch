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
    $perro_id = $_POST['perro_id'] ?? null; // El perro que recibe la solicitud
    $interesado_id = $_POST['interesado_id'] ?? null; // El perro que envía la solicitud

    if (!$perro_id || !$interesado_id) {
        throw new Exception('Datos incompletos');
    }

    // Verificar que el interesado pertenece al usuario logueado
    $perroModel = new Perro();
    $perro_interesado = $perroModel->obtenerPorId($interesado_id);
    if (!$perro_interesado || $perro_interesado['usuario_id'] != $_SESSION['usuario_id']) {
        throw new Exception('No puedes usar este perro para enviar la solicitud');
    }

    $match = new MatchPerro();
    $match->solicitarMatch($perro_id, $interesado_id);

    echo json_encode(['success' => true, 'message' => 'Solicitud enviada correctamente']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 