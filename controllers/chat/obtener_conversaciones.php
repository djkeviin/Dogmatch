<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../models/Mensaje.php';
require_once '../../models/Usuario.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $usuario_id = $_SESSION['usuario_id'];
    $mensajeModel = new Mensaje();
    $usuarioModel = new Usuario();

    // 1. Obtener las conversaciones usando el método del modelo
    $conversaciones = $mensajeModel->obtenerConversaciones($usuario_id);

    // 2. Enriquecer los datos de la conversación
    foreach ($conversaciones as &$conv) {
        // Verificar estado online del otro usuario
        $conv['online'] = $usuarioModel->estaEnLinea($conv['otro_usuario_id']);
        
        // Añadir una bandera para saber si el último mensaje es una imagen
        $conv['es_multimedia'] = !empty($conv['multimedia_id']);
    }

    // 3. Devolver las conversaciones en formato JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($conversaciones);

} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Error al obtener conversaciones: ' . $e->getMessage()]);
} 