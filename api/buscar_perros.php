<?php
// Evitar cualquier salida antes del JSON
ob_start();

// Reportar todos los errores excepto E_NOTICE
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Perro.php';

// Limpiar cualquier salida anterior
ob_clean();

header('Content-Type: application/json');

try {
    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['usuario'])) {
        throw new Exception('Usuario no autenticado');
    }

    // Verificar el método de la solicitud
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener y validar los datos de la solicitud
    $jsonData = file_get_contents('php://input');
    if (!$jsonData) {
        throw new Exception('No se recibieron datos');
    }

    $data = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Datos JSON inválidos: ' . json_last_error_msg());
    }

    $perroModel = new Perro();
    
    // Construir los criterios de búsqueda
    $criterios = [
        'razas' => $data['breeds'] ?? [],
        'tamanios' => $data['sizes'] ?? [],
        'edad_min' => !empty($data['ageMin']) ? intval($data['ageMin']) : null,
        'edad_max' => !empty($data['ageMax']) ? intval($data['ageMax']) : null,
        'vacunado' => $data['health']['vaccinated'] ?? false,
        'esterilizado' => $data['health']['sterilized'] ?? false,
        'distancia_max' => !empty($data['distance']) ? floatval($data['distance']) : 50
    ];

    // Obtener la ubicación del usuario actual
    $ubicacionUsuario = [
        'latitud' => $_SESSION['usuario']['latitud'] ?? null,
        'longitud' => $_SESSION['usuario']['longitud'] ?? null
    ];

    if ($ubicacionUsuario['latitud'] === null || $ubicacionUsuario['longitud'] === null) {
        throw new Exception('La ubicación del usuario no está disponible');
    }

    // Buscar perros que coincidan con los criterios
    $resultados = $perroModel->buscarPerrosConFiltros($criterios, $ubicacionUsuario);
    
    // Asegurarse de que los resultados sean serializables
    $resultados = array_map(function($perro) {
        return array_map(function($valor) {
            return is_numeric($valor) ? (float)$valor : $valor;
        }, $perro);
    }, $resultados);

    echo json_encode($resultados, JSON_THROW_ON_ERROR);

} catch (Exception $e) {
    error_log("Error en buscar_perros.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)
    ], JSON_THROW_ON_ERROR);
}

// Asegurarse de que no haya más salida
ob_end_flush(); 