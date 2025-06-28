<?php
require_once '../config/conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar sesión
session_start();
if (!isset($_SESSION['usuario']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['perro_id']) || !isset($data['puntuacion'])) {
        throw new Exception('Faltan datos requeridos');
    }

    $perro_id = intval($data['perro_id']);
    $puntuacion = intval($data['puntuacion']);
    $usuario_id = $_SESSION['usuario']['id'];

    // Validar puntuación
    if ($puntuacion < 1 || $puntuacion > 5) {
        throw new Exception('La puntuación debe estar entre 1 y 5');
    }

    $conn = Conexion::getConexion();

    // Verificar si el usuario ya valoró este perro
    $stmt = $conn->prepare("SELECT id FROM valoraciones WHERE usuario_id = ? AND perro_id = ?");
    $stmt->execute([$usuario_id, $perro_id]);
    $valoracion_existente = $stmt->fetch();

    if ($valoracion_existente) {
        // Actualizar valoración existente
        $stmt = $conn->prepare("
            UPDATE valoraciones 
            SET puntuacion = ?, 
                fecha_actualizacion = NOW() 
            WHERE usuario_id = ? AND perro_id = ?
        ");
        $stmt->execute([$puntuacion, $usuario_id, $perro_id]);
    } else {
        // Insertar nueva valoración
        $stmt = $conn->prepare("
            INSERT INTO valoraciones (usuario_id, perro_id, puntuacion, fecha_creacion) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$usuario_id, $perro_id, $puntuacion]);
    }

    // *** INICIO: LÓGICA DE NOTIFICACIÓN ***
    require_once '../models/Notificacion.php';
    require_once '../models/Perro.php';

    $perroModel = new Perro();
    $perro_valorado = $perroModel->obtenerPorId($perro_id);
    $usuario_que_valora = $_SESSION['usuario'];

    // Solo notificar si no es el propio dueño valorando
    if ($perro_valorado && $perro_valorado['usuario_id'] != $usuario_id) {
        $notificacion = new Notificacion();
        $mensaje = "¡<b>" . htmlspecialchars($usuario_que_valora['nombre']) . "</b> ha valorado a <b>" . htmlspecialchars($perro_valorado['nombre']) . "</b> con " . $puntuacion . " estrellas!";
        $url = "../auth/perfil.php?id=" . $perro_id;
        $notificacion->crear($perro_valorado['usuario_id'], 'nueva_valoracion', $mensaje, $url);
    }
    // *** FIN: LÓGICA DE NOTIFICACIÓN ***

    // Obtener promedio actualizado
    $stmt = $conn->prepare("
        SELECT 
            AVG(puntuacion) as promedio,
            COUNT(*) as total
        FROM valoraciones 
        WHERE perro_id = ?
    ");
    $stmt->execute([$perro_id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'mensaje' => $valoracion_existente ? 'Valoración actualizada' : 'Valoración agregada',
        'promedio' => round($resultado['promedio'], 1),
        'total' => $resultado['total']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 