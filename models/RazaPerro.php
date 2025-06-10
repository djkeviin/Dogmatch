<?php
require_once __DIR__ . '/../config/conexion.php';

class RazaPerro {
    private $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    public function crear($data) {
        $stmt = $this->db->prepare("INSERT INTO raza_perro (perro_id, raza_id, es_principal, porcentaje) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['perro_id'],
            $data['raza_id'],
            $data['es_principal'],
            $data['porcentaje']
        ]);
        return $this->db->lastInsertId();
    }

    public function obtenerPorPerroId($perroId) {
        $stmt = $this->db->prepare("
            SELECT rp.*, r.nombre as nombre_raza, r.grupo_raza, r.tamanio, r.caracteristicas
            FROM raza_perro rp
            JOIN razas_perros r ON r.id = rp.raza_id
            WHERE rp.perro_id = ?
            ORDER BY rp.es_principal DESC, rp.porcentaje DESC
        ");
        $stmt->execute([$perroId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminarPorPerroId($perroId) {
        $stmt = $this->db->prepare("DELETE FROM raza_perro WHERE perro_id = ?");
        return $stmt->execute([$perroId]);
    }

    public function actualizarRazaPrincipal($perroId, $razaId) {
        try {
            $this->db->beginTransaction();

            // Primero, establecer todas las razas como no principales
            $stmt = $this->db->prepare("
                UPDATE raza_perro 
                SET es_principal = FALSE 
                WHERE perro_id = ?
            ");
            $stmt->execute([$perroId]);

            // Verificar si ya existe una relación con esta raza
            $stmt = $this->db->prepare("
                SELECT id FROM raza_perro 
                WHERE perro_id = ? AND raza_id = ?
            ");
            $stmt->execute([$perroId, $razaId]);
            $existente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existente) {
                // Actualizar la raza existente como principal
                $stmt = $this->db->prepare("
                    UPDATE raza_perro 
                    SET es_principal = TRUE, porcentaje = 100 
                    WHERE id = ?
                ");
                $stmt->execute([$existente['id']]);
            } else {
                // Crear nueva relación como principal
                $stmt = $this->db->prepare("
                    INSERT INTO raza_perro (perro_id, raza_id, es_principal, porcentaje) 
                    VALUES (?, ?, TRUE, 100)
                ");
                $stmt->execute([$perroId, $razaId]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error al actualizar la raza principal: " . $e->getMessage());
        }
    }

    public function buscarRazas($query) {
        try {
            $query = trim($query);
            
            // Si la consulta está vacía, devolver las primeras 10 razas
            if (empty($query)) {
                $sql = "SELECT * FROM razas_perros 
                        ORDER BY nombre ASC 
                        LIMIT 10";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Búsqueda con coincidencia parcial
            $sql = "SELECT * FROM razas_perros 
                    WHERE nombre LIKE :query 
                    OR grupo_raza LIKE :query
                    ORDER BY 
                        CASE 
                            WHEN nombre LIKE :exact THEN 1
                            WHEN nombre LIKE :start THEN 2
                            ELSE 3
                        END,
                        nombre ASC
                    LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':query' => '%' . $query . '%',
                ':exact' => $query,
                ':start' => $query . '%'
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            throw new Exception("Error al buscar razas: " . $e->getMessage());
        }
    }

    public function obtenerTodas() {
        try {
            $stmt = $this->db->query("
                SELECT r.*, 
                       COUNT(rp.id) as total_perros,
                       GROUP_CONCAT(DISTINCT r.grupo_raza) as grupos
                FROM razas_perros r
                LEFT JOIN raza_perro rp ON r.id = rp.raza_id
                GROUP BY r.id
                ORDER BY r.nombre ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error al obtener las razas: " . $e->getMessage());
        }
    }

    public function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM razas_perros WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener raza: " . $e->getMessage());
        }
    }

    public function buscarPorNombre($nombre) {
        $sql = "SELECT * FROM razas_perros WHERE nombre LIKE :nombre ORDER BY nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nombre' => "%$nombre%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorTamanio($tamanio) {
        $sql = "SELECT * FROM razas_perros WHERE tamanio = :tamanio ORDER BY nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tamanio' => $tamanio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorGrupo($grupo) {
        $sql = "SELECT * FROM razas_perros WHERE grupo_raza = :grupo ORDER BY nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':grupo' => $grupo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarConFiltros($filtros = []) {
        $sql = "SELECT * FROM razas_perros WHERE 1=1";
        $params = [];

        if (!empty($filtros['nombre'])) {
            $sql .= " AND nombre LIKE :nombre";
            $params[':nombre'] = "%" . $filtros['nombre'] . "%";
        }

        if (!empty($filtros['tamanio'])) {
            $sql .= " AND tamanio = :tamanio";
            $params[':tamanio'] = $filtros['tamanio'];
        }

        if (!empty($filtros['grupo'])) {
            $sql .= " AND grupo_raza = :grupo";
            $params[':grupo'] = $filtros['grupo'];
        }

        $sql .= " ORDER BY nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerGruposUnicos() {
        $sql = "SELECT DISTINCT grupo_raza FROM razas_perros ORDER BY grupo_raza";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
} 