<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

require_once '../../models/Mensaje.php';
require_once '../../models/Usuario.php';

$usuario_id = $_SESSION['usuario_id'];
$conversacion_activa_id = isset($_GET['conversacion_activa_id']) ? intval($_GET['conversacion_activa_id']) : null;
$ultimo_timestamp = isset($_GET['ultimo_timestamp']) ? intval($_GET['ultimo_timestamp']) : 0;

$mensajeModel = new Mensaje();
$usuarioModel = new Usuario();

try {
    $respuesta = [
        'success' => true,
        'mensajes' => [],
        'conversaciones' => [],
        'timestamp' => time()
    ];

    // 1. Obtener mensajes nuevos para la conversación activa
    if ($conversacion_activa_id) {
        $nuevos_mensajes = $mensajeModel->obtenerMensajesDesde($conversacion_activa_id, $usuario_id, $ultimo_timestamp);
        foreach ($nuevos_mensajes as $msg) {
            $respuesta['mensajes'][] = [
                'id' => $msg['id'],
                'mensaje' => $msg['mensaje'],
                'fecha_envio' => $msg['fecha_envio'],
                'es_emisor' => $msg['emisor_id'] == $usuario_id,
                'id_temporal' => $msg['id_temporal'] ?? null,
                'multimedia_url' => $msg['multimedia_url'] ?? null
            ];
        }
    }

    // 2. Obtener conversaciones actualizadas (con nuevos mensajes o cambios de estado)
    $conversaciones_actualizadas = $mensajeModel->obtenerConversacionesActualizadas($usuario_id, $ultimo_timestamp);
    foreach ($conversaciones_actualizadas as $conv) {
        $respuesta['conversaciones'][] = [
            'perro_id' => $conv['perro_id'],
            'nombre' => $conv['nombre'],
            'foto' => $conv['foto'],
            'ultimo_mensaje' => $conv['ultimo_mensaje'],
            'no_leidos' => $conv['no_leidos'],
            'online' => (time() - strtotime($conv['ultima_actividad'])) < 300 // 5 minutos de inactividad
        ];
    }

    // 3. Marcar mensajes como leídos si la conversación está activa
    if ($conversacion_activa_id && count($respuesta['mensajes']) > 0) {
        $mensajeModel->marcarComoLeido($conversacion_activa_id, $usuario_id);
    }

    echo json_encode($respuesta);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error en actualizar_chat.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error DETALLADO: ' . $e->getMessage()]);
} 