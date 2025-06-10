<?php
require_once '../config/database.php';
header('Content-Type: application/json');

// Verificar si hay una sesión activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Obtener datos del POST
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['latitud']) || !isset($data['longitud']) || !isset($data['radio'])) {
        throw new Exception('Faltan parámetros requeridos');
    }

    $latitud = floatval($data['latitud']);
    $longitud = floatval($data['longitud']);
    $radio = floatval($data['radio']); // en kilómetros
    $usuario_id = $_SESSION['user_id'] ?? 0;

    // Fórmula Haversine para calcular distancia
    $sql = "
        SELECT 
            p.*,
            r.nombre as raza,
            (
                6371 * acos(
                    cos(radians(?)) * cos(radians(p.latitud)) *
                    cos(radians(p.longitud) - radians(?)) +
                    sin(radians(?)) * sin(radians(p.latitud))
                )
            ) AS distancia,
            CASE WHEN m.id IS NOT NULL THEN 1 ELSE 0 END as match
        FROM perros p
        LEFT JOIN perro_raza pr ON p.id = pr.perro_id AND pr.es_principal = 1
        LEFT JOIN razas r ON pr.raza_id = r.id
        LEFT JOIN matches m ON (
            (m.perro1_id = p.id AND m.perro2_id = ?) OR
            (m.perro1_id = ? AND m.perro2_id = p.id)
        ) AND m.estado = 'aceptado'
        WHERE p.usuario_id != ?
        HAVING distancia <= ?
        ORDER BY distancia
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $latitud,
        $longitud,
        $latitud,
        $usuario_id,
        $usuario_id,
        $usuario_id,
        $radio
    ]);

    $perros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear la respuesta
    echo json_encode([
        'success' => true,
        'perros' => array_map(function($perro) {
            return [
                'id' => $perro['id'],
                'nombre' => $perro['nombre'],
                'foto' => $perro['foto'] ?? 'default-dog.jpg',
                'raza' => $perro['raza'],
                'edad' => $perro['edad'],
                'latitud' => floatval($perro['latitud']),
                'longitud' => floatval($perro['longitud']),
                'match' => $perro['match'] == 1,
                'distancia' => round($perro['distancia'], 1)
            ];
        }, $perros)
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 