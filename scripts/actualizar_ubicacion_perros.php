<?php
require_once __DIR__ . '/../config/conexion.php';

$db = Conexion::getConexion();

// Obtener todos los perros sin ubicación
$sql = "SELECT p.id, p.usuario_id, u.latitud, u.longitud FROM perros p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE (p.latitud IS NULL OR p.longitud IS NULL)
        AND u.latitud IS NOT NULL AND u.longitud IS NOT NULL";
$stmt = $db->query($sql);
$perros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$actualizados = 0;
foreach ($perros as $perro) {
    $update = $db->prepare("UPDATE perros SET latitud = ?, longitud = ? WHERE id = ?");
    $update->execute([$perro['latitud'], $perro['longitud'], $perro['id']]);
    $actualizados++;
}

echo "Perros actualizados: $actualizados\n"; 