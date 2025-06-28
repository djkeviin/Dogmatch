<?php
require_once __DIR__ . '/../config/conexion.php';

class Multimedia {
    protected $conexion;

    public function __construct() {
        $this->conexion = Conexion::getConexion();
    }

    /**
     * Crea un nuevo registro de multimedia en la base de datos.
     *
     * @param array $data Datos del archivo a crear.
     * @return int|false El ID del nuevo registro o false si falla.
     */
    public function crear(array $data) {
        // Define las columnas esperadas y sus valores por defecto
        $columnas = [
            'perro_id'    => null,
            'tipo'        => 'desconocido',
            'url_archivo' => null,
            'descripcion' => null,
            'tamano'      => null,
            'mime_type'   => null
        ];

        // Filtra los datos de entrada para usar solo las columnas que existen en la tabla
        $datos_a_insertar = array_intersect_key($data, $columnas);

        // Prepara la consulta SQL dinámicamente
        $nombres_columnas = implode(', ', array_keys($datos_a_insertar));
        $placeholders = ':' . implode(', :', array_keys($datos_a_insertar));

        try {
            $sql = "INSERT INTO multimedia ($nombres_columnas, fecha_subida) VALUES ($placeholders, NOW())";
            $stmt = $this->conexion->prepare($sql);
            
            // Vincula los parámetros
            foreach ($datos_a_insertar as $columna => &$valor) {
                $stmt->bindParam(':' . $columna, $valor);
            }
            
            if ($stmt->execute()) {
                return $this->conexion->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log('Error en Modelo Multimedia::crear: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorPerroId($perro_id) {
        $sql = "SELECT * FROM multimedia WHERE perro_id = ? ORDER BY fecha_subida DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$perro_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM multimedia WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id]);
    }
} 