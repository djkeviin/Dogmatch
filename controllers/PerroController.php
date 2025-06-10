<?php
require_once __DIR__ . '/../models/Perro.php';
require_once __DIR__ . '/../models/RazaPerro.php';

class PerroController {
    private $model;
    private $razaPerroModel;
    private $uploadDir = __DIR__ . '/../public/img/';

    public function __construct() {
        session_start();
        $this->model = new Perro();
        $this->razaPerroModel = new RazaPerro();
        
        // Asegurar que el directorio de subida existe
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
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
    public function actualizar($perro_id, $data) {
        if (!$perro_id || empty($data)) {
            throw new Exception("Datos inválidos para actualizar");
        }
        return $this->model->actualizar($perro_id, $data);
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

        // Obtener las razas del perro usando el modelo correcto
        $razas = $this->razaPerroModel->obtenerPorPerroId($perfil['id']);
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

    /**
     * Maneja la subida de una foto
     */
    private function handlePhotoUpload($file) {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF.');
        }

        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            throw new Exception('El archivo es demasiado grande. Máximo 5MB.');
        }

        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $this->uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Error al subir el archivo.');
        }

        return $fileName;
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

        try {
            $usuario_id = $_SESSION['usuario']['id'];
            
            // Obtener el perro actual
            $perro = $this->model->obtenerUnicoPorUsuarioId($usuario_id);
            if (!$perro) {
                throw new Exception("No se encontró el perro para actualizar");
            }

            // Validar los datos recibidos
            $this->validarDatosActualizacion($_POST);

            // Manejar la subida de foto si se proporcionó una nueva
            $fotoNombre = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $fotoNombre = $this->handlePhotoUpload($_FILES['foto']);
            }

            // Preparar los datos para actualizar
            $data = [
                'nombre' => trim($_POST['nombre']),
                'edad' => intval($_POST['edad']),
                'peso' => !empty($_POST['peso']) ? floatval($_POST['peso']) : null,
                'sexo' => in_array($_POST['sexo'], ['Macho', 'Hembra']) ? $_POST['sexo'] : 'Macho',
                'tamanio' => in_array($_POST['tamanio'], ['pequeño', 'mediano', 'grande']) ? $_POST['tamanio'] : 'mediano',
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'vacunado' => isset($_POST['vacunado']) ? true : false,
                'sociable_perros' => isset($_POST['sociable_perros']) ? true : false,
                'sociable_personas' => isset($_POST['sociable_personas']) ? true : false,
                'pedigri' => isset($_POST['pedigri']) ? true : false,
                'temperamento' => trim($_POST['temperamento'] ?? ''),
                'estado_salud' => trim($_POST['estado_salud'] ?? ''),
                'vacunas' => trim($_POST['vacunas'] ?? ''),
                'disponible_apareamiento' => isset($_POST['disponible_apareamiento']) ? true : false,
                'condiciones_apareamiento' => trim($_POST['condiciones_apareamiento'] ?? '')
            ];

            // Si se subió una nueva foto, incluirla en los datos
            if ($fotoNombre) {
                $data['foto'] = $fotoNombre;
                
                // Eliminar la foto anterior si existe
                if (!empty($perro['foto'])) {
                    $fotoAnterior = $this->uploadDir . $perro['foto'];
                    if (file_exists($fotoAnterior)) {
                        unlink($fotoAnterior);
                    }
                }
            }

            // Actualizar el perfil
            $this->model->actualizar($perro['id'], $data);

            // Si se proporcionó una raza, actualizar la relación
            if (!empty($_POST['raza'])) {
                $this->razaPerroModel->actualizarRazaPrincipal($perro['id'], $_POST['raza']);
            }

            $_SESSION['mensaje'] = "Perfil actualizado correctamente";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ../views/auth/perfil.php');
        exit;
    }

    private function validarDatosActualizacion($datos) {
        if (empty($datos['nombre'])) {
            throw new Exception("El nombre es obligatorio");
        }
        
        if (!isset($datos['edad']) || !is_numeric($datos['edad']) || $datos['edad'] < 0) {
            throw new Exception("La edad debe ser un número válido");
        }
        
        if (!empty($datos['peso']) && (!is_numeric($datos['peso']) || $datos['peso'] < 0)) {
            throw new Exception("El peso debe ser un número válido");
        }
        
        if (!in_array($datos['sexo'], ['Macho', 'Hembra'])) {
            throw new Exception("El sexo debe ser 'Macho' o 'Hembra'");
        }
        
        if (!empty($datos['tamanio']) && !in_array($datos['tamanio'], ['pequeño', 'mediano', 'grande'])) {
            throw new Exception("El tamaño debe ser 'pequeño', 'mediano' o 'grande'");
        }
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
