<?php
require_once __DIR__ . '/../models/Matches.php';
require_once __DIR__ . '/../models/Perro.php';

class MatchesController {
    private $model;
    private $perroModel;

    public function __construct() {
        $this->model = new Matches();
        $this->perroModel = new Perro();
    }

    /**
     * Crea un nuevo match entre dos perros
     */
    public function crear($data) {
        if (!isset($data['dog_id']) || !isset($data['action'])) {
            throw new Exception('Datos incompletos');
        }

        // Verificar que el usuario tenga un perro registrado
        if (!isset($_SESSION['usuario']['id'])) {
            throw new Exception('Usuario no autenticado');
        }

        $miPerro = $this->perroModel->obtenerUnicoPorUsuarioId($_SESSION['usuario']['id']);
        if (!$miPerro) {
            throw new Exception('No tienes un perro registrado');
        }

        // Crear el match
        $matchData = [
            'perro_origen_id' => $miPerro['id'],
            'perro_destino_id' => $data['dog_id'],
            'tipo' => $data['action'] === 'match' ? 'match' : 'no_match'
        ];

        return $this->model->crear($matchData);
    }

    /**
     * Obtiene los matches de un perro
     */
    public function obtenerMatches($perro_id) {
        return $this->model->obtenerPorPerroId($perro_id);
    }

    /**
     * Verifica si existe un match entre dos perros
     */
    public function verificarMatch($perro1_id, $perro2_id) {
        return $this->model->existeMatch($perro1_id, $perro2_id);
    }

    /**
     * Muestra la vista de matches
     */
    public function verMatches() {
        if (!isset($_SESSION['usuario']['id'])) {
            header('Location: ?action=login');
            exit;
        }

        $miPerro = $this->perroModel->obtenerUnicoPorUsuarioId($_SESSION['usuario']['id']);
        if (!$miPerro) {
            $_SESSION['error'] = "No tienes un perro registrado";
            header('Location: ?action=dashboard');
            exit;
        }

        $matches = $this->obtenerMatches($miPerro['id']);
        include '../views/match/ver_match.php';
    }
} 