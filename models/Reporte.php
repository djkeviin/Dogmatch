<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/Notificacion.php';

class Reporte {
    private $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    /**
     * Crear un nuevo reporte
     */
    public function crear($reportador_id, $reportado_id, $perro_id, $tipo_reporte, $descripcion) {
        try {
            // Verificar que no se reporte a sí mismo
            if ($reportador_id == $reportado_id) {
                throw new Exception('No puedes reportarte a ti mismo');
            }

            // Verificar que no haya un reporte reciente del mismo usuario
            $sql = "SELECT id FROM reportes 
                    WHERE reportador_id = ? AND reportado_id = ? 
                    AND fecha_reporte > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reportador_id, $reportado_id]);
            
            if ($stmt->rowCount() > 0) {
                throw new Exception('Ya has reportado a este usuario en las últimas 24 horas');
            }

            // Insertar el reporte
            $sql = "INSERT INTO reportes (reportador_id, reportado_id, perro_id, tipo_reporte, descripcion) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reportador_id, $reportado_id, $perro_id, $tipo_reporte, $descripcion]);

            $reporte_id = $this->db->lastInsertId();

            // Verificar si se debe tomar acción automática
            $this->verificarAccionAutomatica($reportado_id, $tipo_reporte);

            // Notificación al usuario reportado
            $noti = new Notificacion();
            $mensaje = "Tu perfil o perro ha sido reportado por posible '{$tipo_reporte}'. Nuestro equipo revisará el caso.";
            $url = '../auth/perfil.php';
            $noti->crear($reportado_id, 'reporte', $mensaje, $url);
            // Notificación al admin (opcional, aquí asumo usuario_id=1 es admin)
            $noti->crear(1, 'reporte_admin', "Nuevo reporte recibido para revisión.", '../admin/reportes.php');

            return $reporte_id;
        } catch (Exception $e) {
            throw new Exception('Error al crear reporte: ' . $e->getMessage());
        }
    }

    /**
     * Obtener reportes por estado
     */
    public function obtenerPorEstado($estado = 'pendiente', $limite = 50) {
        try {
            $sql = "SELECT r.*, 
                           u1.nombre as reportador_nombre,
                           u2.nombre as reportado_nombre,
                           p.nombre as perro_nombre,
                           m.nombre as moderador_nombre
                    FROM reportes r
                    LEFT JOIN usuarios u1 ON r.reportador_id = u1.id
                    LEFT JOIN usuarios u2 ON r.reportado_id = u2.id
                    LEFT JOIN perros p ON r.perro_id = p.id
                    LEFT JOIN usuarios m ON r.moderador_id = m.id
                    WHERE r.estado = ?
                    ORDER BY r.fecha_reporte DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estado, $limite]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Error al obtener reportes: ' . $e->getMessage());
        }
    }

    /**
     * Obtener un reporte específico
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT r.*, 
                           u1.nombre as reportador_nombre,
                           u2.nombre as reportado_nombre,
                           p.nombre as perro_nombre,
                           m.nombre as moderador_nombre
                    FROM reportes r
                    LEFT JOIN usuarios u1 ON r.reportador_id = u1.id
                    LEFT JOIN usuarios u2 ON r.reportado_id = u2.id
                    LEFT JOIN perros p ON r.perro_id = p.id
                    LEFT JOIN usuarios m ON r.moderador_id = m.id
                    WHERE r.id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Error al obtener reporte: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar estado de un reporte
     */
    public function actualizarEstado($id, $estado, $moderador_id = null, $comentario = null) {
        try {
            $sql = "UPDATE reportes 
                    SET estado = ?, 
                        moderador_id = ?, 
                        comentario_moderador = ?,
                        fecha_resolucion = CASE WHEN ? IN ('resuelto', 'descartado') THEN NOW() ELSE fecha_resolucion END
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estado, $moderador_id, $comentario, $estado, $id]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new Exception('Error al actualizar reporte: ' . $e->getMessage());
        }
    }

    /**
     * Tomar acción contra un usuario reportado
     */
    public function tomarAccion($reporte_id, $usuario_id, $accion, $duracion = null, $motivo = '', $moderador_id = null) {
        try {
            $this->db->beginTransaction();

            // Actualizar el reporte
            $sql = "UPDATE reportes 
                    SET accion_tomada = ?, 
                        estado = 'resuelto',
                        moderador_id = ?,
                        fecha_resolucion = NOW()
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$accion, $moderador_id, $reporte_id]);

            // Registrar en historial
            $sql = "INSERT INTO historial_moderacion (reporte_id, usuario_id, accion, duracion, motivo, moderador_id) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reporte_id, $usuario_id, $accion, $duracion, $motivo, $moderador_id]);

            // Aplicar la acción al usuario
            $this->aplicarAccionUsuario($usuario_id, $accion, $duracion);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Error al tomar acción: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si se debe tomar acción automática
     */
    private function verificarAccionAutomatica($usuario_id, $tipo_reporte) {
        try {
            // Obtener configuración para este tipo de reporte
            $sql = "SELECT * FROM configuracion_moderacion WHERE tipo_reporte = ? AND activo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tipo_reporte]);
            $config = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$config || $config['accion_automatica'] == 'ninguna') {
                return;
            }

            // Contar reportes recientes del usuario
            $sql = "SELECT COUNT(*) as total FROM reportes 
                    WHERE reportado_id = ? AND tipo_reporte = ? 
                    AND fecha_reporte > DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$usuario_id, $tipo_reporte]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado['total'] >= $config['umbral_reportes']) {
                // Tomar acción automática
                $this->aplicarAccionUsuario($usuario_id, $config['accion_automatica'], $config['duracion_bloqueo']);
            }
        } catch (Exception $e) {
            error_log('Error en verificarAccionAutomatica: ' . $e->getMessage());
        }
    }

    /**
     * Aplicar acción a un usuario
     */
    private function aplicarAccionUsuario($usuario_id, $accion, $duracion = null) {
        try {
            switch ($accion) {
                case 'advertencia':
                    // Solo registrar la advertencia
                    break;
                    
                case 'bloqueo_temporal':
                    $fecha_fin = $duracion > 0 ? date('Y-m-d H:i:s', strtotime("+{$duracion} days")) : null;
                    $sql = "UPDATE usuarios SET 
                            bloqueado = 1, 
                            fecha_bloqueo = NOW(),
                            fecha_desbloqueo = ?
                            WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$fecha_fin, $usuario_id]);
                    break;
                    
                case 'bloqueo_permanente':
                    $sql = "UPDATE usuarios SET 
                            bloqueado = 1, 
                            fecha_bloqueo = NOW(),
                            fecha_desbloqueo = NULL
                            WHERE id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$usuario_id]);
                    break;
            }
        } catch (Exception $e) {
            throw new Exception('Error al aplicar acción: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de reportes
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        estado,
                        tipo_reporte,
                        COUNT(*) as total,
                        DATE(fecha_reporte) as fecha
                    FROM reportes 
                    WHERE fecha_reporte > DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY estado, tipo_reporte, DATE(fecha_reporte)
                    ORDER BY fecha DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Error al obtener estadísticas: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si un usuario puede reportar a otro
     */
    public function puedeReportar($reportador_id, $reportado_id) {
        try {
            $sql = "SELECT COUNT(*) as total FROM reportes 
                    WHERE reportador_id = ? AND reportado_id = ? 
                    AND fecha_reporte > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reportador_id, $reportado_id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['total'] == 0;
        } catch (Exception $e) {
            return false;
        }
    }
} 