<?php
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
