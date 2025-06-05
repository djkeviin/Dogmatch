<?php
require_once __DIR__ . '/../config/conexion.php';

try {
    $db = Conexion::getConexion();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Insertando razas de perros originales...\n";

    // Ejecutar el script SQL
    $sqlFile = file_get_contents(__DIR__ . '/../sql/insertar_razas_originales.sql');
    $db->exec($sqlFile);

    echo "✓ Se han insertado las 15 razas de perros correctamente\n";
    
    // Verificar la inserción
    $stmt = $db->query("SELECT COUNT(*) as total FROM razas_perros");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de razas en la base de datos: " . $result['total'] . "\n";

} catch (Exception $e) {
    echo "Error durante la inserción: " . $e->getMessage() . "\n";
    exit(1);
} 