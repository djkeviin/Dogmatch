<?php
require_once __DIR__ . '/../config/conexion.php';

class MatchPerro {
    private $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    // Enviar solicitud de match
    public function solicitarMatch($perro_id, $interesado_id) {
        // No permitir auto-match
        if ($perro_id == $interesado_id) {
            throw new Exception('No puedes hacer match contigo mismo');
        }
        // Verificar si ya existe una solicitud
        $sql = "SELECT * FROM solicitudes_match WHERE perro_id = ? AND interesado_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perro_id, $interesado_id]);
        if ($stmt->rowCount() > 0) {
            throw new Exception('Ya has enviado una solicitud a este perfil');
        }
        // Insertar solicitud
        $sql = "INSERT INTO solicitudes_match (perro_id, interesado_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perro_id, $interesado_id]);
        // Notificación: nueva solicitud de match
        require_once __DIR__ . '/Notificacion.php';
        require_once __DIR__ . '/perro.php';
        $perroModel = new Perro();
        $perro = $perroModel->obtenerPorId($perro_id);
        $interesado = $perroModel->obtenerPorId($interesado_id);
        if ($perro && $interesado) {
            $noti = new Notificacion();
            $mensaje = "¡Has recibido una nueva solicitud de match de '{$interesado['nombre']}'!";
            $url = '../auth/perfil.php?id=' . $interesado_id;
            $noti->crear($perro['usuario_id'], 'match_solicitud', $mensaje, $url);
        }
        return true;
    }

    // Aceptar o rechazar una solicitud (SISTEMA SIMPLIFICADO)
    public function responderMatch($perro_id, $interesado_id, $aceptar = true) {
        $sql = "SELECT * FROM solicitudes_match WHERE perro_id = ? AND interesado_id = ? AND estado = 'pendiente'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perro_id, $interesado_id]);
        if ($stmt->rowCount() == 0) {
            throw new Exception('No existe una solicitud pendiente');
        }
        
        $nuevo_estado = $aceptar ? 'aceptado' : 'rechazado';
        $sql = "UPDATE solicitudes_match SET estado = ?, fecha_respuesta = CURRENT_TIMESTAMP WHERE perro_id = ? AND interesado_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nuevo_estado, $perro_id, $interesado_id]);
        
        // Si se acepta, crear match inmediatamente
        if ($aceptar) {
            $this->crearMatch($perro_id, $interesado_id);
            // Notificación: match confirmado para ambos
            require_once __DIR__ . '/Notificacion.php';
            require_once __DIR__ . '/perro.php';
            $perroModel = new Perro();
            $perroA = $perroModel->obtenerPorId($perro_id);
            $perroB = $perroModel->obtenerPorId($interesado_id);
            if ($perroA && $perroB) {
                $noti = new Notificacion();
                $msgA = "¡Tienes un nuevo match con '{$perroB['nombre']}'!";
                $msgB = "¡Tienes un nuevo match con '{$perroA['nombre']}'!";
                $url = '../auth/dashboard.php';
                $noti->crear($perroA['usuario_id'], 'match_confirmado', $msgA, $url);
                $noti->crear($perroB['usuario_id'], 'match_confirmado', $msgB, $url);
            }
        }
        return true;
    }

    // Crear match confirmado
    private function crearMatch($perro1_id, $perro2_id) {
        // Ordenar IDs para evitar duplicados
        $ids = [$perro1_id, $perro2_id];
        sort($ids);
        $sql = "INSERT IGNORE INTO matches (perro1_id, perro2_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ids[0], $ids[1]]);
    }

    // Obtener matches confirmados de un perro
    public function obtenerMatches($perro_id) {
        $sql = "SELECT * FROM matches WHERE perro1_id = ? OR perro2_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perro_id, $perro_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Verificar si dos perros tienen match confirmado
    public function esMatch($perro1_id, $perro2_id) {
        $ids = [$perro1_id, $perro2_id];
        sort($ids);
        $sql = "SELECT * FROM matches WHERE perro1_id = ? AND perro2_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ids[0], $ids[1]]);
        return $stmt->rowCount() > 0;
    }
} 