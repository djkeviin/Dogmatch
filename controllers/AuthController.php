<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perro.php';
require_once __DIR__ . '/../models/RazaPerro.php';

class AuthController {

    public function registrar($data, $files) {
        // Validación básica
        if (empty($data['nombre_dueño']) || empty($data['email']) || empty($data['password']) ||
            empty($data['nombre_perro']) || empty($data['raza']) || empty($data['telefono']) ||
            empty($data['edad']) || empty($data['sexo']) || empty($files['foto'])) {
            
            header('Location: ../views/auth/registro.php?error=Faltan datos obligatorios');
            exit;
        }

        // Validar si el email ya existe
        $usuarioModel = new Usuario();
        if ($usuarioModel->obtenerPorEmail($data['email'])) {
            header('Location: ../views/auth/registro.php?error=El correo ya está registrado');
            exit;
        }

        // Subir imagen del perro
        $fotoNombre = time() . '_' . basename($files['foto']['name']);
        $rutaDestino = __DIR__ . '/../public/img/' . $fotoNombre;

        if (!move_uploaded_file($files['foto']['tmp_name'], $rutaDestino)) {
            header('Location: ../views/auth/registro.php?error=Error al subir la imagen');
            exit;
        }

        try {
            // Guardar usuario
            $usuarioId = $usuarioModel->crear([
                'nombre' => $data['nombre_dueño'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'telefono' => $data['telefono'],
                'latitud' => null,
                'longitud' => null
            ]);

            // Guardar perro
            $perroModel = new Perro();
            $perroId = $perroModel->crear([
                'nombre' => $data['nombre_perro'],
                'edad' => $data['edad'],
                'sexo' => $data['sexo'],
                'foto' => $fotoNombre,
                'usuario_id' => $usuarioId,
                'tamanio' => $data['tamanio'] ?? 'mediano',
                'vacunado' => isset($data['vacunado']),
                'sociable_perros' => isset($data['sociable_perros']),
                'sociable_personas' => isset($data['sociable_personas']),
                'descripcion' => $data['descripcion'] ?? null
            ]);

            // Guardar relación con la raza
            if (!empty($data['raza'])) {
                $razaPerroModel = new RazaPerro();
                $razaPerroModel->crear([
                    'perro_id' => $perroId,
                    'raza_id' => $data['raza'],
                    'es_principal' => true,
                    'porcentaje' => 100
                ]);
            }

            session_start();
            $_SESSION['mensaje'] = "Tu cuenta fue creada correctamente. Ahora puedes iniciar sesión.";
            header('Location: ../views/auth/registro.php');
            exit;

        } catch (Exception $e) {
            // Si algo falla, eliminar la imagen subida
            if (file_exists($rutaDestino)) {
                unlink($rutaDestino);
            }
            session_start();
            $_SESSION['mensaje_error'] = $e->getMessage();
            header('Location: ../views/auth/registro.php');
            exit;
        }
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
        
        // Resetear intentos fallidos al lograr iniciar sesión
        if (isset($_SESSION['intentos_fallidos'])) {
            unset($_SESSION['intentos_fallidos']);
        }

        header('Location: ../views/auth/dashboard.php');
        exit;
    }

    /**
     * Cierra la sesión del usuario
     */
    public function logout() {
        session_start();
        session_destroy();
        header('Location: ../views/auth/login.php');
        exit;
    }
}

