<?php
require_once __DIR__ . '/../config/conexion.php';

try {
    $db = Conexion::getConexion();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Verificando datos insertados...\n\n";

    // 1. Verificar usuarios
    $usuarios = $db->query("SELECT COUNT(*) as total FROM usuarios")->fetch(PDO::FETCH_ASSOC);
    echo "Usuarios registrados: " . $usuarios['total'] . " (esperados: 5)\n";

    // 2. Verificar razas
    $razas = $db->query("SELECT COUNT(*) as total FROM razas_perros")->fetch(PDO::FETCH_ASSOC);
    echo "Razas registradas: " . $razas['total'] . " (esperadas: 10)\n";

    // 3. Verificar perros
    $perros = $db->query("SELECT COUNT(*) as total FROM perros")->fetch(PDO::FETCH_ASSOC);
    echo "Perros registrados: " . $perros['total'] . " (esperados: 10)\n";

    // 4. Verificar asignaciones de razas
    $razas_perros = $db->query("SELECT COUNT(*) as total FROM raza_perro")->fetch(PDO::FETCH_ASSOC);
    echo "Asignaciones de razas: " . $razas_perros['total'] . " (esperadas: 14)\n";

    // 5. Verificar imágenes
    $imgDir = __DIR__ . '/../public/img';
    $imagenes = glob($imgDir . '/*.jpg');
    echo "Imágenes encontradas: " . count($imagenes) . " (esperadas: 11)\n";

    echo "\nListado de perros con sus razas:\n";
    $stmt = $db->query("
        SELECT p.nombre, p.tamanio, GROUP_CONCAT(r.nombre) as razas
        FROM perros p
        LEFT JOIN raza_perro rp ON p.id = rp.perro_id
        LEFT JOIN razas_perros r ON rp.raza_id = r.id
        GROUP BY p.id
        ORDER BY p.nombre
    ");
    
    while ($perro = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$perro['nombre']} ({$perro['tamanio']}): {$perro['razas']}\n";
    }

} catch (Exception $e) {
    echo "Error durante la verificación: " . $e->getMessage() . "\n";
    exit(1);
} 