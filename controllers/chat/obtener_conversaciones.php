<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../models/Mensaje.php';
require_once '../../models/Perro.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

try {
    $db = Conexion::getConexion();
    $usuario_id = $_SESSION['usuario_id'];

    // Obtener todas las conversaciones del usuario
    $sql = "SELECT DISTINCT 
                p.id,
                p.nombre,
                p.foto,
                p.usuario_id,
                (SELECT mensaje 
                 FROM mensajes 
                 WHERE perro_id = p.id
                 ORDER BY fecha_envio DESC 
                 LIMIT 1) as ultimo_mensaje
            FROM perros p
            INNER JOIN mensajes m ON m.perro_id = p.id
            WHERE 
                (m.emisor_id = ? OR m.perro_id IN (
                    SELECT id FROM perros WHERE usuario_id = ?
                ))
            GROUP BY p.id
            ORDER BY MAX(m.fecha_envio) DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute([$usuario_id, $usuario_id]);
    $conversaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver las conversaciones en formato JSON
    header('Content-Type: application/json');
    echo json_encode($conversaciones);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al obtener conversaciones: ' . $e->getMessage()]);
} 