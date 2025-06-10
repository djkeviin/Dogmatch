<?php
require_once __DIR__ . '/config/conexion.php';

try {
    $db = Conexion::getConexion();
    echo "Conexión exitosa a la base de datos\n";
    
    $stmt = $db->query('SHOW TABLES');
    echo "\nTablas en la base de datos:\n";
    while($row = $stmt->fetch()) {
        echo "- " . $row[0] . "\n";
    }
} catch (Exception $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
} 