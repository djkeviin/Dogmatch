<?php
require_once __DIR__ . '/../config/conexion.php';

class MatchPerro {
    protected $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    public function crear($data) {
        $sql = "INSERT INTO matches (perro_id_origen, perro_id_destino, fecha_match) 
                VALUES (:perro_id_origen, :perro_id_destino, :fecha_match)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':perro_id_origen' => $data['perro_id_origen'],
            ':perro_id_destino' => $data['perro_id_destino'],
            ':fecha_match' => $data['fecha_match']
        ]);
    }

    public function verificarMatchMutuo($perroId1, $perroId2) {
        $sql = "SELECT COUNT(*) as total FROM matches 
                WHERE (perro_id_origen = :perro1 AND perro_id_destino = :perro2)
                AND EXISTS (
                    SELECT 1 FROM matches 
                    WHERE perro_id_origen = :perro2 
                    AND perro_id_destino = :perro1
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':perro1' => $perroId1,
            ':perro2' => $perroId2
        ]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    public function obtenerMatchesMutuos($perroId) {
        $sql = "SELECT p.*, u.nombre as nombre_dueno, u.telefono, m.fecha_match
                FROM matches m
                JOIN perros p ON p.id = m.perro_id_destino
                JOIN usuarios u ON u.id = p.usuario_id
                WHERE m.perro_id_origen = :perro_id
                AND EXISTS (
                    SELECT 1 FROM matches m2 
                    WHERE m2.perro_id_origen = m.perro_id_destino 
                    AND m2.perro_id_destino = m.perro_id_origen
                )
                ORDER BY m.fecha_match DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':perro_id' => $perroId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 