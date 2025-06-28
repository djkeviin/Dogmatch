<?php
session_start();
require_once '../../models/Reporte.php';
require_once '../../models/Usuario.php';
require_once '../../models/Perro.php';

header('Content-Type: application/json');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

try {
    // Validar datos de entrada
    $reportado_id = $_POST['reportado_id'] ?? null;
    $perro_id = $_POST['perro_id'] ?? null;
    $tipo_reporte = $_POST['tipo_reporte'] ?? null;
    $descripcion = $_POST['descripcion'] ?? '';

    if (!$reportado_id || !$tipo_reporte) {
        throw new Exception('Datos incompletos');
    }

    // Validar tipo de reporte
    $tipos_validos = ['perfil_falso', 'contenido_inapropiado', 'spam', 'acoso', 'otro'];
    if (!in_array($tipo_reporte, $tipos_validos)) {
        throw new Exception('Tipo de reporte inválido');
    }

    // Verificar que no se reporte a sí mismo
    if ($_SESSION['usuario_id'] == $reportado_id) {
        throw new Exception('No puedes reportarte a ti mismo');
    }

    // Verificar que el usuario reportado existe
    $usuarioModel = new Usuario();
    $usuario_reportado = $usuarioModel->obtenerPorId($reportado_id);
    if (!$usuario_reportado) {
        throw new Exception('Usuario no encontrado');
    }

    // Verificar que el perro existe si se proporciona
    if ($perro_id) {
        $perroModel = new Perro();
        $perro = $perroModel->obtenerPorId($perro_id);
        if (!$perro) {
            throw new Exception('Perro no encontrado');
        }
    }

    // Crear el reporte
    $reporteModel = new Reporte();
    
    // Verificar si ya reportó recientemente
    if (!$reporteModel->puedeReportar($_SESSION['usuario_id'], $reportado_id)) {
        throw new Exception('Ya has reportado a este usuario en las últimas 24 horas');
    }

    $reporte_id = $reporteModel->crear(
        $_SESSION['usuario_id'],
        $reportado_id,
        $perro_id,
        $tipo_reporte,
        $descripcion
    );

    echo json_encode([
        'success' => true,
        'message' => 'Reporte creado exitosamente',
        'reporte_id' => $reporte_id
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 