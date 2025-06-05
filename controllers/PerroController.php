<?php
require_once __DIR__ . '/../models/Perro.php';

class PerroController {
    private $model;

    public function __construct() {
        $this->model = new Perro();
    }

    /**
     * Busca perros según los filtros proporcionados
     */
    public function buscar($data) {
        try {
            $perros = $this->model->buscarPerros($data);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $perros
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Actualiza el perfil de un perro
     */
    public function actualizar($data) {
        return $this->model->actualizar($data);
    }

    /**
     * Obtiene el perfil completo de un perro por ID de usuario
     */
    public function obtenerPerfil($usuario_id) {
        return $this->model->obtenerPerfilCompletoPorUsuarioId($usuario_id);
    }

    public function verPerfil() {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ../views/auth/login.php');
            exit;
        }

        $usuarioId = $_SESSION['usuario']['id'];
        $perfil = $this->model->obtenerPerfilCompletoPorUsuarioId($usuarioId);

        if (!$perfil) {
            header('Location: ../views/auth/dashboard.php?error=No tienes un perro registrado');
            exit;
        }

        // Obtener las razas del perro
        $razas = $this->model->obtenerRazasPerro($perfil['id']);
        $perfil['razas'] = $razas;
        
        // Si hay una raza principal, usarla como la raza principal del perro
        if (!empty($razas)) {
            foreach ($razas as $raza) {
                if ($raza['es_principal']) {
                    $perfil['raza'] = $raza['nombre'];
                    break;
                }
            }
        }

        // Enviar datos a la vista
        include __DIR__ . '/../views/auth/perfil.php';
    }

    public function actualizarPerfil() {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ../views/auth/login.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/auth/perfil.php');
            exit;
        }

        $usuario_id = $_SESSION['usuario']['id'];

        // Preparar los datos para actualizar
        $data = [
            'nombre' => $_POST['nombre'],
            'edad' => $_POST['edad'],
            'sexo' => $_POST['sexo'],
            'peso' => $_POST['peso'] ?: null,
            'descripcion' => $_POST['descripcion'],
            'temperamento' => $_POST['temperamento'],
            'sociable_perros' => isset($_POST['sociable_perros']) ? 1 : 0,
            'sociable_personas' => isset($_POST['sociable_personas']) ? 1 : 0,
            'estado_salud' => $_POST['estado_salud'],
            'vacunas' => $_POST['vacunas'],
            'esterilizado' => isset($_POST['esterilizado']) ? 1 : 0,
            'disponible_apareamiento' => isset($_POST['disponible_apareamiento']) ? 1 : 0,
            'condiciones_apareamiento' => $_POST['condiciones_apareamiento'] ?? null,
            'pedigri' => isset($_POST['pedigri']) ? 1 : 0,
            'usuario_id' => $usuario_id
        ];

        try {
            // Si se proporcionó una nueva raza, incluirla en los datos
            if (!empty($_POST['raza'])) {
                $data['raza'] = $_POST['raza'];
            }
            
            // Actualizar toda la información del perro
            $perro_id = $this->model->actualizar($data);
            
            $_SESSION['mensaje'] = "Perfil actualizado correctamente";
            header('Location: ../views/auth/perfil.php?actualizado=1');
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al actualizar el perfil: " . $e->getMessage();
            header('Location: ../views/auth/perfil.php?error=1');
        }
        exit;
    }

    /**
     * Busca perros cercanos a una ubicación
     */
    public function buscarCercanos($lat, $lng, $rango) {
        if ($lat === null || $lng === null) {
            return [];
        }
        return $this->model->buscarPerrosCercanos($lat, $lng, $rango);
    }
}

// Procesar la acción si se recibe una solicitud directa
if (isset($_POST['action'])) {
    $controller = new PerroController();
    switch ($_POST['action']) {
        case 'actualizar':
            $controller->actualizarPerfil();
            break;
    }
}
