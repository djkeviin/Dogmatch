<?php
require_once '../config/database.php';
header('Content-Type: application/json');

try {
    // Obtener datos del POST
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Inicializar variables de filtro
    $busqueda = $data['busqueda'] ?? '';
    $raza = $data['raza'] ?? '';
    $edad = $data['edad'] ?? '';
    $valoracion = $data['valoracion'] ?? '';

    // Construir la consulta base
    $sql = "SELECT DISTINCT p.*, 
            GROUP_CONCAT(DISTINCT r.nombre SEPARATOR ', ') as razas,
            COALESCE(AVG(v.puntuacion), 0) as valoracion_promedio,
            COUNT(DISTINCT v.id) as total_valoraciones,
            u.nombre as nombre_dueno
            FROM perros p
            LEFT JOIN perro_raza pr ON p.id = pr.perro_id
            LEFT JOIN razas r ON pr.raza_id = r.id
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
            case 'cachorro':
                $where[] = "p.edad <= 12";
                break;
            case 'joven':
                $where[] = "p.edad > 12 AND p.edad <= 84";
                break;
            case 'adulto':
                $where[] = "p.edad > 84";
                break;
        }
    }

    if (!empty($valoracion)) {
        $where[] = "COALESCE(AVG(v.puntuacion), 0) >= ?";
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
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $perros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener las razas de cada perro
    foreach ($perros as &$perro) {
        // Obtener las razas específicas del perro
        $stmt = $conn->prepare("
            SELECT r.nombre, pr.porcentaje
            FROM perro_raza pr
            JOIN razas r ON pr.raza_id = r.id
            WHERE pr.perro_id = ?
            ORDER BY pr.es_principal DESC, pr.porcentaje DESC
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
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 