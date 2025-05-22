<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
session_start();

require_once __DIR__ . '/../config/conexion.php';
$db = Conexion::getConexion();

$lat = $_GET['lat'] ?? null;
$lng = $_GET['lng'] ?? null;
$rango = $_GET['rango'] ?? 5;

if ($lat === null || $lng === null) {
    echo json_encode([]);
    exit;
}

$usuarioId = $_SESSION['usuario']['id'] ?? null;

$sql = "
    SELECT nombre, raza, latitud, longitud, foto,
        (6371 * ACOS(
            COS(RADIANS(:lat)) * COS(RADIANS(latitud)) *
            COS(RADIANS(longitud) - RADIANS(:lng)) +
            SIN(RADIANS(:lat)) * SIN(RADIANS(latitud))
        )) AS distancia
    FROM perros
    WHERE visible_en_mapa = 1
    AND latitud IS NOT NULL AND longitud IS NOT NULL
";

if ($usuarioId !== null) {
    $sql .= " AND usuario_id != :usuario_id";
}

$sql .= " HAVING distancia <= :rango ORDER BY distancia ASC";

$stmt = $db->prepare($sql);

$params = [
    ':lat' => $lat,
    ':lng' => $lng,
    ':rango' => $rango
];

if ($usuarioId !== null) {
    $params[':usuario_id'] = $usuarioId;
}

$stmt->execute($params);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
