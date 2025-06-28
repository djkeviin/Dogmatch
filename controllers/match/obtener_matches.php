<?php
session_start();
require_once '../../models/MatchPerro.php';
require_once '../../models/perro.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

try {
    $perro_id = $_GET['perro_id'] ?? null;
    if (!$perro_id) {
        throw new Exception('ID de perro no proporcionado');
    }

    // Verificar que el perro pertenece al usuario logueado
    $perroModel = new Perro();
    $perro = $perroModel->obtenerPorId($perro_id);
    if (!$perro || $perro['usuario_id'] != $_SESSION['usuario_id']) {
        throw new Exception('No tienes permiso para ver los matches de este perro');
    }

    $match = new MatchPerro();
    $matches = $match->obtenerMatches($perro_id);

    // Agregar info del otro perro (nombre y foto) y filtrar matches propios
    $matches_filtrados = [];
    foreach ($matches as $m) {
        $otro_id = $m['perro1_id'] == $perro_id ? $m['perro2_id'] : $m['perro1_id'];
        $otro = $perroModel->obtenerPorId($otro_id);
        // Filtrar si el otro perro es del mismo usuario
        if ($otro && $otro['usuario_id'] != $_SESSION['usuario_id']) {
            $m['nombre_perro'] = $otro['nombre'] ?? 'Desconocido';
            $m['foto_perro'] = $otro['foto'] ?? 'default-dog.png';
            $matches_filtrados[] = $m;
        }
    }

    echo json_encode(['success' => true, 'matches' => $matches_filtrados]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 