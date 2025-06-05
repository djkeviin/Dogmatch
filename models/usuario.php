<?php
require_once __DIR__ . '/../config/conexion.php';

class Usuario {
    private $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    public function crear($data) {
        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, email, password, telefono, latitud, longitud) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nombre'],
            $data['email'],
            $data['password'],
            $data['telefono'],
            $data['latitud'] ?? null,
            $data['longitud'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function obtenerPorEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id, $data) {
        $campos = [];
        $valores = [];
        
        if (isset($data['nombre'])) {
            $campos[] = "nombre = ?";
            $valores[] = $data['nombre'];
        }
        if (isset($data['email'])) {
            $campos[] = "email = ?";
            $valores[] = $data['email'];
        }
        if (isset($data['telefono'])) {
            $campos[] = "telefono = ?";
            $valores[] = $data['telefono'];
        }
        if (isset($data['latitud'])) {
            $campos[] = "latitud = ?";
            $valores[] = $data['latitud'];
        }
        if (isset($data['longitud'])) {
            $campos[] = "longitud = ?";
            $valores[] = $data['longitud'];
        }
        
        if (empty($campos)) {
            return false;
        }
        
        $valores[] = $id; // Para la cláusula WHERE
        
        $sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($valores);
    }
}
