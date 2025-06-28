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
    $perro_id = $_POST['perro_id'] ?? null; // El perro que responde
    $interesado_id = $_POST['interesado_id'] ?? null; // El perro que envió la solicitud
    $accion = $_POST['accion'] ?? null; // 'aceptar' o 'rechazar'

    if (!$perro_id || !$interesado_id || !$accion) {
        throw new Exception('Datos incompletos');
    }

    // Verificar que el perro que responde pertenece al usuario logueado
    $perroModel = new Perro();
    $perro = $perroModel->obtenerPorId($perro_id);
    if (!$perro || $perro['usuario_id'] != $_SESSION['usuario_id']) {
        throw new Exception('No puedes responder con este perro');
    }

    $match = new MatchPerro();
    $aceptar = ($accion === 'aceptar');
    $match->responderMatch($perro_id, $interesado_id, $aceptar);

    $mensaje = $aceptar ? 'Solicitud aceptada correctamente' : 'Solicitud rechazada';
    echo json_encode(['success' => true, 'message' => $mensaje]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 