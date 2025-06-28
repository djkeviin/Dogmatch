<?php
require_once 'config/conexion.php';
require_once 'models/MatchPerro.php';
require_once 'models/perro.php';

echo "<h2>Prueba del Sistema de Matches (Aceptación Única)</h2>";

try {
    $db = Conexion::getConexion();
    $matchModel = new MatchPerro();
    $perroModel = new Perro();
    
    // Obtener algunos perros para la prueba
    $sql = "SELECT id, nombre, usuario_id FROM perros LIMIT 2";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $perros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($perros) < 2) {
        echo "<p style='color: red;'>Se necesitan al menos 2 perros para hacer la prueba.</p>";
        exit;
    }
    
    $perro1 = $perros[0];
    $perro2 = $perros[1];
    
    echo "<h3>Perros para la prueba:</h3>";
    echo "<p>Perro 1: {$perro1['nombre']} (ID: {$perro1['id']})</p>";
    echo "<p>Perro 2: {$perro2['nombre']} (ID: {$perro2['id']})</p>";
    
    // Paso 1: Enviar solicitud de match
    echo "<h3>Paso 1: Enviando solicitud de match...</h3>";
    try {
        $matchModel->solicitarMatch($perro1['id'], $perro2['id']);
        echo "<p style='color: green;'>✓ Solicitud enviada correctamente</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error al enviar solicitud: " . $e->getMessage() . "</p>";
    }
    
    // Verificar que se creó la solicitud
    $sql = "SELECT * FROM solicitudes_match WHERE perro_id = ? AND interesado_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$perro1['id'], $perro2['id']]);
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($solicitud) {
        echo "<p>✓ Solicitud encontrada en la base de datos (ID: {$solicitud['id']}, Estado: {$solicitud['estado']})</p>";
    } else {
        echo "<p style='color: red;'>✗ No se encontró la solicitud en la base de datos</p>";
    }
    
    // Paso 2: Aceptar la solicitud (esto debería crear el match inmediatamente)
    echo "<h3>Paso 2: Aceptando la solicitud (debería crear match inmediatamente)...</h3>";
    try {
        $matchModel->responderMatch($perro2['id'], $perro1['id'], true);
        echo "<p style='color: green;'>✓ Solicitud aceptada correctamente</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error al aceptar solicitud: " . $e->getMessage() . "</p>";
    }
    
    // Verificar que se actualizó la solicitud
    $sql = "SELECT * FROM solicitudes_match WHERE perro_id = ? AND interesado_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$perro1['id'], $perro2['id']]);
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($solicitud && $solicitud['estado'] == 'aceptado') {
        echo "<p>✓ Solicitud actualizada a estado 'aceptado'</p>";
    } else {
        echo "<p style='color: red;'>✗ La solicitud no se actualizó correctamente</p>";
    }
    
    // Paso 3: Verificar si se creó el match (debería existir inmediatamente)
    echo "<h3>Paso 3: Verificando match confirmado (debería existir inmediatamente)...</h3>";
    $sql = "SELECT * FROM matches WHERE (perro1_id = ? AND perro2_id = ?) OR (perro1_id = ? AND perro2_id = ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$perro1['id'], $perro2['id'], $perro2['id'], $perro1['id']]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($match) {
        echo "<p style='color: green;'>✓ Match confirmado creado (ID: {$match['id']})</p>";
        echo "<p style='color: green;'>✓ Sistema de aceptación única funcionando correctamente</p>";
    } else {
        echo "<p style='color: red;'>✗ No se creó el match confirmado</p>";
    }
    
    // Mostrar estado final de las tablas
    echo "<h3>Estado final de las tablas:</h3>";
    
    $sql = "SELECT COUNT(*) as total FROM solicitudes_match";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $total_solicitudes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $sql = "SELECT COUNT(*) as total FROM matches";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $total_matches = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<p>Total solicitudes: {$total_solicitudes}</p>";
    echo "<p>Total matches: {$total_matches}</p>";
    
    // Mostrar las solicitudes y matches específicos
    echo "<h3>Detalles de las solicitudes:</h3>";
    $sql = "SELECT * FROM solicitudes_match ORDER BY id DESC LIMIT 5";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($solicitudes)) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Perro ID</th><th>Interesado ID</th><th>Estado</th><th>Fecha Solicitud</th><th>Fecha Respuesta</th></tr>";
        foreach ($solicitudes as $s) {
            echo "<tr>";
            echo "<td>{$s['id']}</td>";
            echo "<td>{$s['perro_id']}</td>";
            echo "<td>{$s['interesado_id']}</td>";
            echo "<td>{$s['estado']}</td>";
            echo "<td>{$s['fecha_solicitud']}</td>";
            echo "<td>" . ($s['fecha_respuesta'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>Detalles de los matches:</h3>";
    $sql = "SELECT * FROM matches ORDER BY id DESC LIMIT 5";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($matches)) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Perro1 ID</th><th>Perro2 ID</th><th>Fecha Match</th></tr>";
        foreach ($matches as $m) {
            echo "<tr>";
            echo "<td>{$m['id']}</td>";
            echo "<td>{$m['perro1_id']}</td>";
            echo "<td>{$m['perro2_id']}</td>";
            echo "<td>{$m['fecha_match']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error general: " . $e->getMessage() . "</p>";
}
?> 