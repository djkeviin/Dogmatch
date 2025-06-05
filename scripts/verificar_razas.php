<?php
require_once __DIR__ . '/config/conexion.php';

try {
    $db = Conexion::getConexion();
    
    // Verificar si la tabla existe
    $stmt = $db->query("SHOW TABLES LIKE 'razas_perros'");
    if ($stmt->rowCount() == 0) {
        die("La tabla razas_perros no existe\n");
    }
    
    // Verificar la estructura de la tabla
    $stmt = $db->query("DESCRIBE razas_perros");
    echo "Estructura de la tabla razas_perros:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} - {$row['Type']}\n";
    }
    
    // Verificar si hay datos
    $stmt = $db->query("SELECT COUNT(*) as total FROM razas_perros");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "\nTotal de razas en la base de datos: $count\n";
    
    if ($count > 0) {
        // Mostrar algunas razas como ejemplo
        $stmt = $db->query("SELECT * FROM razas_perros LIMIT 5");
        echo "\nEjemplos de razas:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$row['nombre']} ({$row['tamanio']})\n";
        }
    }
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
} 