<?php

class Matches {
    private $db;

    public function __construct() {
        global $conn;
        $this->db = $conn;
    }

    /**
     * Crea un nuevo match entre dos perros
     */
    public function crear($data) {
        $sql = "INSERT INTO matches (perro_origen_id, perro_destino_id, tipo, fecha_creacion) 
                VALUES (:perro_origen_id, :perro_destino_id, :tipo, NOW())";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':perro_origen_id' => $data['perro_origen_id'],
                ':perro_destino_id' => $data['perro_destino_id'],
                ':tipo' => $data['tipo']
            ]);
            return ['success' => true, 'match_id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Error en crear match: " . $e->getMessage());
            throw new Exception("Error al crear el match");
        }
    }

    /**
     * Obtiene los matches de un perro
     */
    public function obtenerPorPerroId($perro_id) {
        $sql = "SELECT m.*, 
                       p1.nombre as perro_origen_nombre, 
                       p2.nombre as perro_destino_nombre,
                       p1.foto as perro_origen_foto,
                       p2.foto as perro_destino_foto
                FROM matches m
                JOIN perros p1 ON m.perro_origen_id = p1.id
                JOIN perros p2 ON m.perro_destino_id = p2.id
                WHERE (m.perro_origen_id = :perro_id OR m.perro_destino_id = :perro_id)
                AND m.tipo = 'match'
                ORDER BY m.fecha_creacion DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':perro_id' => $perro_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorPerroId: " . $e->getMessage());
            throw new Exception("Error al obtener los matches");
        }
    }

    /**
     * Verifica si existe un match entre dos perros
     */
    public function existeMatch($perro1_id, $perro2_id) {
        $sql = "SELECT COUNT(*) as total
                FROM matches 
                WHERE ((perro_origen_id = :perro1_id AND perro_destino_id = :perro2_id)
                OR (perro_origen_id = :perro2_id AND perro_destino_id = :perro1_id))
                AND tipo = 'match'";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':perro1_id' => $perro1_id,
                ':perro2_id' => $perro2_id
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en existeMatch: " . $e->getMessage());
            throw new Exception("Error al verificar el match");
        }
    }
} 