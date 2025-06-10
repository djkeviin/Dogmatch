<?php
require_once __DIR__ . '/../config/conexion.php';

class Mensaje {
    protected $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    /**
     * Obtener todas las conversaciones de un usuario
     */
    public function obtenerConversaciones($usuario_id) {
        $sql = "SELECT 
                    p.id as perro_id,
                    p.nombre,
                    p.foto,
                    (SELECT mensaje 
                     FROM mensajes m2 
                     WHERE (m2.emisor_id = :usuario_id AND m2.perro_id = p.id)
                        OR (m2.emisor_id = p.usuario_id AND m2.perro_id = p.id)
                     ORDER BY m2.fecha_envio DESC 
                     LIMIT 1) as ultimo_mensaje,
                    (SELECT fecha_envio 
                     FROM mensajes m2 
                     WHERE (m2.emisor_id = :usuario_id AND m2.perro_id = p.id)
                        OR (m2.emisor_id = p.usuario_id AND m2.perro_id = p.id)
                     ORDER BY m2.fecha_envio DESC 
                     LIMIT 1) as ultima_fecha,
                    COUNT(CASE WHEN m.leido = 0 AND m.emisor_id != :usuario_id THEN 1 END) as mensajes_no_leidos
                FROM perros p
                LEFT JOIN mensajes m ON m.perro_id = p.id
                WHERE EXISTS (
                    SELECT 1 
                    FROM mensajes m3 
                    WHERE (m3.emisor_id = :usuario_id AND m3.perro_id = p.id)
                       OR (m3.emisor_id = p.usuario_id AND m3.perro_id = p.id)
                )
                GROUP BY p.id
                ORDER BY ultima_fecha DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener mensajes de una conversación
     */
    public function obtenerMensajes($perro_id, $usuario_id, $ultimo_id = 0) {
        $sql = "SELECT 
                    m.*,
                    u.nombre as emisor_nombre
                FROM mensajes m
                JOIN usuarios u ON m.emisor_id = u.id
                WHERE m.perro_id = :perro_id 
                AND m.id > :ultimo_id
                ORDER BY m.fecha_envio ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':perro_id' => $perro_id,
            ':ultimo_id' => $ultimo_id
        ]);
        
        // Marcar mensajes como leídos
        $this->marcarMensajesComoLeidos($perro_id, $usuario_id);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo mensaje
     */
    public function crear($data) {
        $sql = "INSERT INTO mensajes (emisor_id, perro_id, mensaje, fecha_envio) 
                VALUES (:emisor_id, :perro_id, :mensaje, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':emisor_id' => $data['emisor_id'],
            ':perro_id' => $data['perro_id'],
            ':mensaje' => $data['mensaje']
        ]);

        return $this->obtenerPorId($this->db->lastInsertId());
    }

    /**
     * Obtener un mensaje por su ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT 
                    m.*,
                    u.nombre as emisor_nombre
                FROM mensajes m
                JOIN usuarios u ON m.emisor_id = u.id
                WHERE m.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Marcar mensajes como leídos
     */
    private function marcarMensajesComoLeidos($perro_id, $usuario_id) {
        $sql = "UPDATE mensajes 
                SET leido = 1 
                WHERE perro_id = :perro_id 
                AND emisor_id != :usuario_id 
                AND leido = 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':perro_id' => $perro_id,
            ':usuario_id' => $usuario_id
        ]);
    }

    /**
     * Verificar si un usuario tiene permiso para ver/enviar mensajes a un perro
     */
    public function verificarPermiso($perro_id, $usuario_id) {
        // Obtener el dueño del perro
        $sql = "SELECT usuario_id, disponible_apareamiento FROM perros WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perro_id]);
        $perro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$perro) {
            return false;
        }

        // El usuario puede enviar mensajes si:
        // 1. Es el dueño del perro
        // 2. Ya existe una conversación previa
        // 3. El perro está disponible para apareamiento (permitir nuevas conversaciones)
        return $perro['usuario_id'] == $usuario_id || 
               $this->existeConversacion($perro_id, $usuario_id) ||
               $perro['disponible_apareamiento'] == 1;
    }

    /**
     * Verificar si existe una conversación previa
     */
    private function existeConversacion($perro_id, $usuario_id) {
        $sql = "SELECT 1 
                FROM mensajes 
                WHERE perro_id = ? 
                AND (emisor_id = ? OR emisor_id = (SELECT usuario_id FROM perros WHERE id = ?))
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perro_id, $usuario_id, $perro_id]);
        return (bool) $stmt->fetch();
    }
} 