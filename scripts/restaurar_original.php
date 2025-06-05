<?php
require_once __DIR__ . '/../config/conexion.php';

try {
    $db = Conexion::getConexion();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Restaurando base de datos a su estado original...\n\n";

    // 1. Ejecutar script de restauración
    echo "Restaurando estructura original...\n";
    $sqlFile = file_get_contents(__DIR__ . '/../sql/restaurar_original.sql');
    $statements = array_filter(array_map('trim', explode(';', $sqlFile)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
            } catch (PDOException $e) {
                echo "Error ejecutando: " . substr($statement, 0, 50) . "...\n";
                echo "Mensaje: " . $e->getMessage() . "\n\n";
            }
        }
    }
    echo "✓ Estructura restaurada correctamente\n\n";

    // 2. Eliminar imágenes de prueba
    echo "Limpiando imágenes de prueba...\n";
    $imgDir = __DIR__ . '/../public/img';
    $imagenes = glob($imgDir . '/*.jpg');
    foreach ($imagenes as $imagen) {
        if (basename($imagen) !== 'default-dog.jpg') {
            unlink($imagen);
        }
    }
    echo "✓ Imágenes limpiadas correctamente\n\n";

    echo "¡Restauración completada!\n";
    echo "La base de datos ha sido restaurada a su estructura original.\n";
    echo "Ahora puedes volver a crear tus usuarios y perros como estaban antes.\n";

} catch (Exception $e) {
    echo "Error durante la restauración: " . $e->getMessage() . "\n";
    exit(1);
} 