<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../models/Mensaje.php';

header('Content-Type: application/json');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Obtener parámetros
$perro_id = isset($_GET['perro_id']) ? intval($_GET['perro_id']) : 0;
$ultima_actualizacion = isset($_GET['ultima_actualizacion']) ? intval($_GET['ultima_actualizacion']) : 0;

if (!$perro_id) {
    echo json_encode(['error' => 'ID del perro no proporcionado']);
    exit;
}

try {
    $mensajeModel = new Mensaje();
    $mensajes = $mensajeModel->obtenerMensajes($perro_id, $_SESSION['usuario_id'], $ultima_actualizacion);

    // Procesar los mensajes para incluir información adicional
    foreach ($mensajes as &$mensaje) {
        $mensaje['es_emisor'] = $mensaje['emisor_id'] == $_SESSION['usuario_id'];
        // Asegurarse de que el nombre del emisor esté presente
        if (!isset($mensaje['emisor_nombre']) || empty($mensaje['emisor_nombre'])) {
            $mensaje['emisor_nombre'] = 'Usuario';
        }
    }

    echo json_encode([
        'success' => true,
        'mensajes' => $mensajes,
        'ultima_actualizacion' => time()
    ]);

} catch (Exception $e) {
    error_log("Error en obtener_mensajes.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error al obtener mensajes']);
} 