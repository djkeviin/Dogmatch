<?php
require_once __DIR__ . '/../config/conexion.php';

$db = Conexion::getConexion();
$sql = "SELECT p.id, p.nombre, p.latitud, p.longitud, u.nombre AS usuario FROM perros p JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.id";
$stmt = $db->query($sql);
$perros = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($perros as $perro) {
    echo "ID: {$perro['id']} | Nombre: {$perro['nombre']} | Usuario: {$perro['usuario']} | Lat: {$perro['latitud']} | Lng: {$perro['longitud']}\n";
} 