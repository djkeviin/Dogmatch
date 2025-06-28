<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';
header('Content-Type: application/json');

try {
    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['usuario'])) {
        throw new Exception('Usuario no autenticado');
    }

    // Obtener conexión a la base de datos
    $db = Conexion::getConexion();
    if (!$db) {
        throw new Exception('Error de conexión a la base de datos');
    }

    // Verificar si la tabla razas_perros existe
    $stmt = $db->query("SHOW TABLES LIKE 'razas_perros'");
    if ($stmt->rowCount() === 0) {
        throw new Exception('La tabla razas_perros no existe en la base de datos');
    }

    // Verificar la estructura de la tabla razas_perros
    $stmt = $db->query("DESCRIBE razas_perros");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($columnas)) {
        throw new Exception('La tabla razas_perros está vacía o no tiene la estructura correcta');
    }

    // Obtener razas
    $stmt = $db->query("SELECT id, nombre FROM razas_perros ORDER BY nombre");
    $razas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener rangos de edad disponibles
    $stmt = $db->query("
        SELECT 
            CASE 
                WHEN edad <= 6 THEN '0-6'
                WHEN edad <= 12 THEN '7-12'
                ELSE '13+'
            END as rango_edad,
            COUNT(*) as total
        FROM perros 
        GROUP BY rango_edad
        ORDER BY MIN(edad)
    ");
    $rangos_edad = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener rangos de valoración disponibles
    $stmt = $db->query("
        SELECT 
            FLOOR(AVG(puntuacion)) as valoracion,
            COUNT(*) as total
        FROM valoraciones 
        GROUP BY FLOOR(puntuacion)
        HAVING valoracion >= 2
        ORDER BY valoracion DESC
    ");
    $valoraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver todos los datos de filtro
    echo json_encode([
        'success' => true,
        'filtros' => [
            'razas' => $razas,
            'rangos_edad' => $rangos_edad,
            'valoraciones' => $valoraciones
        ]
    ]);

} catch (Exception $e) {
    error_log("Error en filtros.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug_info' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
} 