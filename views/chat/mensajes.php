<?php
require_once '../../config/conexion.php';
require_once '../../models/Mensaje.php';
require_once '../../models/Perro.php';
require_once '../../models/Usuario.php';
session_start();

// Verificar si hay un usuario logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Establecer el ID del usuario en la sesión si no existe
if (!isset($_SESSION['usuario_id']) && isset($_SESSION['usuario']['id'])) {
    $_SESSION['usuario_id'] = $_SESSION['usuario']['id'];
}

// Verificar si tenemos el ID del usuario
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Obtener ID del perro si se proporciona en la URL
$perro_id = isset($_GET['perro_id']) ? intval($_GET['perro_id']) : null;

// Instanciar modelos
$mensajeModel = new Mensaje();
$perroModel = new Perro();
$usuarioModel = new Usuario();

// Si hay un perro_id, obtener sus datos
$perro_activo = null;
$dueno_perro = null;
if ($perro_id) {
    $perro_activo = $perroModel->obtenerPorId($perro_id);
    if ($perro_activo) {
        $dueno_perro = $usuarioModel->obtenerPorId($perro_activo['usuario_id']);
    }
}

// Actualizar la última actividad del usuario actual
$usuarioModel->actualizarActividad($_SESSION['usuario']['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - DogMatch</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../public/css/chat.css" rel="stylesheet">
</head>
<body>

<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../auth/dashboard.php">🐶 DogMatch</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../auth/dashboard.php">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/perfil.php">
                        <i class="bi bi-person"></i> Mi Perfil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/logout.php">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Contenedor principal del chat -->
<div class="chat-container">
    <!-- Sidebar - Lista de conversaciones -->
    <div class="chat-sidebar" id="chatSidebar">
        <div class="search-container">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" placeholder="Buscar conversación...">
            </div>
        </div>
        <div class="conversations-list">
            <!-- Las conversaciones se cargarán dinámicamente aquí -->
        </div>
    </div>

    <!-- Chat principal -->
    <div class="chat-main" id="chatMain">
        <?php if ($perro_activo && $dueno_perro): ?>
            <!-- Header del chat -->
            <div class="chat-header">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link d-lg-none me-2" onclick="toggleSidebar()">
                        <i class="bi bi-list"></i>
                    </button>
                    <img src="../../public/img/<?= htmlspecialchars($perro_activo['foto'] ?? 'default-dog.png') ?>" 
                         alt="<?= htmlspecialchars($perro_activo['nombre']) ?>" 
                         class="chat-header-img">
                    <div class="chat-header-info" data-usuario-id="<?= htmlspecialchars($dueno_perro['id']) ?>">
                        <h5 class="mb-0"><?= htmlspecialchars($perro_activo['nombre']) ?></h5>
                        <small class="estado-usuario">Verificando estado...</small>
                    </div>
                </div>
                <button class="btn btn-link d-lg-none" onclick="toggleInfo()">
                    <i class="bi bi-info-circle"></i>
                </button>
            </div>

            <!-- Área de mensajes -->
            <div class="chat-messages" id="chatMessages">
                <!-- Los mensajes se cargarán dinámicamente aquí -->
            </div>

            <!-- Input de mensaje -->
            <?php if ($perro_activo['usuario_id'] != $_SESSION['usuario_id']): ?>
                <div class="chat-input">
                    <form id="chatForm" class="d-flex align-items-center">
                        <button type="button" class="btn btn-link">
                            <i class="bi bi-image"></i>
                        </button>
                        <input type="text" class="form-control" id="mensajeInput" name="mensaje"
                               placeholder="Escribe un mensaje...">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="chat-input text-center p-3 bg-light">
                    <p class="text-muted mb-0">
                        <i class="bi bi-info-circle"></i>
                        Este es tu perro, no puedes enviarte mensajes a ti mismo
                    </p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Mensaje cuando no hay chat seleccionado -->
            <div class="chat-empty-state">
                <i class="bi bi-chat-dots"></i>
                <h4>Selecciona una conversación</h4>
                <p>O inicia una nueva desde el perfil de un perro</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Panel de información -->
    <div class="chat-info" id="chatInfo">
        <?php if ($perro_activo): ?>
            <div class="text-center p-3">
                <img src="../../public/img/<?= htmlspecialchars($perro_activo['foto'] ?? 'default-dog.png') ?>" 
                     alt="<?= htmlspecialchars($perro_activo['nombre']) ?>" 
                     class="chat-info-img mb-3">
                <h4><?= htmlspecialchars($perro_activo['nombre']) ?></h4>
                <p class="text-muted">
                    <?php if (!empty($perro_activo['razas'])): ?>
                        <?= htmlspecialchars(implode(', ', array_column($perro_activo['razas'], 'nombre'))) ?> •
                    <?php endif; ?>
                    <?= htmlspecialchars($perro_activo['edad']) ?> meses
                </p>
                <div class="d-grid gap-2">
                    <a href="../auth/perfil.php?id=<?= $perro_activo['id'] ?>" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> Ver Perfil
                    </a>
                    <button class="btn btn-outline-danger">
                        <i class="bi bi-exclamation-triangle"></i> Reportar
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Variables globales para el chat
    const userId = <?= json_encode($_SESSION['usuario_id']) ?>;
    const userName = <?= json_encode($_SESSION['usuario']['nombre']) ?>;
    const perroId = <?= json_encode($perro_id) ?>;
    
    // Asignar perroId a perroActualId al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof perroActualId === 'undefined') {
            perroActualId = perroId;
        }
        console.log('Variables globales inicializadas:', { userId, userName, perroId, perroActualId });
    });
</script>
<script src="../../public/js/chat.js"></script>

</body>
</html> 