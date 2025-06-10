<?php
require_once '../config/database.php';
header('Content-Type: application/json');

// Verificar si el usuario está autenticado
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Usuario no autenticado'
    ]);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'POST':
            // Agregar nueva valoración
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['perro_id']) || !isset($data['puntuacion'])) {
                throw new Exception('Faltan datos requeridos');
            }

            // Verificar si el usuario ya valoró este perro
            $stmt = $conn->prepare("SELECT id FROM valoraciones WHERE usuario_id = ? AND perro_id = ?");
            $stmt->execute([$_SESSION['user_id'], $data['perro_id']]);
            
            if ($stmt->fetch()) {
                // Actualizar valoración existente
                $sql = "UPDATE valoraciones SET puntuacion = ?, fecha_actualizacion = NOW() 
                        WHERE usuario_id = ? AND perro_id = ?";
            } else {
                // Insertar nueva valoración
                $sql = "INSERT INTO valoraciones (usuario_id, perro_id, puntuacion, fecha_creacion) 
                        VALUES (?, ?, ?, NOW())";
            }

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $data['puntuacion'],
                $_SESSION['user_id'],
                $data['perro_id']
            ]);

            // Obtener promedio actualizado
            $stmt = $conn->prepare("SELECT AVG(puntuacion) as promedio, COUNT(*) as total 
                                  FROM valoraciones WHERE perro_id = ?");
            $stmt->execute([$data['perro_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'promedio' => round($result['promedio'], 1),
                'total' => $result['total']
            ]);
            break;

        case 'GET':
            // Obtener valoraciones de un perro
            if (!isset($_GET['perro_id'])) {
                throw new Exception('ID del perro no especificado');
            }

            $stmt = $conn->prepare("SELECT v.*, u.nombre as usuario_nombre 
                                  FROM valoraciones v 
                                  JOIN usuarios u ON v.usuario_id = u.id 
                                  WHERE v.perro_id = ? 
                                  ORDER BY v.fecha_creacion DESC");
            $stmt->execute([$_GET['perro_id']]);
            $valoraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'valoraciones' => $valoraciones
            ]);
            break;

        default:
            throw new Exception('Método no permitido');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 