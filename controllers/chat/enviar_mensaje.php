<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 1. Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// 2. Decodificar y validar datos de entrada
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['perro_destinatario_id']) || !isset($data['mensaje']) || empty(trim($data['mensaje']))) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos o mensaje vacío.']);
    exit;
}

// 3. Sanitizar y preparar variables
require_once '../../models/Mensaje.php';
require_once '../../models/Perro.php';
require_once '../../models/MatchPerro.php';

$mensajeModel = new Mensaje();
$perroModel = new Perro();
$matchModel = new MatchPerro();

$emisor_id = $_SESSION['usuario_id'];
$perro_destinatario_id = filter_var($data['perro_destinatario_id'], FILTER_VALIDATE_INT);
$mensaje = trim($data['mensaje']);
$id_temporal = $data['id_temporal'] ?? null;

if (!$perro_destinatario_id) {
    echo json_encode(['success' => false, 'error' => 'ID de destinatario inválido.']);
    exit;
}

try {
    // 4. Verificar permiso para chatear (existencia de un match)
    $perros_del_emisor = $perroModel->obtenerPorUsuario($emisor_id);
    if (empty($perros_del_emisor)) {
        echo json_encode(['success' => false, 'error' => 'No tienes un perro asignado para chatear.']);
        exit;
    }
    
    // Suponemos que el usuario chatea con su primer perro. Esto se puede ampliar en el futuro.
    $perro_emisor_id = $perros_del_emisor[0]['id']; 

    $tiene_match = $matchModel->esMatch($perro_emisor_id, $perro_destinatario_id);
    if (!$tiene_match) {
        // Permitir chatear con uno mismo (por si se abre el chat con el propio perro)
        $perro_destino_info = $perroModel->obtenerPorId($perro_destinatario_id);
        if (!$perro_destino_info || $perro_destino_info['usuario_id'] != $emisor_id) {
             echo json_encode(['success' => false, 'error' => 'No puedes enviar mensajes si no hay un match mutuo.']);
             exit;
        }
    }

    // 5. Enviar el mensaje
    $nuevo_id = $mensajeModel->enviarMensaje($perro_destinatario_id, $emisor_id, $mensaje, $id_temporal);
    
    if ($nuevo_id) {
        echo json_encode(['success' => true, 'nuevo_id' => $nuevo_id, 'id_temporal' => $id_temporal]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo guardar el mensaje (el modelo no devolvió un ID).']);
    }

} catch (Exception $e) {
    // Log del error real para depuración
    error_log('Error en enviar_mensaje.php: ' . $e->getMessage());
    // Devolver el mensaje de error exacto para depuración
    echo json_encode(['success' => false, 'error' => 'Error DETALLADO: ' . $e->getMessage()]);
} 