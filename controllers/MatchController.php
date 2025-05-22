<?php
<<<<<<< HEAD
require_once 'models/Perro.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$perroModel = new Perro();
$mis_perros = $perroModel->obtenerPorUsuarioId($usuario_id);

$matches = [];

if (!empty($mis_perros)) {
    $matches = $perroModel->buscarPerrosCompatibles($mis_perros);
}

include 'views/auth/ver_match.php';
=======
require_once __DIR__ . '/../models/Perro.php';

class MatchController {
  public function index() {
    session_start();
    if (!isset($_SESSION['usuario'])) {
        header('Location: login.php');
        exit;
    }
    $usuario_id = $_SESSION['usuario']['id'];
    $modelo = new Perro();
    $perrosCompatibles = $modelo->buscarCompatibles($usuario_id);

    require_once __DIR__ . '/../views/match/index.php';
  }
}
>>>>>>> 391ae4218a3fa9df854bc2922f957fbc09d3ead5
