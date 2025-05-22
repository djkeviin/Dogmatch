<?php
require_once __DIR__ . '/../models/Perro.php';

class PerroController {
    private $perroModel;

    public function __construct() {
        $this->perroModel = new Perro();
    }

    public function registrar($data, $files, $usuarioId) {
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
}


