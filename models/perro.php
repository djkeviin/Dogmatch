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

            // Forzar disponible_apareamiento a 1 por defecto si no está definido
            if (!isset($data['disponible_apareamiento'])) {
                $data['disponible_apareamiento'] = 1;
            }

            // Calcular edad en meses a partir de la fecha de nacimiento
            $edad = null;
            if (!empty($data['fecha_nacimiento'])) {
                $fecha_nacimiento = new DateTime($data['fecha_nacimiento']);
                $hoy = new DateTime();
                $intervalo = $hoy->diff($fecha_nacimiento);
                $edad = $intervalo->y * 12 + $intervalo->m;
            }

            // Insertar el perro primero
            $sql = "INSERT INTO perros (nombre, fecha_nacimiento, edad, sexo, foto, usuario_id, disponible_apareamiento) 
                    VALUES (:nombre, :fecha_nacimiento, :edad, :sexo, :foto, :usuario_id, :disponible_apareamiento)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':fecha_nacimiento' => $data['fecha_nacimiento'],
                ':edad' => $edad,
                ':sexo' => $data['sexo'],
                ':foto' => $data['foto'],
                ':usuario_id' => $data['usuario_id'],
                ':disponible_apareamiento' => $data['disponible_apareamiento']
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

public function actualizar($perro_id, $data) {
    try {
        $this->db->beginTransaction();

        // Calcular edad en meses a partir de la fecha de nacimiento
        $edad = null;
        if (!empty($data['fecha_nacimiento'])) {
            $fecha_nacimiento = new DateTime($data['fecha_nacimiento']);
            $hoy = new DateTime();
            $intervalo = $hoy->diff($fecha_nacimiento);
            $edad = $intervalo->y * 12 + $intervalo->m;
        }

        $sql = "UPDATE perros SET 
            nombre = :nombre,
            fecha_nacimiento = :fecha_nacimiento,
            edad = :edad,
            peso = :peso,
            sexo = :sexo,
            tamanio = :tamanio,
            descripcion = :descripcion,
            vacunado = :vacunado,
            sociable_perros = :sociable_perros,
            sociable_personas = :sociable_personas,
            pedigri = :pedigri,
            temperamento = :temperamento,
            estado_salud = :estado_salud,
            vacunas = :vacunas,
            disponible_apareamiento = :disponible_apareamiento,
            condiciones_apareamiento = :condiciones_apareamiento";

        // Agregar foto solo si se proporciona
        if (isset($data['foto'])) {
            $sql .= ", foto = :foto";
        }

        $sql .= " WHERE id = :perro_id";
                
        $stmt = $this->db->prepare($sql);
        
        // Preparar los datos para la actualización
        $params = [
            ':nombre' => $data['nombre'],
            ':fecha_nacimiento' => $data['fecha_nacimiento'],
            ':edad' => $edad,
            ':peso' => $data['peso'],
            ':sexo' => $data['sexo'],
            ':tamanio' => $data['tamanio'] ?? 'mediano',
            ':descripcion' => $data['descripcion'] ?? null,
            ':vacunado' => $data['vacunado'] ? 1 : 0,
            ':sociable_perros' => $data['sociable_perros'] ? 1 : 0,
            ':sociable_personas' => $data['sociable_personas'] ? 1 : 0,
            ':pedigri' => $data['pedigri'] ? 1 : 0,
            ':temperamento' => $data['temperamento'] ?? null,
            ':estado_salud' => $data['estado_salud'] ?? null,
            ':vacunas' => $data['vacunas'] ?? null,
            ':disponible_apareamiento' => $data['disponible_apareamiento'] ? 1 : 0,
            ':condiciones_apareamiento' => $data['condiciones_apareamiento'] ?? null,
            ':perro_id' => $perro_id
        ];

        // Agregar foto a los parámetros si existe
        if (isset($data['foto'])) {
            $params[':foto'] = $data['foto'];
        }

        if (!$stmt->execute($params)) {
            throw new Exception("Error al actualizar el perfil del perro");
        }

        $this->db->commit();
        return true;
    } catch (Exception $e) {
        $this->db->rollBack();
        throw new Exception("Error al actualizar el perfil del perro: " . $e->getMessage());
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
        
        // Calcular edad automáticamente si existe fecha_nacimiento
        if (!empty($perro['fecha_nacimiento'])) {
            $fecha_nacimiento = new DateTime($perro['fecha_nacimiento']);
            $hoy = new DateTime();
            $edad = $hoy->diff($fecha_nacimiento);
            // Mostrar en años y meses
            if ($edad->y > 0) {
                $perro['edad_calculada'] = $edad->y . ' año' . ($edad->y > 1 ? 's' : '');
                if ($edad->m > 0) {
                    $perro['edad_calculada'] .= ' y ' . $edad->m . ' mes' . ($edad->m > 1 ? 'es' : '');
                }
            } else {
                $perro['edad_calculada'] = $edad->m . ' mes' . ($edad->m > 1 ? 'es' : '');
            }
        } else if (isset($perro['edad'])) {
            // Compatibilidad: si no hay fecha_nacimiento, usar edad manual
            $perro['edad_calculada'] = $perro['edad'] . ' mes' . ($perro['edad'] == 1 ? '' : 'es');
        } else {
            $perro['edad_calculada'] = 'N/D';
        }
        
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

            // Calcular edad automáticamente si existe fecha_nacimiento
            if (!empty($perro['fecha_nacimiento'])) {
                $fecha_nacimiento = new DateTime($perro['fecha_nacimiento']);
                $hoy = new DateTime();
                $edad = $hoy->diff($fecha_nacimiento);
                // Mostrar en años y meses
                if ($edad->y > 0) {
                    $perro['edad_calculada'] = $edad->y . ' año' . ($edad->y > 1 ? 's' : '');
                    if ($edad->m > 0) {
                        $perro['edad_calculada'] .= ' y ' . $edad->m . ' mes' . ($edad->m > 1 ? 'es' : '');
                    }
                } else {
                    $perro['edad_calculada'] = $edad->m . ' mes' . ($edad->m > 1 ? 'es' : '');
                }
            } else if (isset($perro['edad'])) {
                // Compatibilidad: si no hay fecha_nacimiento, usar edad manual
                $perro['edad_calculada'] = $perro['edad'] . ' mes' . ($perro['edad'] == 1 ? '' : 'es');
            } else {
                $perro['edad_calculada'] = 'N/D';
            }

            // Si el campo disponible_apareamiento es NULL o 1, el perro es disponible por defecto
            // Si el usuario lo cambió a 0, se respeta como no disponible
            if (!isset($perro['disponible_apareamiento']) || $perro['disponible_apareamiento'] === null || $perro['disponible_apareamiento'] == 1) {
                $perro['disponible_apareamiento'] = 1;
            } else {
                $perro['disponible_apareamiento'] = 0;
            }
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

public function obtenerPerrosConValoraciones($filtros = []) {
    $sql = "
        SELECT 
            p.*,
            u.nombre as nombre_dueno,
            GROUP_CONCAT(DISTINCT r.nombre) as razas,
            COALESCE(AVG(v.puntuacion), 0) as valoracion_promedio,
            COUNT(DISTINCT v.id) as total_valoraciones
        FROM perros p
        LEFT JOIN usuarios u ON p.usuario_id = u.id
        LEFT JOIN raza_perro rp ON p.id = rp.perro_id
        LEFT JOIN razas_perros r ON rp.raza_id = r.id
        LEFT JOIN valoraciones v ON p.id = v.perro_id
        WHERE 1=1";

    $params = [];

    // Aplicar filtros si existen
    if (!empty($filtros['raza'])) {
        $sql .= " AND r.nombre LIKE :raza";
        $params[':raza'] = '%' . $filtros['raza'] . '%';
    }

    if (!empty($filtros['edad_min'])) {
        $sql .= " AND p.edad >= :edad_min";
        $params[':edad_min'] = $filtros['edad_min'];
    }

    if (!empty($filtros['edad_max'])) {
        $sql .= " AND p.edad <= :edad_max";
        $params[':edad_max'] = $filtros['edad_max'];
    }

    if (!empty($filtros['valoracion_min'])) {
        $sql .= " HAVING valoracion_promedio >= :valoracion_min";
        $params[':valoracion_min'] = $filtros['valoracion_min'];
    }

    $sql .= " GROUP BY p.id ORDER BY valoracion_promedio DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function obtenerPorId($perro_id) {
    try {
        // Obtener información básica del perro
        $sql = "SELECT p.*, u.nombre as nombre_dueno, u.telefono
                FROM perros p
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perro_id]);
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

public function obtenerPorUsuario($usuario_id) {
    $sql = "SELECT * FROM perros WHERE usuario_id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene el perro principal de un usuario para iniciar un chat o match.
 * Por ahora, devuelve el primer perro que encuentra.
 */
public function obtenerPerroParaMatch($usuario_id) {
    $sql = "SELECT id FROM perros WHERE usuario_id = :usuario_id LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Verifica si existe un match confirmado entre dos perros.
 */
public function existeMatch($perro_id_1, $perro_id_2) {
    $sql = "SELECT 1 FROM matches 
            WHERE (perro_id_origen = :perro1 AND perro_id_destino = :perro2)
               OR (perro_id_origen = :perro2 AND perro_id_destino = :perro1)
            LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':perro1' => $perro_id_1,
        ':perro2' => $perro_id_2
    ]);
    return (bool) $stmt->fetchColumn();
}

public function esMatch($perro_id_1, $perro_id_2) {
    $sql = "SELECT id FROM matches WHERE (perro1_id = ? AND perro2_id = ? AND estado = 'aceptado') OR (perro1_id = ? AND perro2_id = ? AND estado = 'aceptado')";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$perro_id_1, $perro_id_2, $perro_id_2, $perro_id_1]);
    return $stmt->fetchColumn() > 0;
}

}