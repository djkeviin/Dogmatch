<?php
require_once __DIR__ . '/../config/conexion.php';

class Raza {
    private $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    /**
     * Busca razas que coincidan con el término de búsqueda
     * @param string $query Término de búsqueda
     * @return array Array de razas que coinciden con la búsqueda
     */
    public function buscarRazas($query) {
        $sql = "SELECT id, nombre, tamanio, grupo_raza, descripcion, caracteristicas 
                FROM razas_perros 
                WHERE LOWER(nombre) LIKE LOWER(?) 
                ORDER BY nombre ASC 
                LIMIT 10";
        
        try {
            $stmt = $this->db->prepare($sql);
            $searchTerm = '%' . $query . '%';
            error_log("SQL Query: " . $sql . " with term: " . $searchTerm);
            $stmt->execute([$searchTerm]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("SQL Result: " . json_encode($result));
            return $result;
        } catch (PDOException $e) {
            error_log("Error en buscarRazas: " . $e->getMessage());
            throw new Exception("Error al buscar razas");
        }
    }

    /**
     * Obtiene una raza por su ID
     * @param int $id ID de la raza
     * @return array|false Datos de la raza o false si no existe
     */
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM razas_perros WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            throw new Exception("Error al obtener la raza");
        }
    }

    /**
     * Obtiene todas las razas
     * @return array Array con todas las razas
     */
    public function obtenerTodas() {
        $sql = "SELECT * FROM razas_perros ORDER BY nombre ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerTodas: " . $e->getMessage());
            throw new Exception("Error al obtener las razas");
        }
    }

    public function obtenerTodasLasRazas() {
        try {
            $query = "SELECT id, nombre FROM razas_perros ORDER BY nombre ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener razas: " . $e->getMessage());
            return [];
        }
    }
} 