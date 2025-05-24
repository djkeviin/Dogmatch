<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../controllers/PerroController.php';

$perroController = new PerroController();

// Manejo de registrarPerro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['accion']) && $_GET['accion'] === 'registrarPerro') {
    if (!isset($_SESSION['usuario'])) {
        header('Location: login.php');
        exit;
    }
    $usuarioId = $_SESSION['usuario']['id'];
    $perroController->registrar($_POST, $_FILES, $usuarioId);
    exit;
}

require_once __DIR__ . '/../controllers/AuthController.php';

$authController = new AuthController();

// Mueve este if antes o después del switch
if (isset($_GET['accion']) && $_GET['accion'] == 'verMatch') {
    require_once '../views/match/ver_match.php';
    exit;  // Para evitar que siga con el switch
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'registrar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->registrar($_POST, $_FILES);
        } else {
            header('Location: ../views/auth/registro.php');
        }
        break;

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $authController->login($email, $password);
        } else {
            header('Location: ../views/auth/login.php');
        }
        break;

    case 'dashboard':
        require_once '../views/auth/dashboard.php';
        break;

    default:
        header('Location: ../views/auth/index.php');
        break;
}
