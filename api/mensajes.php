<?php
require_once '../config/conexion.php';
require_once '../models/Mensaje.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar sesión
session_start();
if (!isset($_SESSION['usuario']['id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Usuario no autenticado'
    ]);
    exit;
}

$mensajeModel = new Mensaje();
$usuario_id = $_SESSION['usuario']['id'];

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'conversaciones':
                    // Obtener lista de conversaciones
                    $conversaciones = $mensajeModel->obtenerConversaciones($usuario_id);
                    echo json_encode([
                        'success' => true,
                        'conversaciones' => $conversaciones
                    ]);
                    break;

                case 'mensajes':
                    // Validar parámetros
                    if (!isset($_GET['perro_id'])) {
                        throw new Exception('ID del perro no especificado');
                    }

                    $perro_id = intval($_GET['perro_id']);
                    $ultimo_id = isset($_GET['ultimo_id']) ? intval($_GET['ultimo_id']) : 0;

                    // Verificar permiso
                    if (!$mensajeModel->verificarPermiso($perro_id, $usuario_id)) {
                        throw new Exception('No tienes permiso para ver estos mensajes');
                    }

                    // Obtener mensajes
                    $mensajes = $mensajeModel->obtenerMensajes($perro_id, $usuario_id, $ultimo_id);
                    echo json_encode([
                        'success' => true,
                        'mensajes' => $mensajes
                    ]);
                    break;

                default:
                    throw new Exception('Acción no válida');
            }
            break;

        case 'POST':
            // Obtener datos del cuerpo de la petición
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['perro_id']) || !isset($data['mensaje'])) {
                throw new Exception('Faltan datos requeridos');
            }

            $perro_id = intval($data['perro_id']);

            // Verificar permiso
            if (!$mensajeModel->verificarPermiso($perro_id, $usuario_id)) {
                throw new Exception('No tienes permiso para enviar mensajes a este perro');
            }

            // Crear mensaje
            $mensaje = $mensajeModel->crear([
                'emisor_id' => $usuario_id,
                'perro_id' => $perro_id,
                'mensaje' => $data['mensaje']
            ]);

            echo json_encode([
                'success' => true,
                'mensaje' => $mensaje
            ]);
            break;

        default:
            throw new Exception('Método no permitido');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 