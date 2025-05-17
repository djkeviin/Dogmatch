<?php
require_once __DIR__ . '/../config/conexion.php';

class Usuario {
    private $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    public function crear($data) {
        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, email, password, telefono) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['nombre'],
            $data['email'],
            $data['password'],
            $data['telefono']
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
}
