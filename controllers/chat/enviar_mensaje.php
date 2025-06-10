<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../models/Mensaje.php';
require_once '../../models/Perro.php';

header('Content-Type: application/json');

// Debug - Guardar información de la sesión
error_log("SESSION en enviar_mensaje.php: " . print_r($_SESSION, true));

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Obtener y validar los datos del mensaje
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Debug - Guardar datos recibidos
error_log("Datos recibidos en enviar_mensaje.php: " . print_r($data, true));

if (!$data) {
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

if (empty($data['perro_id'])) {
    echo json_encode(['error' => 'ID del perro no proporcionado']);
    exit;
}

if (empty($data['mensaje'])) {
    echo json_encode(['error' => 'Mensaje no proporcionado']);
    exit;
}

try {
    $mensajeModel = new Mensaje();
    $usuario_id = $_SESSION['usuario_id'];
    $perro_id = intval($data['perro_id']);
    $mensaje = trim($data['mensaje']);

    // Debug - Guardar datos procesados
    error_log("Datos a insertar: usuario_id={$usuario_id}, perro_id={$perro_id}, mensaje={$mensaje}");

    // Verificar permisos
    if (!$mensajeModel->verificarPermiso($perro_id, $usuario_id)) {
        echo json_encode(['error' => 'No tienes permiso para enviar mensajes a este perro']);
        exit;
    }

    // Crear el mensaje usando el modelo
    $mensajeData = [
        'emisor_id' => $usuario_id,
        'perro_id' => $perro_id,
        'mensaje' => $mensaje
    ];

    $resultado = $mensajeModel->crear($mensajeData);

    if ($resultado) {
        error_log("Mensaje insertado correctamente con ID: " . $resultado['id']);
        echo json_encode([
            'success' => true,
            'mensaje_id' => $resultado['id']
        ]);
    } else {
        error_log("Error al crear el mensaje en el modelo");
        throw new Exception('Error al guardar el mensaje');
    }

} catch (Exception $e) {
    error_log("Excepción en enviar_mensaje.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error al enviar mensaje: ' . $e->getMessage()]);
} 