<?php
require_once __DIR__ . '/../config/conexion.php';


class Perro {
    protected $db;

    public function __construct() {
      $this->db = Conexion::getConexion();
    }

    public function crear($data) {
        try {
            $this->db->beginTransaction();

            // Insertar el perro primero
            $sql = "INSERT INTO perros (nombre, edad, sexo, foto, usuario_id) 
                    VALUES (:nombre, :edad, :sexo, :foto, :usuario_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':edad' => $data['edad'],
                ':sexo' => $data['sexo'],
                ':foto' => $data['foto'],
                ':usuario_id' => $data['usuario_id']
            ]);

            $perro_id = $this->db->lastInsertId();

            // Insertar la relación con la raza
            if (!empty($data['raza'])) {
                $sql = "INSERT INTO raza_perro (perro_id, raza_id, es_principal) 
                        VALUES (:perro_id, :raza_id, true)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':perro_id' => $perro_id,
                    ':raza_id' => $data['raza']
                ]);
            }

            $this->db->commit();
            return $perro_id;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error al crear el perro: " . $e->getMessage());
        }
    }

    
 public function obtenerPerfilCompletoPorUsuarioId($usuario_id) {
    return $this->obtenerUnicoPorUsuarioId($usuario_id);
}


public static function buscarPerrosCompatibles($mis_perros) {
    $conexion = Conexion::getConexion();
    $matches = [];

    foreach ($mis_perros as $mi_perro) {
        $stmt = $conexion->prepare("
            SELECT p.*, u.nombre as nombre_dueno, u.telefono
            FROM perros p
            JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.raza = ? 
            AND p.sexo != ? 
            AND p.usuario_id != ?
            AND p.disponible_apareamiento = 1
        ");
        $stmt->execute([$mi_perro['raza'], $mi_perro['sexo'], $mi_perro['usuario_id']]);

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($resultados) {
            $matches = array_merge($matches, $resultados);
        }
    }

    return $matches;
}

public function obtenerUnicoPorUsuarioId($usuario_id) {
    try {
        // Obtener información básica del perro
        $sql = "SELECT p.*, u.nombre as nombre_dueno, u.telefono
                FROM perros p
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.usuario_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id]);
        $perro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$perro) {
            return null;
        }

        // Obtener las razas del perro
        $sql = "SELECT r.*, rp.porcentaje, rp.es_principal, rp.raza_id
                FROM raza_perro rp
                JOIN razas_perros r ON rp.raza_id = r.id
                WHERE rp.perro_id = :perro_id
                ORDER BY rp.es_principal DESC, rp.porcentaje DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':perro_id' => $perro['id']]);
        $perro['razas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $perro;
    } catch (PDOException $e) {
        throw new Exception("Error al obtener el perfil del perro: " . $e->getMessage());
    }
}

public function obtenerMultimediaPorPerroId($perro_id) {
    $stmt = $this->db->prepare("
        SELECT *
        FROM multimedia
        WHERE perro_id = ?
        ORDER BY fecha_subida DESC
    ");
    $stmt->execute([$perro_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function actualizar($data) {
    try {
        $this->db->beginTransaction();

        // Validar datos requeridos
        $campos_requeridos = ['nombre', 'edad', 'sexo', 'usuario_id'];
        foreach ($campos_requeridos as $campo) {
            if (!isset($data[$campo]) || $data[$campo] === '') {
                throw new Exception("El campo " . ucfirst($campo) . " es requerido");
            }
        }

        // Validar edad en meses
        if (!is_numeric($data['edad']) || $data['edad'] < 0 || $data['edad'] > 300) {
            throw new Exception("La edad debe ser un número válido entre 0 y 300 meses");
        }

        // Validar peso si está presente
        if (isset($data['peso']) && !empty($data['peso'])) {
            if (!is_numeric($data['peso']) || $data['peso'] < 0) {
                throw new Exception("El peso debe ser un número válido");
            }
        }

        // Primero obtener el ID del perro
        $stmt = $this->db->prepare("SELECT id FROM perros WHERE usuario_id = ?");
        $stmt->execute([$data['usuario_id']]);
        $perro_id = $stmt->fetchColumn();

        if (!$perro_id) {
            throw new Exception("No se encontró el perro para actualizar");
        }

        $sql = "UPDATE perros SET 
                nombre = :nombre,
                edad = :edad,
                sexo = :sexo,
                peso = :peso,
                descripcion = :descripcion,
                temperamento = :temperamento,
                sociable_perros = :sociable_perros,
                sociable_personas = :sociable_personas,
                estado_salud = :estado_salud,
                vacunas = :vacunas,
                esterilizado = :esterilizado,
                disponible_apareamiento = :disponible_apareamiento,
                condiciones_apareamiento = :condiciones_apareamiento,
                pedigri = :pedigri
                WHERE id = :perro_id";
                
        $stmt = $this->db->prepare($sql);
        
        // Preparar los datos para la actualización
        $params = [
            ':nombre' => $data['nombre'],
            ':edad' => $data['edad'],
            ':sexo' => $data['sexo'],
            ':peso' => $data['peso'] ?? null,
            ':descripcion' => $data['descripcion'] ?? null,
            ':temperamento' => $data['temperamento'] ?? null,
            ':sociable_perros' => isset($data['sociable_perros']) ? 1 : 0,
            ':sociable_personas' => isset($data['sociable_personas']) ? 1 : 0,
            ':estado_salud' => $data['estado_salud'] ?? null,
            ':vacunas' => $data['vacunas'] ?? null,
            ':esterilizado' => isset($data['esterilizado']) ? 1 : 0,
            ':disponible_apareamiento' => isset($data['disponible_apareamiento']) ? 1 : 0,
            ':condiciones_apareamiento' => $data['condiciones_apareamiento'] ?? null,
            ':pedigri' => isset($data['pedigri']) ? 1 : 0,
            ':perro_id' => $perro_id
        ];

        if (!$stmt->execute($params)) {
            throw new Exception("Error al actualizar el perfil del perro");
        }

        // Manejar la actualización de razas si se proporcionó una nueva
        if (!empty($data['raza'])) {
            // Eliminar todas las razas existentes
            $stmt = $this->db->prepare("DELETE FROM raza_perro WHERE perro_id = ?");
            $stmt->execute([$perro_id]);

            // Agregar la nueva raza
            $stmt = $this->db->prepare("INSERT INTO raza_perro (perro_id, raza_id, es_principal, porcentaje) VALUES (?, ?, true, 100.00)");
            $stmt->execute([$perro_id, $data['raza']]);
        }

        $this->db->commit();
        return $perro_id;
    } catch (Exception $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        throw new Exception("Error al actualizar el perro: " . $e->getMessage());
    }
}

public function buscarPerrosConFiltros($usuarioId, $filtros) {
    // Obtener el perro del usuario actual para comparar compatibilidad
    $miPerro = $this->obtenerPerfilCompletoPorUsuarioId($usuarioId);
    if (!$miPerro) {
        throw new Exception('No tienes un perro registrado');
    }

    // Construir la consulta base
    $sql = "SELECT p.*, u.nombre as nombre_dueno, u.telefono,
                   (SELECT COUNT(*) FROM matches m WHERE m.perro_id_origen = :usuario_perro_id AND m.perro_id_destino = p.id) as ya_match
            FROM perros p
            JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.usuario_id != :usuario_id
            AND p.disponible_apareamiento = 1";
    
    $params = [
        ':usuario_id' => $usuarioId,
        ':usuario_perro_id' => $miPerro['id']
    ];

    // Agregar filtros
    if (!empty($filtros['raza'])) {
        $sql .= " AND p.raza = :raza";
        $params[':raza'] = $filtros['raza'];
    }

    if (!empty($filtros['edad_min'])) {
        $sql .= " AND p.edad >= :edad_min";
        $params[':edad_min'] = $filtros['edad_min'];
    }

    if (!empty($filtros['edad_max'])) {
        $sql .= " AND p.edad <= :edad_max";
        $params[':edad_max'] = $filtros['edad_max'];
    }

    // Filtrar por sexo opuesto
    $sql .= " AND p.sexo != :sexo_mi_perro";
    $params[':sexo_mi_perro'] = $miPerro['sexo'];

    // Ordenar por distancia y excluir matches existentes
    $sql .= " AND ya_match = 0 ORDER BY RAND() LIMIT 50";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $perros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar resultados
    foreach ($perros as &$perro) {
        // Calcular distancia (simulada por ahora)
        $perro['distancia'] = rand(1, $filtros['distancia']); // Simulación
        
        // Agregar información adicional relevante
        $perro['esterilizado'] = (bool)($perro['esterilizado'] ?? false);
        $perro['vacunas'] = !empty($perro['vacunas']);
        
        // Limpiar datos sensibles
        unset($perro['usuario_id']);
        unset($perro['ya_match']);
    }

    return $perros;
}

/**
 * Busca perros aplicando los filtros especificados
 * @param array $filters Array asociativo con los filtros a aplicar
 * @return array Array de perros que cumplen con los filtros
 */
public function buscarPerros($filters = []) {
    try {
        // Construir la consulta base
        $sql = "SELECT p.*, u.nombre as nombre_dueno, u.telefono,
                      r.nombre as raza_nombre, r.tamanio, r.grupo_raza
               FROM perros p
               JOIN usuarios u ON p.usuario_id = u.id
               LEFT JOIN raza_perro rp ON p.id = rp.perro_id
               LEFT JOIN razas_perros r ON rp.raza_id = r.id
               WHERE rp.es_principal = 1";
        
        $params = [];

        // Aplicar filtros
        if (!empty($filters['raza'])) {
            $sql .= " AND r.nombre LIKE :raza";
            $params[':raza'] = '%' . $filters['raza'] . '%';
        }

        if (!empty($filters['edad'])) {
            $sql .= " AND p.edad <= :edad";
            $params[':edad'] = $filters['edad'];
        }

        if (!empty($filters['tamanio'])) {
            $sql .= " AND r.tamanio = :tamanio";
            $params[':tamanio'] = $filters['tamanio'];
        }

        if (!empty($filters['grupo'])) {
            $sql .= " AND r.grupo_raza = :grupo";
            $params[':grupo'] = $filters['grupo'];
        }

        if (isset($filters['esterilizado'])) {
            $sql .= " AND p.esterilizado = :esterilizado";
            $params[':esterilizado'] = $filters['esterilizado'] ? 1 : 0;
        }

        if (isset($filters['pedigri'])) {
            $sql .= " AND p.pedigri = :pedigri";
            $params[':pedigri'] = $filters['pedigri'] ? 1 : 0;
        }

        if (isset($filters['sociable_perros'])) {
            $sql .= " AND p.sociable_perros = :sociable_perros";
            $params[':sociable_perros'] = $filters['sociable_perros'] ? 1 : 0;
        }

        // Limitar resultados
        $sql .= " ORDER BY RAND() LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $perros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Procesar los resultados
        foreach ($perros as &$perro) {
            $perro['distancia'] = rand(1, isset($filters['distancia']) ? intval($filters['distancia']) : 10);
            $perro['esterilizado'] = (bool)$perro['esterilizado'];
            $perro['pedigri'] = (bool)$perro['pedigri'];
            $perro['sociable_perros'] = (bool)$perro['sociable_perros'];
            $perro['raza'] = $perro['raza_nombre']; // Para mantener compatibilidad
        }

        return $perros;

    } catch (PDOException $e) {
        throw new Exception("Error al buscar perros: " . $e->getMessage());
    }
}

/**
 * Busca perros cercanos a una ubicación dada
 */
public function buscarPerrosCercanos($lat, $lng, $rango) {
    $sql = "
        SELECT nombre, raza, latitud, longitud, foto,
            (6371 * ACOS(
                COS(RADIANS(:lat)) * COS(RADIANS(latitud)) *
                COS(RADIANS(longitud) - RADIANS(:lng)) +
                SIN(RADIANS(:lat)) * SIN(RADIANS(latitud))
            )) AS distancia
        FROM perros
        WHERE visible_en_mapa = 1
        AND latitud IS NOT NULL AND longitud IS NOT NULL
    ";

    if (isset($_SESSION['usuario']['id'])) {
        $sql .= " AND usuario_id != :usuario_id";
    }

    $sql .= " HAVING distancia <= :rango ORDER BY distancia ASC";

    try {
        $stmt = $this->db->prepare($sql);
        
        $params = [
            ':lat' => $lat,
            ':lng' => $lng,
            ':rango' => $rango
        ];

        if (isset($_SESSION['usuario']['id'])) {
            $params[':usuario_id'] = $_SESSION['usuario']['id'];
        }

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en buscarPerrosCercanos: " . $e->getMessage());
        throw new Exception("Error al buscar perros cercanos");
    }
}

public function asignarRaza($perro_id, $raza_id, $porcentaje = 100.00, $es_principal = true) {
    try {
        // Primero intentamos actualizar si existe
        $sql = "REPLACE INTO raza_perro (perro_id, raza_id, porcentaje, es_principal) 
                VALUES (:perro_id, :raza_id, :porcentaje, :es_principal)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':perro_id' => $perro_id,
            ':raza_id' => $raza_id,
            ':porcentaje' => $porcentaje,
            ':es_principal' => $es_principal ? 1 : 0
        ]);
    } catch (PDOException $e) {
        throw new Exception("Error al asignar raza: " . $e->getMessage());
    }
}

public function obtenerRazasPerro($perro_id) {
    try {
        $sql = "SELECT r.*, rp.porcentaje, rp.es_principal
                FROM raza_perro rp
                JOIN razas_perros r ON rp.raza_id = r.id
                WHERE rp.perro_id = :perro_id
                ORDER BY rp.es_principal DESC, rp.porcentaje DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':perro_id' => $perro_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new Exception("Error al obtener razas del perro: " . $e->getMessage());
    }
}

public function beginTransaction() {
    $this->db->beginTransaction();
}

public function commit() {
    $this->db->commit();
}

public function rollBack() {
    $this->db->rollBack();
}

public function desactivarRazasPrincipales($perro_id) {
    $sql = "UPDATE raza_perro SET es_principal = false WHERE perro_id = ?";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$perro_id]);
}

}