<?php
require_once __DIR__ . '/config/conexion.php';

try {
    $db = Conexion::getConexion();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Iniciando actualización de la tabla perros...\n";
    
    // Verificar si existe la columna raza
    $stmt = $db->query("SHOW COLUMNS FROM perros LIKE 'raza'");
    $columnaRazaExiste = $stmt->rowCount() > 0;
    
    // Crear tabla raza_perro si no existe
    echo "Verificando tabla raza_perro...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS raza_perro (
        id INT AUTO_INCREMENT PRIMARY KEY,
        perro_id INT NOT NULL,
        raza_id INT NOT NULL,
        es_principal BOOLEAN DEFAULT true,
        porcentaje DECIMAL(5,2) DEFAULT 100.00,
        FOREIGN KEY (perro_id) REFERENCES perros(id) ON DELETE CASCADE,
        FOREIGN KEY (raza_id) REFERENCES razas_perros(id) ON DELETE RESTRICT,
        UNIQUE KEY unique_perro_raza (perro_id, raza_id)
    )");
    
    if ($columnaRazaExiste) {
        // Iniciar transacción para la migración de datos
        $db->beginTransaction();
        
        try {
            echo "Creando tabla temporal...\n";
            $db->exec("CREATE TEMPORARY TABLE IF NOT EXISTS temp_perros AS SELECT * FROM perros");
            
            // Obtener perros con sus razas antes de eliminar la columna
            $stmt = $db->query("SELECT id, nombre, raza FROM perros WHERE raza IS NOT NULL");
            $perros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Eliminando columna raza...\n";
            $db->exec("ALTER TABLE perros DROP COLUMN raza");
            
            echo "Migrando datos existentes...\n";
            foreach ($perros as $perro) {
                // Buscar la raza correspondiente
                $stmt = $db->prepare("SELECT id FROM razas_perros WHERE LOWER(nombre) LIKE ?");
                $stmt->execute(['%' . strtolower($perro['raza']) . '%']);
                $raza = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($raza) {
                    // Insertar en la tabla raza_perro
                    $stmt = $db->prepare("INSERT IGNORE INTO raza_perro (perro_id, raza_id, es_principal) VALUES (?, ?, true)");
                    $stmt->execute([$perro['id'], $raza['id']]);
                    echo "Migrado: {$perro['nombre']} - {$perro['raza']}\n";
                } else {
                    echo "ADVERTENCIA: No se encontró coincidencia para la raza '{$perro['raza']}' del perro '{$perro['nombre']}'\n";
                }
            }
            
            // Confirmar los cambios
            $db->commit();
            echo "Migración completada con éxito.\n";
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    } else {
        echo "La columna 'raza' no existe en la tabla perros. No es necesaria la migración.\n";
    }
    
    echo "Actualización completada con éxito.\n";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
} 