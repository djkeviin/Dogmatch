<?php
session_start();
require_once '../config/conexion.php';

function redirigir_con_error($mensaje) {
    $_SESSION['error'] = $mensaje;
    header("Location: ../views/auth/configuracion.php");
    exit();
}

if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    redirigir_con_error("Acceso no autorizado. Debes iniciar sesión.");
}

$id = $_SESSION['usuario']['id'];

$nuevoCorreo = trim($_POST['nuevo_correo']);
$contrasenaActual = trim($_POST['contrasena_actual']);
$nuevaContrasena = trim($_POST['nueva_contrasena']);
$confirmarContrasena = trim($_POST['confirmar_contrasena']);
$telefono = trim($_POST['numero_telefono']);

try {
    $conexion = new Conexion();
    $pdo = $conexion->getConexion();

    // Obtener datos actuales
    $stmt = $pdo->prepare("SELECT email, password FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        redirigir_con_error("Usuario no encontrado.");
    }

    $campos = [];
    $valores = [];
    $correoCambiado = false;

    // Validar y actualizar correo
    if (!empty($nuevoCorreo) && $nuevoCorreo !== $usuario['email']) {
        if (!filter_var($nuevoCorreo, FILTER_VALIDATE_EMAIL)) {
            redirigir_con_error("Formato de correo no válido.");
        }
        $campos[] = "email = :email";
        $valores['email'] = $nuevoCorreo;
        $correoCambiado = true;
    }

    // Validar y actualizar contraseña
    if (!empty($nuevaContrasena)) {
        if (empty($contrasenaActual) || !password_verify($contrasenaActual, $usuario['password'])) {
            redirigir_con_error("La contraseña actual es incorrecta.");
        }

        if ($nuevaContrasena !== $confirmarContrasena) {
            redirigir_con_error("Las nuevas contraseñas no coinciden.");
        }

        $hash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
        $campos[] = "password = :password";
        $valores['password'] = $hash;
    }

    // Validar y actualizar teléfono
    if (!empty($telefono)) {
        if (!preg_match('/^[0-9]{10}$/', $telefono)) { // Asumiendo un número de 10 dígitos
            redirigir_con_error("El formato del número de teléfono no es válido. Debe contener 10 dígitos.");
        }
        $campos[] = "telefono = :telefono";
        $valores['telefono'] = $telefono;
    }

    if (!empty($campos)) {
        $sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = :id";
        $valores['id'] = $id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($valores);

        if ($correoCambiado) {
            $asunto = "Tu correo ha sido actualizado en DogMatch 🐶";
            $mensaje = "Hola, tu dirección de correo ha sido cambiada exitosamente. Si no hiciste este cambio, contacta al soporte inmediatamente.";
            mail($nuevoCorreo, $asunto, $mensaje, "From: DogMatch <no-reply@dogmatch.com>");
        }

        $_SESSION['mensaje'] = "Tus credenciales han sido actualizadas correctamente.";
        header("Location: ../views/auth/configuracion.php");
        exit();
    } else {
        redirigir_con_error("No se realizaron cambios.");
    }

} catch (PDOException $e) {
    redirigir_con_error("Error en la base de datos: " . $e->getMessage());
}


