<?php
require_once '../config/database.php';
header('Content-Type: application/json');

try {
    $sql = "SELECT id, nombre FROM razas ORDER BY nombre ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $razas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'razas' => $razas
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 