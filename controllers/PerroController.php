<?php
require_once __DIR__ . '/../models/Perro.php';

class PerroController {
    private $perroModel;

    public function __construct() {
        $this->perroModel = new Perro();
    }

    public function verPerfil() {
        session_start();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ../views/auth/login.php');
            exit;
        }

        $usuarioId = $_SESSION['usuario_id'];
        $perfil = $this->perroModel->obtenerPerfilCompletoPorUsuarioId($usuarioId);

        if (!$perfil) {
            header('Location: ../views/auth/dashboard.php?error=No tienes un perro registrado');
            exit;
        }

        // Enviar datos a la vista
        include __DIR__ . '/../views/auth/perfil.php';
    }

    public function registrar($data, $files, $usuarioId) {
        // Validar si ya tiene un perro
        if ($this->perroModel->obtenerPerfilCompletoPorUsuarioId($usuarioId)) {
            header('Location: ../views/auth/dashboard.php?error=ya_existe_perro');
            exit;
        }

        // Validar campos
        if (empty($data['nombre_perro']) || empty($data['raza']) || empty($data['edad']) || empty($data['sexo']) || empty($files['foto'])) {
            header('Location: ../views/auth/dashboard.php?error=Faltan datos obligatorios');
            exit;
        }

        // Manejo de foto
        $fotoNombre = basename($files['foto']['name']);
        $rutaDestino = __DIR__ . '/../public/img/' . $fotoNombre;

        if (!move_uploaded_file($files['foto']['tmp_name'], $rutaDestino)) {
            header('Location: ../views/auth/dashboard.php?error=Error al subir la imagen');
            exit;
        }

        // Guardar perro en BD
        $this->perroModel->crear([
            'nombre' => $data['nombre_perro'],
            'raza' => $data['raza'],
            'edad' => $data['edad'],
            'sexo' => $data['sexo'],
            'foto' => $fotoNombre,
            'usuario_id' => $usuarioId
        ]);

        header('Location: ../views/auth/dashboard.php?mensaje=Perro agregado correctamente');
        exit;
    }

    public function actualizarPerfil() {
        session_start();

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
            'raza' => $_POST['raza'],
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
            'condiciones_apareamiento' => $_POST['condiciones_apareamiento'],
            'usuario_id' => $usuario_id
        ];

        try {
            $this->perroModel->actualizar($data);
            $_SESSION['mensaje'] = "Perfil actualizado correctamente";
            header('Location: ../views/auth/perfil.php?actualizado=1');
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al actualizar el perfil: " . $e->getMessage();
            header('Location: ../views/auth/perfil.php?error=1');
        }
        exit;
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
