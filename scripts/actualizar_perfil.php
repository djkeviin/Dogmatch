<?php
require_once __DIR__ . '/../config/conexion.php';

try {
    $db = Conexion::getConexion();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Actualizando estructura de las tablas para el perfil...\n\n";

    // Ejecutar script de actualización
    $sqlFile = file_get_contents(__DIR__ . '/../sql/actualizar_perfil.sql');
    $statements = array_filter(array_map('trim', explode(';', $sqlFile)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                echo "✓ Ejecutado: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                echo "Error ejecutando: " . substr($statement, 0, 50) . "...\n";
                echo "Mensaje: " . $e->getMessage() . "\n\n";
            }
        }
    }
    
    echo "\n¡Actualización completada!\n";
    echo "Las tablas han sido actualizadas con los campos necesarios para el perfil.\n";

} catch (Exception $e) {
    echo "Error durante la actualización: " . $e->getMessage() . "\n";
    exit(1);
} 