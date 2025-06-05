<?php
require_once __DIR__ . '/../config/conexion.php';

try {
    $db = Conexion::getConexion();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Iniciando configuración de datos de prueba...\n\n";

    // 1. Crear estructura de la base de datos
    echo "Creando estructura de la base de datos...\n";
    $sqlEstructura = file_get_contents(__DIR__ . '/../sql/estructura.sql');
    $statements = array_filter(array_map('trim', explode(';', $sqlEstructura)));
    
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
    echo "✓ Estructura creada correctamente\n\n";

    // 2. Insertar razas de perros
    echo "Insertando razas de perros...\n";
    $sqlRazas = file_get_contents(__DIR__ . '/../sql/insertar_razas.sql');
    $db->exec($sqlRazas);
    echo "✓ Razas insertadas correctamente\n\n";

    // 3. Ejecutar script SQL de datos de prueba
    echo "Ejecutando script SQL de datos de prueba...\n";
    $sqlFile = file_get_contents(__DIR__ . '/../sql/insertar_datos_prueba.sql');
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
    echo "✓ Datos SQL insertados correctamente\n\n";

    // 4. Descargar y configurar imágenes
    echo "Configurando imágenes de prueba...\n";
    require_once __DIR__ . '/crear_imagenes_prueba.php';
    echo "✓ Imágenes configuradas correctamente\n\n";

    echo "¡Configuración completada con éxito!\n";
    echo "\nPuedes iniciar sesión con cualquiera de estas cuentas:\n";
    echo "- Email: ana@test.com, Contraseña: password\n";
    echo "- Email: carlos@test.com, Contraseña: password\n";
    echo "- Email: maria@test.com, Contraseña: password\n";
    echo "- Email: juan@test.com, Contraseña: password\n";
    echo "- Email: laura@test.com, Contraseña: password\n";

} catch (Exception $e) {
    echo "Error durante la configuración: " . $e->getMessage() . "\n";
    exit(1);
} 