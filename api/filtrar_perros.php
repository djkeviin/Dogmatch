<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';
header('Content-Type: application/json');

try {
    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['usuario'])) {
        throw new Exception('Usuario no autenticado');
    }

    // Obtener datos del POST
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
    }
    
    // Inicializar variables de filtro
    $busqueda = $data['busqueda'] ?? '';
    $raza = $data['raza'] ?? '';
    $edad = $data['edad'] ?? '';
    $valoracion = $data['valoracion'] ?? '';

    // Obtener conexión a la base de datos
    $db = Conexion::getConexion();
    if (!$db) {
        throw new Exception('Error de conexión a la base de datos');
    }

    // Construir la consulta base
    $sql = "SELECT DISTINCT p.*, 
            GROUP_CONCAT(DISTINCT r.nombre SEPARATOR ', ') as razas,
            COALESCE(AVG(v.puntuacion), 0) as valoracion_promedio,
            COUNT(DISTINCT v.id) as total_valoraciones,
            u.nombre as nombre_dueno
            FROM perros p
            LEFT JOIN raza_perro rp ON p.id = rp.perro_id
            LEFT JOIN razas_perros r ON rp.raza_id = r.id
            LEFT JOIN valoraciones v ON p.id = v.perro_id
            LEFT JOIN usuarios u ON p.usuario_id = u.id";

    $where = [];
    $params = [];

    // Aplicar filtros
    if (!empty($busqueda)) {
        // Dividir la búsqueda en palabras para búsqueda más precisa
        $palabras = explode(' ', trim($busqueda));
        $condiciones_busqueda = [];
        
        foreach ($palabras as $palabra) {
            if (strlen($palabra) >= 2) { // Solo buscar palabras de 2 o más caracteres
                $condiciones_busqueda[] = "(
                    p.nombre LIKE ? OR 
                    r.nombre LIKE ? OR
                    u.nombre LIKE ?
                )";
                $params[] = "%$palabra%";
                $params[] = "%$palabra%";
                $params[] = "%$palabra%";
            }
        }
        
        if (!empty($condiciones_busqueda)) {
            $where[] = '(' . implode(' AND ', $condiciones_busqueda) . ')';
        }
    }

    if (!empty($raza)) {
        $where[] = "r.id = ?";
        $params[] = $raza;
    }

    if (!empty($edad)) {
        switch ($edad) {
            case '0-6':
                $where[] = "p.edad <= 6";
                break;
            case '7-12':
                $where[] = "p.edad > 6 AND p.edad <= 12";
                break;
            case '13+':
                $where[] = "p.edad > 12";
                break;
        }
    }

    if (!empty($valoracion)) {
        $where[] = "EXISTS (
            SELECT 1 
            FROM valoraciones v2 
            WHERE v2.perro_id = p.id 
            GROUP BY v2.perro_id 
            HAVING AVG(v2.puntuacion) >= ?
        )";
        $params[] = $valoracion;
    }

    // Agregar condiciones WHERE si existen
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    // Agrupar y ordenar resultados
    $sql .= " GROUP BY p.id";

    // Ordenar por relevancia si hay búsqueda, si no por nombre
    if (!empty($busqueda)) {
        $sql .= " ORDER BY 
                CASE 
                    WHEN p.nombre LIKE ? THEN 1
                    WHEN r.nombre LIKE ? THEN 2
                    ELSE 3
                END,
                p.nombre ASC";
        $params[] = "$busqueda%"; // Coincidencia exacta al inicio
        $params[] = "$busqueda%"; // Coincidencia exacta al inicio
    } else {
        $sql .= " ORDER BY p.nombre ASC";
    }

    // Preparar y ejecutar la consulta
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $perros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en la consulta SQL: " . $e->getMessage());
        throw new Exception("Error al ejecutar la consulta: " . $e->getMessage());
    }

    // Obtener las razas de cada perro
    foreach ($perros as &$perro) {
        // Obtener las razas específicas del perro
        $stmt = $db->prepare("
            SELECT r.nombre, rp.porcentaje
            FROM raza_perro rp
            JOIN razas_perros r ON rp.raza_id = r.id
            WHERE rp.perro_id = ?
            ORDER BY rp.porcentaje DESC
        ");
        $stmt->execute([$perro['id']]);
        $razas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear las razas con porcentajes
        $razas_formateadas = [];
        foreach ($razas as $raza) {
            $razas_formateadas[] = $raza['nombre'] . 
                                 ($raza['porcentaje'] < 100 ? " ({$raza['porcentaje']}%)" : '');
        }
        $perro['razas'] = implode(', ', $razas_formateadas);
    }

    // Devolver resultados
    echo json_encode([
        'success' => true,
        'perros' => $perros,
        'total' => count($perros)
    ]);

} catch (Exception $e) {
    error_log("Error en filtrar_perros.php: " . $e->getMessage());
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