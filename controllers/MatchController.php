<?php
require_once 'models/Perro.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$perroModel = new Perro();
$mis_perros = $perroModel->obtenerPerfilCompletoPorUsuarioId($usuario_id);

$matches = [];

if (!empty($mis_perros)) {
    $matches = $perroModel->buscarPerrosCompatibles($mis_perros);
}

include 'views/auth/ver_match.php';

