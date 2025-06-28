<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../../config/conexion.php';
require_once '../../models/Mensaje.php';
require_once '../../models/Multimedia.php';
require_once '../../models/Perro.php';

// 1. Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// 2. Validar datos de entrada
$perro_destinatario_id = filter_input(INPUT_POST, 'perro_destinatario_id', FILTER_VALIDATE_INT);
$id_temporal = filter_input(INPUT_POST, 'id_temporal', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$perro_destinatario_id || !isset($_FILES['imagen'])) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
    exit;
}

// 3. Validar el archivo subido
$file = $_FILES['imagen'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Error al subir el archivo. Código: ' . $file['error']]);
    exit;
}

$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'error' => 'Tipo de archivo no permitido.']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) { // Límite de 5 MB
    echo json_encode(['success' => false, 'error' => 'El archivo es demasiado grande (máx 5MB).']);
    exit;
}

// 4. Procesar y guardar el archivo
$upload_dir = '../../public/img/chat/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_filename = uniqid('chatimg_', true) . '.' . $file_extension;
$upload_path = $upload_dir . $new_filename;

if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    echo json_encode(['success' => false, 'error' => 'No se pudo mover el archivo subido.']);
    exit;
}

// 5. Insertar en la base de datos
try {
    $conexion = Conexion::getConexion();
    $multimediaModel = new Multimedia();
    $mensajeModel = new Mensaje();
    $perroModel = new Perro();

    $emisor_id = $_SESSION['usuario_id'];
    $mi_perro = $perroModel->obtenerUnicoPorUsuarioId($emisor_id);
     if (!$mi_perro) {
        echo json_encode(['success' => false, 'error' => 'No tienes un perro para chatear.']);
        exit;
    }

    // Iniciar transacción
    $conexion->beginTransaction();

    // 5.1. Guardar en tabla multimedia
    $datos_multimedia = [
        'perro_id'    => $mi_perro['id'], // Asociamos la imagen con el perro que la envía
        'tipo'        => 'imagen_chat',
        'url_archivo' => $new_filename,
        'descripcion' => 'Imagen enviada en el chat',
        'tamano'      => $file['size'],
        'mime_type'   => $file['type']
    ];
    $multimedia_id = $multimediaModel->crear($datos_multimedia);

    if (!$multimedia_id) {
        throw new Exception('No se pudo crear el registro multimedia.');
    }

    // 5.2. Crear el mensaje
    $mensaje_texto = 'Imagen'; // Texto placeholder para la notificación/lista de chats
    $mensaje_id = $mensajeModel->enviarMensajeConMultimedia($mi_perro['id'], $perro_destinatario_id, $mensaje_texto, $multimedia_id, $id_temporal);
    if (!$mensaje_id) {
        throw new Exception('No se pudo guardar el mensaje.');
    }

    // Confirmar transacción
    $conexion->commit();

    // 6. Devolver el mensaje completo para la actualización de la UI
    $nuevo_mensaje = $mensajeModel->obtenerPorId($mensaje_id);
    if ($nuevo_mensaje) {
        // Asegurarse de que el objeto devuelto tenga la misma estructura que los demás
        $nuevo_mensaje['es_emisor'] = true; 
        echo json_encode(['success' => true, 'mensaje' => $nuevo_mensaje]);
    } else {
         throw new Exception('No se pudo recuperar el mensaje recién creado.');
    }

} catch (Exception $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    // Si algo falla, eliminar el archivo físico para no dejar basura
    if (file_exists($upload_path)) {
        unlink($upload_path);
    }
    error_log('Error en enviar_imagen.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()]);
}
?> 