<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perro.php';

class AuthController {

    public function registrar($data, $files) {
        // Validación básica
        if (empty($data['nombre_dueño']) || empty($data['email']) || empty($data['password']) ||
            empty($data['telefono']) || empty($data['nombre_perro']) || empty($data['raza']) ||
            empty($data['edad']) || empty($data['sexo']) || empty($files['foto'])) {
            
            header('Location: ../views/auth/registro.php?error=Faltan datos obligatorios');
            exit;
        }

        // Subir imagen del perro
        $fotoNombre = basename($files['foto']['name']);
        $rutaDestino = __DIR__ . '/../public/img/' . $fotoNombre;
        move_uploaded_file($files['foto']['tmp_name'], $rutaDestino);

        // Guardar usuario
        $usuarioModel = new Usuario();
        $usuarioId = $usuarioModel->crear([
            'nombre' => $data['nombre_dueño'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'telefono' => $data['telefono']
        ]);

        // Guardar perro
        $perroModel = new Perro();
        $perroModel->crear([
            'nombre' => $data['nombre_perro'],
            'raza' => $data['raza'],
            'edad' => $data['edad'],
            'sexo' => $data['sexo'], 
            'foto' => $fotoNombre,
            'usuario_id' => $usuarioId
        ]);

        header('Location: ../views/auth/login.php?mensaje=Cuenta creada correctamente');
        exit;
    }

    public function login($email, $password) {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->obtenerPorEmail($email);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            header('Location: ../views/auth/login.php?error=Credenciales incorrectas');
            exit;
        }

        // Autenticado
        session_start();
        $_SESSION['usuario'] = $usuario;

        header('Location: ../views/auth/dashboard.php');
        exit;
    }
}


