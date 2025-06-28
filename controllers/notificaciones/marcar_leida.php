<?php
session_start();
require_once '../../models/Notificacion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
    exit;
}

$noti = new Notificacion();
$noti->marcarLeida($id);
echo json_encode(['success' => true]); 