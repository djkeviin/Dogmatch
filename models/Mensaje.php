<?php
require_once __DIR__ . '/../config/conexion.php';

class Mensaje {
    protected $conexion;

    public function __construct() {
        $this->conexion = Conexion::getConexion();
    }

    /**
     * Obtener todas las conversaciones de un usuario
     */
    public function obtenerConversaciones($usuario_id) {
        $sql = "SELECT 
                    p.id as perro_id,
                    p.nombre,
                    p.foto,
                    p.usuario_id as otro_usuario_id,
                    m.mensaje as ultimo_mensaje,
                    m.fecha_envio as ultima_fecha,
                    m.multimedia_id,
                    (SELECT COUNT(*) 
                     FROM mensajes m2 
                     WHERE m2.perro_id = p.id AND m2.emisor_id = p.usuario_id AND m2.leido = 0
                    ) as no_leidos
                FROM (
                    SELECT 
                        perro_id, 
                        MAX(fecha_envio) as max_fecha
                    FROM mensajes
                    WHERE emisor_id = :usuario_id OR perro_id IN (SELECT id FROM perros WHERE usuario_id = :usuario_id)
                    GROUP BY perro_id
                ) as ultimos_mensajes
                JOIN mensajes m ON m.perro_id = ultimos_mensajes.perro_id AND m.fecha_envio = ultimos_mensajes.max_fecha
                JOIN perros p ON p.id = m.perro_id
                WHERE p.usuario_id != :usuario_id
                ORDER BY m.fecha_envio DESC";
        
        $stmt = $this->conexion->prepare($sql);
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
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':perro_id' => $perro_id,
            ':ultimo_id' => $ultimo_id
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo mensaje
     */
    public function crear($data) {
        $sql = "INSERT INTO mensajes (emisor_id, perro_id, mensaje, fecha_envio) 
                VALUES (:emisor_id, :perro_id, :mensaje, NOW())";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':emisor_id' => $data['emisor_id'],
            ':perro_id' => $data['perro_id'],
            ':mensaje' => $data['mensaje']
        ]);

        return $this->obtenerPorId($this->conexion->lastInsertId());
    }

    /**
     * Obtener un mensaje por su ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT m.*, mul.url_archivo AS multimedia_url
                FROM mensajes m
                LEFT JOIN multimedia mul ON m.multimedia_id = mul.id
                WHERE m.id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Marca los mensajes de una conversación como leídos para un usuario.
     */
    public function marcarComoLeido($perro_id, $usuario_id) {
        $this->marcarMensajesComoLeidos($perro_id, $usuario_id);
    }

    /**
     * Obtiene los mensajes nuevos de una conversación desde un timestamp.
     */
    public function obtenerMensajesDesde($perro_id, $usuario_id, $ultimo_timestamp) {
        $sql = "SELECT m.id, m.mensaje, m.fecha_envio, m.emisor_id, m.id_temporal,
                       mul.url_archivo AS multimedia_url
                FROM mensajes m
                LEFT JOIN multimedia mul ON m.multimedia_id = mul.id
                WHERE m.perro_id = :perro_id
                AND UNIX_TIMESTAMP(m.fecha_envio) > :ultimo_timestamp
                ORDER BY m.fecha_envio ASC";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':perro_id' => $perro_id,
            ':ultimo_timestamp' => $ultimo_timestamp
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las conversaciones que han tenido actividad reciente.
     */
    public function obtenerConversacionesActualizadas($usuario_id, $ultimo_timestamp) {
        // Esta consulta busca conversaciones donde:
        // 1. Hay un nuevo mensaje desde el último timestamp.
        // 2. El estado online del otro usuario ha cambiado recientemente (esto se maneja en el frontend pero la consulta lo facilita).
        $sql = "SELECT 
                    p.id AS perro_id,
                    p.nombre,
                    p.foto,
                    u_otro.ultima_actividad,
                    (SELECT msg.mensaje FROM mensajes msg WHERE msg.perro_id = p.id ORDER BY msg.fecha_envio DESC LIMIT 1) as ultimo_mensaje,
                    (SELECT COUNT(*) FROM mensajes msg WHERE msg.perro_id = p.id AND msg.leido = 0 AND msg.emisor_id != :usuario_id) as no_leidos
                FROM perros p
                JOIN usuarios u_otro ON p.usuario_id = u_otro.id
                WHERE EXISTS (
                    SELECT 1 FROM mensajes m
                    WHERE m.perro_id = p.id
                    AND ( (m.emisor_id = :usuario_id) OR (m.emisor_id = p.usuario_id) )
                ) 
                AND (
                    (SELECT UNIX_TIMESTAMP(MAX(m2.fecha_envio)) FROM mensajes m2 WHERE m2.perro_id = p.id) > :ultimo_timestamp
                    OR
                    UNIX_TIMESTAMP(u_otro.ultima_actividad) > :ultimo_timestamp
                )";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':ultimo_timestamp' => $ultimo_timestamp
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si un usuario tiene permiso para ver/enviar mensajes a un perro
     */
    public function verificarPermiso($perro_id, $usuario_id) {
        // Obtener el dueño del perro
        $sql = "SELECT usuario_id, disponible_apareamiento FROM perros WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
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
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$perro_id, $usuario_id, $perro_id]);
        return (bool) $stmt->fetch();
    }

    public function enviarMensaje($perro_destinatario_id, $emisor_id, $mensaje, $id_temporal = null) {
        $mi_perro = (new Perro())->obtenerUnicoPorUsuarioId($emisor_id);
        if (!$mi_perro) return false;
        
        $sql = "INSERT INTO mensajes (perro_id, emisor_id, mensaje, id_temporal, fecha_envio) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$perro_destinatario_id, $emisor_id, $mensaje, $id_temporal]);
        $nuevo_id = $this->conexion->lastInsertId();

        // Lógica de notificación...
        return $nuevo_id;
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
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':perro_id' => $perro_id,
            ':usuario_id' => $usuario_id
        ]);
    }

    /**
     * Obtener todos los mensajes entre dos perros (sin importar el emisor)
     */
    public function obtenerMensajesEntrePerros($perro1_id, $perro2_id, $ultimo_id = 0) {
        $sql = "SELECT m.*, u.nombre as emisor_nombre, 
                       mul.url_archivo as multimedia_url
                FROM mensajes m
                JOIN usuarios u ON m.emisor_id = u.id
                LEFT JOIN multimedia mul ON m.multimedia_id = mul.id
                WHERE (
                    (m.perro_id = :perro1_id AND m.emisor_id = (SELECT usuario_id FROM perros WHERE id = :perro2_id))
                    OR
                    (m.perro_id = :perro2_id AND m.emisor_id = (SELECT usuario_id FROM perros WHERE id = :perro1_id))
                )
                AND m.id > :ultimo_id
                ORDER BY m.fecha_envio ASC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':perro1_id' => $perro1_id,
            ':perro2_id' => $perro2_id,
            ':ultimo_id' => $ultimo_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function enviarMensajeConMultimedia($perro_emisor_id, $perro_destinatario_id, $mensaje, $multimedia_id, $id_temporal) {
        $emisor_id = $_SESSION['usuario_id'];

        $sql = "INSERT INTO mensajes (perro_id, emisor_id, mensaje, multimedia_id, id_temporal, fecha_envio) 
                VALUES (:perro_dest_id, :emisor_id, :mensaje, :multimedia_id, :id_temporal, NOW())";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':perro_dest_id', $perro_destinatario_id, PDO::PARAM_INT);
        $stmt->bindParam(':emisor_id', $emisor_id, PDO::PARAM_INT);
        $stmt->bindParam(':mensaje', $mensaje, PDO::PARAM_STR);
        $stmt->bindParam(':multimedia_id', $multimedia_id, PDO::PARAM_INT);
        $stmt->bindParam(':id_temporal', $id_temporal, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $nuevo_id = $this->conexion->lastInsertId();
            // Lógica de notificación...
            return $nuevo_id;
        }
        return false;
    }
} 