<?php
session_start();
require_once '../../models/Notificacion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$noti = new Notificacion();
$notificaciones = $noti->obtenerNoLeidas($_SESSION['usuario_id'], 20);
echo json_encode(['success' => true, 'notificaciones' => $notificaciones]); 