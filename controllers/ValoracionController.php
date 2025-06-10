<?php
require_once __DIR__ . '/../models/Valoracion.php';

class ValoracionController {
    private $model;

    public function __construct() {
        $this->model = new Valoracion();
    }

    public function obtenerValoracionesPerro($perro_id) {
        return $this->model->obtenerPorPerroId($perro_id);
    }

    public function agregarValoracion($data) {
        if (!isset($_SESSION['usuario'])) {
            throw new Exception('Usuario no autenticado');
        }

        if (!isset($data['perro_id']) || !isset($data['puntuacion'])) {
            throw new Exception('Datos incompletos');
        }

        return $this->model->crear([
            'perro_id' => $data['perro_id'],
            'usuario_id' => $_SESSION['usuario']['id'],
            'puntuacion' => $data['puntuacion'],
            'comentario' => $data['comentario'] ?? null
        ]);
    }

    public function obtenerPromedioValoraciones($perro_id) {
        return $this->model->obtenerPromedio($perro_id);
    }
} 