<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../config/conexion.php';
global $conn;
$conn = Conexion::getConexion();

require_once __DIR__ . '/../controllers/PerroController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/RazaController.php';
require_once __DIR__ . '/../controllers/MatchesController.php';

// Instanciar controladores
$perroController = new PerroController();
$authController = new AuthController();
$razaController = new RazaController();
$matchesController = new MatchesController();

// Obtener la ruta de la URL
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/Ignis360/Dogmatch';
$route = str_replace($base_path, '', parse_url($request_uri, PHP_URL_PATH));

// Verificar si es una petición API
if (strpos($route, '/api/') === 0) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    // Si es una petición OPTIONS, terminar aquí (para CORS)
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }

    try {
        // Remover '/api' del inicio de la ruta
        $api_route = substr($route, 4);
        error_log("API Route: " . $api_route);
        
        switch ($api_route) {
            case '/perros/buscar':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
                    }
                    $result = $perroController->buscar($data);
                    echo json_encode(['success' => true, 'data' => $result]);
                } else {
                    throw new Exception('Método no permitido');
                }
                break;

            case '/razas/buscar':
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $query = $_GET['q'] ?? '';
                    error_log("Búsqueda de razas con query: " . $query);
                    $result = $razaController->buscarRazas($query);
                    error_log("Resultado de búsqueda de razas: " . json_encode($result));
                    echo json_encode(['success' => true, 'data' => $result]);
                } else {
                    throw new Exception('Método no permitido');
                }
                break;

            case '/matches/crear':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
                    }
                    $result = $matchesController->crear($data);
                    echo json_encode(['success' => true, 'data' => $result]);
                } else {
                    throw new Exception('Método no permitido');
                }
                break;

            case '/perros/cercanos':
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $lat = $_GET['lat'] ?? null;
                    $lng = $_GET['lng'] ?? null;
                    $rango = $_GET['rango'] ?? 5;
                    $result = $perroController->buscarCercanos($lat, $lng, $rango);
                    echo json_encode(['success' => true, 'data' => $result]);
                } else {
                    throw new Exception('Método no permitido');
                }
                break;

            default:
                http_response_code(404);
                echo json_encode(['error' => 'Ruta API no encontrada']);
                break;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Rutas web normales
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'perros/buscar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Error al decodificar JSON: ' . json_last_error_msg()]);
                exit;
            }
            try {
                $perros = $perroController->buscar($data);
                echo json_encode(['success' => true, 'data' => $perros]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }
        break;

    case 'registrar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->registrar($_POST, $_FILES);
        } else {
            include '../views/auth/registro.php';
        }
        break;

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $authController->login($email, $password);
        } else {
            include '../views/auth/login.php';
        }
        break;
   
        
    case 'perfil':
        $perroController->verPerfil();
        break;

    case 'actualizar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $perroController->actualizarPerfil();
        } else {
            header('Location: ?action=perfil');
        }
        break;

    case 'matches':
        include '../views/match/perros_cards.php';
        break;

    case 'ver-matches':
        include '../views/match/ver_match.php';
        break;

    case 'dashboard':
        include '../views/auth/dashboard.php';
        break;

    case 'logout':
        $authController->logout();
        break;

    case 'razas/buscar':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $query = $_GET['q'] ?? '';
            error_log("Búsqueda de razas con query: " . $query);
            $result = $razaController->buscarRazas($query);
            error_log("Resultado de búsqueda de razas: " . json_encode($result));
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            throw new Exception('Método no permitido');
        }
        break;

    default:
        include '../views/auth/index.php';
        break;
}

