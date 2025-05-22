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


public static function buscarPerrosCompatibles($mis_perros) {
    $conexion = Conexion::getConexion();
    $matches = [];

    foreach ($mis_perros as $mi_perro) {
        $stmt = $conexion->prepare("SELECT * FROM perros WHERE raza = ? AND sexo != ? AND usuario_id != ?");
        $stmt->execute([$mi_perro['raza'], $mi_perro['sexo'], $mi_perro['usuario_id']]);

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($resultados) {
            $matches = array_merge($matches, $resultados);
        }
    }

    return $matches;
}


}