<?php
class Valoracion {
    private $conn;

    public function __construct() {
        require_once __DIR__ . '/../config/conexion.php';
        $this->conn = Conexion::getConexion();
    }

    public function crear($data) {
        $sql = "INSERT INTO valoraciones (perro_id, usuario_id, puntuacion, comentario) 
                VALUES (:perro_id, :usuario_id, :puntuacion, :comentario)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':perro_id' => $data['perro_id'],
            ':usuario_id' => $data['usuario_id'],
            ':puntuacion' => $data['puntuacion'],
            ':comentario' => $data['comentario']
        ]);
    }

    public function obtenerPorPerroId($perro_id) {
        $sql = "SELECT v.*, u.nombre as nombre_usuario 
                FROM valoraciones v
                JOIN usuarios u ON v.usuario_id = u.id
                WHERE v.perro_id = :perro_id
                ORDER BY v.fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':perro_id' => $perro_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener el promedio de valoraciones de un perro
     */
    public function obtenerPromedio($perro_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                COALESCE(AVG(puntuacion), 0) as promedio,
                COUNT(*) as total
            FROM valoraciones 
            WHERE perro_id = ?
        ");
        $stmt->execute([$perro_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todas las valoraciones de un perro
     */
    public function obtenerValoracionesPerro($perro_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                v.*,
                u.nombre as nombre_usuario
            FROM valoraciones v
            JOIN usuarios u ON v.usuario_id = u.id
            WHERE v.perro_id = ?
            ORDER BY v.fecha_creacion DESC
            LIMIT 10
        ");
        $stmt->execute([$perro_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener la valoración específica de un usuario para un perro
     */
    public function obtenerValoracionUsuario($perro_id, $usuario_id) {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM valoraciones
            WHERE perro_id = ? AND usuario_id = ?
        ");
        $stmt->execute([$perro_id, $usuario_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Agregar o actualizar una valoración
     */
    public function valorar($perro_id, $usuario_id, $puntuacion) {
        // Verificar si ya existe una valoración
        $valoracion_existente = $this->obtenerValoracionUsuario($perro_id, $usuario_id);

        if ($valoracion_existente) {
            // Actualizar valoración existente
            $stmt = $this->conn->prepare("
                UPDATE valoraciones 
                SET puntuacion = ?, 
                    fecha_actualizacion = NOW() 
                WHERE usuario_id = ? AND perro_id = ?
            ");
            return $stmt->execute([$puntuacion, $usuario_id, $perro_id]);
        } else {
            // Insertar nueva valoración
            $stmt = $this->conn->prepare("
                INSERT INTO valoraciones (usuario_id, perro_id, puntuacion, fecha_creacion) 
                VALUES (?, ?, ?, NOW())
            ");
            return $stmt->execute([$usuario_id, $perro_id, $puntuacion]);
        }
    }

    public function guardarValoracion($perro_id, $usuario_id, $puntuacion, $comentario = null) {
        // ... código existente ...
        // Guardar o actualizar la valoración
        // ...
        // Notificación al dueño del perro
        require_once __DIR__ . '/perro.php';
        require_once __DIR__ . '/Notificacion.php';
        $perroModel = new Perro();
        $perro = $perroModel->obtenerPorId($perro_id);
        if ($perro && $perro['usuario_id'] != $usuario_id) {
            $noti = new Notificacion();
            $mensaje = "¡Tu perro '{$perro['nombre']}' ha recibido una nueva valoración!";
            $url = '../auth/perfil.php?id=' . $perro_id;
            $noti->crear($perro['usuario_id'], 'valoracion', $mensaje, $url);
        }
        // ...
    }
} 