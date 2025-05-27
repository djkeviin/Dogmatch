<?php
require_once __DIR__ . '/../config/conexion.php';


class Perro {
    protected $db;

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
    $sql = "SELECT p.*, u.nombre as nombre_dueno, u.telefono
            FROM perros p
            JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.usuario_id = ?
            LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$usuario_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
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
    // Validar datos requeridos
    $campos_requeridos = ['nombre', 'raza', 'edad', 'sexo', 'usuario_id'];
    foreach ($campos_requeridos as $campo) {
        if (!isset($data[$campo]) || $data[$campo] === '') {
            throw new Exception("El campo " . ucfirst($campo) . " es requerido");
        }
    }

    // Validar edad en meses
    if (!is_numeric($data['edad']) || $data['edad'] < 0 || $data['edad'] > 300) { // 300 meses = 25 años
        throw new Exception("La edad debe ser un número válido entre 0 y 300 meses");
    }

    // Validar peso si está presente
    if (isset($data['peso']) && !empty($data['peso'])) {
        if (!is_numeric($data['peso']) || $data['peso'] < 0) {
            throw new Exception("El peso debe ser un número válido");
        }
    }

    $sql = "UPDATE perros SET 
            nombre = :nombre,
            raza = :raza,
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
            condiciones_apareamiento = :condiciones_apareamiento
            WHERE usuario_id = :usuario_id";
            
    $stmt = $this->db->prepare($sql);
    
    // Preparar los datos para la actualización
    $params = [
        ':nombre' => $data['nombre'],
        ':raza' => $data['raza'],
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
        ':usuario_id' => $data['usuario_id']
    ];

    if (!$stmt->execute($params)) {
        throw new Exception("Error al actualizar el perfil del perro");
    }
    return true;
}

}