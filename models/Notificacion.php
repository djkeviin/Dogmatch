<?php
require_once __DIR__ . '/../config/conexion.php';

class Notificacion {
    private $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    /**
     * Crear una notificación
     * @param int $usuario_id
     * @param string $tipo
     * @param string $mensaje
     * @param string|null $url
     */
    public function crear($usuario_id, $tipo, $mensaje, $url = null) {
        $sql = "INSERT INTO notificaciones (usuario_id, tipo, mensaje, url) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id, $tipo, $mensaje, $url]);
        return $this->db->lastInsertId();
    }

    /**
     * Obtener notificaciones no leídas
     */
    public function obtenerNoLeidas($usuario_id, $limite = 10) {
        $limite = intval($limite);
        $sql = "SELECT * FROM notificaciones WHERE usuario_id = ? AND leida = 0 ORDER BY fecha_creacion DESC LIMIT $limite";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarLeida($notificacion_id) {
        $sql = "UPDATE notificaciones SET leida = 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$notificacion_id]);
    }

    /**
     * Marcar todas como leídas
     */
    public function marcarTodasLeidas($usuario_id) {
        $sql = "UPDATE notificaciones SET leida = 1 WHERE usuario_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$usuario_id]);
    }
} 