<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$lat = $data['lat'];
$lng = $data['lng'];
$usuario_id = $_SESSION['usuario']['id'];

$db = Conexion::getConexion();
$stmt = $db->prepare("UPDATE perros SET latitud = ?, longitud = ? WHERE usuario_id = ?");
$stmt->execute([$lat, $lng, $usuario_id]);
?>
