
<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../controllers/PerroController.php';

$perroController = new PerroController();

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

$action = $_GET['action'] ?? '';

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
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $authController->login($email, $password);
        } else {
            header('Location: ../views/auth/login.php');
        }
        break;

        if ($_GET['accion'] == 'verMatch') {
    require_once '../views/match/ver_match.php';
}
    case 'dashboard':
        require_once '../views/auth/dashboard.php';
        break;

    default:
        header('Location: ../views/auth/index.php');
        break;

        case 'verMatches':
  require_once '../controllers/MatchController.php';
  $controller = new MatchController();
  $controller->index();
  break;

}

