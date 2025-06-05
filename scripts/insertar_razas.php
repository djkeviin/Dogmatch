<?php
require_once __DIR__ . '/config/conexion.php';

try {
    $db = Conexion::getConexion();
    
    // Asegurarse de que la tabla existe
    $sql = "CREATE TABLE IF NOT EXISTS razas_perros (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        tamanio ENUM('Pequeño', 'Mediano', 'Grande') NOT NULL,
        grupo_raza VARCHAR(50) NOT NULL,
        caracteristicas TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $db->exec($sql);
    
    // Leer y ejecutar el archivo SQL
    $sql = file_get_contents(__DIR__ . '/sql/insertar_razas.sql');
    $db->exec($sql);
    
    echo "Razas insertadas correctamente\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
} 