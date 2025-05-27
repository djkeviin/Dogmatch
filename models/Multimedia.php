<?php
require_once __DIR__ . '/../config/conexion.php';

class Multimedia {
    protected $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    public function crear($data) {
        $sql = "INSERT INTO multimedia (perro_id, tipo, url_archivo, descripcion, fecha_subida) 
                VALUES (:perro_id, :tipo, :url_archivo, :descripcion, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':perro_id' => $data['perro_id'],
            ':tipo' => $data['tipo'],
            ':url_archivo' => $data['url_archivo'],
            ':descripcion' => $data['descripcion']
        ]);
    }

    public function obtenerPorPerroId($perro_id) {
        $sql = "SELECT * FROM multimedia WHERE perro_id = ? ORDER BY fecha_subida DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perro_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM multimedia WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
} 