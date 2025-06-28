<?php
session_start();
header('Content-Type: application/json');

// 1. Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// 2. Validar entrada
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    echo json_encode(['success' => false, 'error' => 'ID de perro no válido o no proporcionado.']);
    exit;
}

// 3. Obtener y procesar datos
require_once '../../models/Perro.php';
require_once '../../models/Raza.php';

$perroModel = new Perro();
$razaModel = new Raza();
$perro_id = intval($_GET['id']);

try {
    // Obtener info básica del perro
    $perro = $perroModel->obtenerPorId($perro_id);

    if ($perro) {
        // Obtener la raza principal
        $razas = $perroModel->obtenerRazasPerro($perro_id);
        $raza_principal = 'Mestizo'; // Valor por defecto
        if (!empty($razas)) {
            foreach ($razas as $raza) {
                if ($raza['es_principal']) {
                    $raza_info = $razaModel->obtenerPorId($raza['id']);
                    $raza_principal = $raza_info ? $raza_info['nombre'] : 'Mestizo';
                    break;
                }
            }
        }
        $perro['raza'] = $raza_principal;

        // Formatear edad
        $edad_meses = $perro['edad'];
        if ($edad_meses < 12) {
            $perro['edad'] = "$edad_meses meses";
        } else {
            $anios = floor($edad_meses / 12);
            $meses_resto = $edad_meses % 12;
            $perro['edad'] = "$anios " . ($anios > 1 ? "años" : "año") . ($meses_resto > 0 ? " y $meses_resto mes(es)" : "");
        }
        
        echo json_encode(['success' => true, 'perro' => $perro]);

    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Perro no encontrado.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error en obtener_info_perro.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error en el servidor.']);
} 