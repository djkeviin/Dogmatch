<?php
require_once __DIR__ . '/../config/conexion.php';


class Perro {
    private $db;

    public function __construct() {
      $this->db = Conexion::getConexion();
    }

    public function crear($data) {
        $sql = "INSERT INTO perros (nombre, raza, edad, sexo, foto, usuario_id) 
                VALUES (:nombre, :raza, :edad, :sexo, :foto, :usuario_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':raza' => $data['raza'],
            ':edad' => $data['edad'],
            ':sexo' => $data['sexo'],
            ':foto' => $data['foto'],
            ':usuario_id' => $data['usuario_id']
        ]);
    }

    // 🚀 Nuevo método para listar perros por usuario
    public function obtenerPorUsuarioId($usuarioId) {
        $sql = "SELECT * FROM perros WHERE usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
