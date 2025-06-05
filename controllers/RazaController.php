<?php
require_once __DIR__ . '/../models/RazaPerro.php';

class RazaController {
    private $model;

    public function __construct() {
        $this->model = new RazaPerro();
    }

    /**
     * Busca razas que coincidan con el término de búsqueda
     */
    public function buscar() {
        header('Content-Type: application/json');
        
        try {
            $q = $_GET['q'] ?? '';
            $razas = $this->model->buscarRazas($q);
            
            if (!is_array($razas)) {
                throw new Exception('Error al obtener las razas');
            }
            
            echo json_encode($razas);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'mensaje' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene una raza por su ID
     */
    public function obtenerPorId($id) {
        return $this->model->obtenerPorId($id);
    }

    /**
     * Obtiene todas las razas
     */
    public function obtenerTodas() {
        return $this->model->obtenerTodas();
    }
}

// Procesar la acción si se recibe una solicitud directa
if (isset($_GET['action'])) {
    $controller = new RazaController();
    
    try {
        switch ($_GET['action']) {
            case 'buscar':
                $controller->buscar();
                break;
            default:
                throw new Exception('Acción no válida');
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'error' => true,
            'mensaje' => $e->getMessage()
        ]);
    }
} 