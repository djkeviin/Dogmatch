<?php
require_once __DIR__ . '/../config/conexion.php';

class Usuario {
    private $db;
    const TIEMPO_ONLINE = 300; // 5 minutos en segundos

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

    /**
     * Actualizar la última actividad del usuario
     */
    public function actualizarActividad($usuario_id) {
        $sql = "UPDATE usuarios SET ultima_actividad = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$usuario_id]);
    }

    /**
     * Verificar si un usuario está en línea
     */
    public function estaEnLinea($usuario_id) {
        $sql = "SELECT ultima_actividad FROM usuarios WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resultado || !$resultado['ultima_actividad']) {
            return false;
        }

        $ultima_actividad = strtotime($resultado['ultima_actividad']);
        $ahora = time();
        
        return ($ahora - $ultima_actividad) <= self::TIEMPO_ONLINE;
    }

    /**
     * Obtener el estado en línea de varios usuarios
     */
    public function obtenerEstadosEnLinea($usuario_ids) {
        if (empty($usuario_ids)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($usuario_ids) - 1) . '?';
        $sql = "SELECT id, ultima_actividad 
                FROM usuarios 
                WHERE id IN ($placeholders)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($usuario_ids);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $estados = [];
        $ahora = time();
        foreach ($resultados as $resultado) {
            $ultima_actividad = strtotime($resultado['ultima_actividad']);
            $estados[$resultado['id']] = ($ahora - $ultima_actividad) <= self::TIEMPO_ONLINE;
        }

        return $estados;
    }
}
