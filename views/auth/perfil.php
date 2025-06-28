<?php
require_once '../../config/conexion.php';
require_once '../../models/Perro.php';
require_once '../../models/RazaPerro.php';
require_once '../../models/Valoracion.php';
require_once '../../models/MatchPerro.php';
session_start();

// Verificar si hay un usuario logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// *** NUEVO: Obtener los perros del usuario actual para la selección de match ***
$misPerrosModel = new Perro();
$misPerros = $misPerrosModel->obtenerPorUsuario($_SESSION['usuario']['id']);

// Obtener ID del perro
$perro_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Instanciar modelos y obtener conexión
$conn = Conexion::getConexion();
$perroModel = new Perro();
$valoracionModel = new Valoracion();
$matchModel = new MatchPerro();

// Obtener el ID del perro de la URL o usar el del usuario logueado
if (isset($_GET['id'])) {
    $perro = $perroModel->obtenerPorId($perro_id);
    $es_propietario = $perro && $perro['usuario_id'] == $_SESSION['usuario']['id'];
} else {
    $usuario_id = $_SESSION['usuario']['id'];
    $perro = $perroModel->obtenerUnicoPorUsuarioId($usuario_id);
    $es_propietario = true;
}

if (!$perro) {
    header('Location: dashboard.php?error=perro_no_encontrado');
    exit;
}

// Nueva lógica para verificar el match
$miPerroId = $_SESSION['perro_id'] ?? null;
$existeMatch = false;
if ($miPerroId && !$es_propietario) {
    $existeMatch = $matchModel->esMatch($miPerroId, $perro['id']);
}

$multimedia = $perroModel->obtenerMultimediaPorPerroId($perro['id']);

// Obtener valoraciones
$valoraciones = $valoracionModel->obtenerValoracionesPerro($perro_id);
$promedio = $valoracionModel->obtenerPromedio($perro_id);
$mi_valoracion = $valoracionModel->obtenerValoracionUsuario($perro_id, $_SESSION['usuario']['id']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($perro['nombre']) ?> - DogMatch</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="../../public/css/perfil.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <?php if (isset($perro['latitud']) && isset($perro['longitud']) && $perro['latitud'] && $perro['longitud']): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <?php endif; ?>
</head>
<body>

<!-- Notificaciones -->
<?php 
// Código de depuración
if (isset($perro['foto'])) {
    echo '<!-- Debug info:';
    echo 'Foto: ' . htmlspecialchars($perro['foto']) . '<br>';
    echo 'Ruta completa: ' . __DIR__ . '/../../public/img/' . $perro['foto'] . '<br>';
    echo 'Existe: ' . (file_exists(__DIR__ . '/../../public/img/' . $perro['foto']) ? 'Sí' : 'No') . '<br>';
    echo '-->';
}
?>
<?php if (isset($_SESSION['mensaje']) || isset($_SESSION['error'])): ?>
<div class="notification">
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../auth/dashboard.php">🐶 DogMatch</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../auth/dashboard.php">
                        <i class="bi bi-house-door"></i> Menu Principal
                    </a>
                </li>
                <?php if ($es_propietario): ?>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="$('#editarPerfilModal').modal('show')">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Encabezado del perfil -->
<div class="profile-header">
    <div class="container text-center">
        <div class="profile-image-container">
            <?php if (!empty($perro['foto']) && file_exists(__DIR__ . '/../../public/img/' . $perro['foto'])): ?>
                <img src="../../public/img/<?= rawurlencode($perro['foto']) ?>" 
                     alt="<?= htmlspecialchars($perro['nombre']) ?>" 
                     class="profile-image mb-3">
            <?php else: ?>
                <img src="../../public/img/default-dog.png" 
                     alt="<?= htmlspecialchars($perro['nombre']) ?>" 
                     class="profile-image mb-3">
            <?php endif; ?>
        </div>
        <h1 class="text-white"><?= htmlspecialchars($perro['nombre']) ?></h1>
        <p class="lead">
            <?php if (!empty($perro['razas'])): ?>
                <?php 
                $razas_nombres = array_map(function($raza) {
                    return htmlspecialchars($raza['nombre']);
                }, $perro['razas']);
                echo implode(' • ', $razas_nombres);
                ?> • 
            <?php endif; ?>
            <?php
            if (!empty($perro['fecha_nacimiento'])) {
                $fecha_nacimiento = new DateTime($perro['fecha_nacimiento']);
                $hoy = new DateTime();
                $intervalo = $hoy->diff($fecha_nacimiento);
                $meses = $intervalo->y * 12 + $intervalo->m;
                if ($meses > 0) {
                    echo $meses . ' mes' . ($meses > 1 ? 'es' : '');
                } else {
                    echo 'recién nacido';
                }
            } else if (isset($perro['edad'])) {
                echo htmlspecialchars($perro['edad']) . ' mes' . ($perro['edad'] == 1 ? '' : 'es');
            } else {
                echo 'N/D';
            }
            ?> • 
            <?= htmlspecialchars($perro['sexo']) ?>
            <?php if ($perro['peso']): ?>
                • <?= htmlspecialchars($perro['peso']) ?> kg
            <?php endif; ?>
        </p>
        <?php if ($es_propietario): ?>
        <button type="button" class="btn btn-light" onclick="$('#editarPerfilModal').modal('show')">
            <i class="bi bi-pencil"></i> Editar Perfil
        </button>
        <?php else: ?>
            <?php if ($existeMatch): ?>
                <a href="../chat/mensajes.php?perro_id=<?= $perro['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-chat"></i> Chatear
                </a>
            <?php else: ?>
                <button class="btn btn-primary" onclick="mostrarAlertaNoMatch()">
                    <i class="bi bi-chat"></i> Chatear
                </button>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($perro['usuario_id'] != $_SESSION['usuario_id']): ?>
            <button class="btn btn-success" id="btnMatch" onclick="enviarSolicitudMatch(<?= $perro['id'] ?>)">
                <i class="bi bi-heart"></i> Me interesa
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <!-- Información principal -->
        <div class="col-md-8">
            <div class="card stat-card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Sobre mí</h3>
                        <span class="badge <?= $perro['disponible_apareamiento'] ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $perro['disponible_apareamiento'] ? 'Disponible' : 'No disponible' ?>
                        </span>
                    </div>
                    <p class="card-text"><?= htmlspecialchars($perro['descripcion'] ?? 'Sin descripción') ?></p>
                    
                    <h4 class="mt-4">Características</h4>
                    <div class="characteristics-badges">
                        <?php if ($perro['vacunado']): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-shield-check"></i> Vacunado
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($perro['pedigri']): ?>
                            <span class="badge bg-warning badge-custom">
                                <i class="bi bi-award"></i> Pedigrí
                            </span>
                        <?php endif; ?>

                        <?php if ($perro['sociable_perros']): ?>
                            <span class="badge bg-info badge-custom">
                                <i class="bi bi-heart"></i> Sociable con perros
                            </span>
                        <?php endif; ?>

                        <?php if ($perro['sociable_personas']): ?>
                            <span class="badge bg-info badge-custom">
                                <i class="bi bi-people"></i> Sociable con personas
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($perro['temperamento']) && $perro['temperamento']): ?>
                        <h4 class="mt-4">Temperamento</h4>
                        <p><?= htmlspecialchars($perro['temperamento']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Salud -->
            <div class="card stat-card mb-4">
                <div class="card-body">
                    <h3 class="card-title">
                        <i class="bi bi-heart-pulse"></i> Información de Salud
                    </h3>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Estado de Salud</h5>
                            <p><?= htmlspecialchars($perro['estado_salud'] ?? 'No especificado') ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Vacunas</h5>
                            <p><?= nl2br(htmlspecialchars($perro['vacunas'] ?? 'No especificado')) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Galería -->
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="card-title mb-0">
                            <i class="bi bi-images"></i> Galería
                        </h3>
                        <button type="button" class="btn btn-primary btn-sm" onclick="$('#subirFotosModal').modal('show')">
                            <i class="bi bi-plus-circle"></i> Añadir fotos
                        </button>
                    </div>
                    <?php if (!empty($multimedia)): ?>
                    <div class="row g-3">
                        <?php foreach ($multimedia as $media): ?>
                            <?php if ($media['tipo'] === 'foto'): ?>
                            <div class="col-md-4">
                                <div class="position-relative">
                                    <img src="../../public/img/<?= htmlspecialchars($media['url_archivo']) ?>" 
                                         alt="<?= htmlspecialchars($media['descripcion'] ?? 'Imagen de ' . $perro['nombre']) ?>"
                                         class="gallery-img">
                                    <?php if ($media['descripcion']): ?>
                                        <div class="image-caption">
                                            <?= htmlspecialchars($media['descripcion']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-images" style="font-size: 3rem;"></i>
                        <p class="mt-2">No hay fotos en la galería</p>
                        <p class="small">Haz clic en "Añadir fotos" para comenzar a subir imágenes</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sistema de Valoración -->
            <div class="valoracion-container mb-4">
                <h4>Valoración</h4>
                <div class="d-flex align-items-center mb-3">
                    <div class="valoracion-estrellas me-3">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?= $i <= $promedio['promedio'] ? '-fill' : ($i - 0.5 <= $promedio['promedio'] ? '-half' : '') ?> text-warning"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="valoracion-texto">
                        <span class="h5 mb-0"><?= number_format($promedio['promedio'], 1) ?></span>
                        <small class="text-muted">(<?= $promedio['total'] ?> valoraciones)</small>
                    </div>
                </div>

                <?php if (!$es_propietario): ?>
                <!-- Formulario de valoración -->
                <div class="mi-valoracion mb-3">
                    <h5>Mi valoración</h5>
                    <div class="estrellas-interactivas">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?= $i <= ($mi_valoracion['puntuacion'] ?? 0) ? '-fill' : '' ?> text-warning estrella-valoracion"
                               data-valor="<?= $i ?>"
                               style="cursor: pointer; font-size: 1.5rem;"></i>
                        <?php endfor; ?>
                    </div>
                    <small class="text-muted" id="mensajeValoracion"></small>
                </div>
                <?php endif; ?>

                <!-- Lista de valoraciones recientes -->
                <div class="valoraciones-recientes">
                    <h5>Valoraciones recientes</h5>
                    <?php if (empty($valoraciones)): ?>
                        <p class="text-muted">Aún no hay valoraciones</p>
                    <?php else: ?>
                        <?php foreach ($valoraciones as $valoracion): ?>
                            <div class="valoracion-item border-bottom py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($valoracion['nombre_usuario']) ?></strong>
                                        <div>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= $valoracion['puntuacion'] ? '-fill' : '' ?> text-warning"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($valoracion['fecha_creacion'])) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Información de contacto -->
            <div class="owner-info">
                <h3>
                    <i class="bi bi-person"></i> Información del Dueño
                </h3>
                <p><strong>Nombre:</strong> <?= htmlspecialchars($perro['nombre_dueno']) ?></p>
                <p><strong>Teléfono:</strong> <?= htmlspecialchars($perro['telefono']) ?></p>
            </div>

            <!-- Ubicación -->
            <?php if (isset($perro['latitud']) && isset($perro['longitud']) && $perro['latitud'] && $perro['longitud']): ?>
            <div class="card stat-card mb-4">
                <div class="card-body">
                    <h3 class="card-title">
                        <i class="bi bi-geo-alt"></i> Ubicación
                    </h3>
                    <p><?= htmlspecialchars($perro['ubicacion'] ?? 'No especificada') ?></p>
                    <div id="mapa" 
                         style="height: 200px;" 
                         class="mt-3"
                         data-lat="<?= htmlspecialchars($perro['latitud']) ?>"
                         data-lng="<?= htmlspecialchars($perro['longitud']) ?>"
                         data-nombre="<?= htmlspecialchars($perro['nombre']) ?>">
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Apareamiento -->
            <?php if ($perro['disponible_apareamiento']): ?>
            <div class="breeding-section">
                <h3>
                    <i class="bi bi-heart"></i> Apareamiento
                </h3>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Disponible para apareamiento
                </div>
                <?php if ($perro['condiciones_apareamiento']): ?>
                <h5>Condiciones:</h5>
                <p><?= nl2br(htmlspecialchars($perro['condiciones_apareamiento'])) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Edición de Perfil -->
<div class="modal fade" id="editarPerfilModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="../../public/index.php?action=actualizar" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="actualizar">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del perro</label>
                            <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($perro['nombre']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Raza</label>
                            <select class="form-control raza-select" name="raza" required>
                                <?php 
                                // Obtener todas las razas
                                $razasModel = new RazaPerro();
                                $todasLasRazas = $razasModel->obtenerTodas();
                                foreach ($todasLasRazas as $raza): 
                                    $selected = false;
                                    if (!empty($perro['razas'])) {
                                        foreach ($perro['razas'] as $razaPerro) {
                                            if ($razaPerro['raza_id'] == $raza['id'] && $razaPerro['es_principal']) {
                                                $selected = true;
                                                break;
                                            }
                                        }
                                    }
                                ?>
                                    <option value="<?= htmlspecialchars($raza['id']) ?>" <?= $selected ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($raza['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Tarjeta de información de la raza -->
                    <div class="raza-card mb-3">
                        <h5 class="raza-nombre"></h5>
                        <p class="raza-descripcion"></p>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Tamaño:</strong> <span class="raza-tamanio"></span></p>
                                <p><strong>Grupo:</strong> <span class="raza-grupo"></span></p>
                            </div>
                            <div class="col-md-12">
                                <p><strong>Características:</strong></p>
                                <div class="caracteristicas-lista"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="fecha_nacimiento">Fecha de nacimiento</label>
                            <input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento" value="<?= htmlspecialchars($perro['fecha_nacimiento'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sexo</label>
                            <select class="form-select" name="sexo" required>
                                <option value="Macho" <?= $perro['sexo'] == 'Macho' ? 'selected' : '' ?>>Macho</option>
                                <option value="Hembra" <?= $perro['sexo'] == 'Hembra' ? 'selected' : '' ?>>Hembra</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tamaño</label>
                            <select class="form-select" name="tamanio" required>
                                <option value="pequeño" <?= $perro['tamanio'] == 'pequeño' ? 'selected' : '' ?>>Pequeño</option>
                                <option value="mediano" <?= $perro['tamanio'] == 'mediano' ? 'selected' : '' ?>>Mediano</option>
                                <option value="grande" <?= $perro['tamanio'] == 'grande' ? 'selected' : '' ?>>Grande</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" step="0.1" class="form-control" name="peso" value="<?= htmlspecialchars($perro['peso'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Foto de perfil</label>
                            <input type="file" class="form-control" name="foto" accept="image/*">
                            <?php if (!empty($perro['foto'])): ?>
                                <small class="form-text text-muted">Deja vacío para mantener la foto actual</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="3"><?= htmlspecialchars($perro['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Temperamento</label>
                        <textarea class="form-control" name="temperamento" rows="2"><?= htmlspecialchars($perro['temperamento'] ?? '') ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Estado de Salud</label>
                            <textarea class="form-control" name="estado_salud" rows="2"><?= htmlspecialchars($perro['estado_salud'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vacunas</label>
                            <textarea class="form-control" name="vacunas" rows="2"><?= htmlspecialchars($perro['vacunas'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="ubicacion" name="ubicacion" value="<?= htmlspecialchars($perro['ubicacion'] ?? '') ?>">
                            <input type="hidden" id="latitud" name="latitud" value="<?= htmlspecialchars($perro['latitud'] ?? '') ?>">
                            <input type="hidden" id="longitud" name="longitud" value="<?= htmlspecialchars($perro['longitud'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="disponible_apareamiento" name="disponible_apareamiento" <?= $perro['disponible_apareamiento'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="disponible_apareamiento">Disponible para apareamiento</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="pedigri" name="pedigri" <?= $perro['pedigri'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="pedigri">Tiene pedigrí</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="vacunado" name="vacunado" <?= $perro['vacunado'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="vacunado">Vacunado</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="sociable_perros" name="sociable_perros" <?= $perro['sociable_perros'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="sociable_perros">Sociable con perros</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="sociable_personas" name="sociable_personas" <?= $perro['sociable_personas'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="sociable_personas">Sociable con personas</label>
                            </div>
                        </div>
                    </div>

                    <div id="seccionApareamiento" class="mb-3" style="display: <?= $perro['disponible_apareamiento'] ? 'block' : 'none' ?>;">
                        <label class="form-label">Condiciones de Apareamiento</label>
                        <textarea class="form-control" name="condiciones_apareamiento" rows="3"><?= htmlspecialchars($perro['condiciones_apareamiento'] ?? '') ?></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para subir fotos -->
<div class="modal fade" id="subirFotosModal" tabindex="-1" aria-labelledby="subirFotosModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subirFotosModalLabel">Subir Fotos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubirFotos" action="../../controllers/MultimediaController.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fotos" class="form-label">Seleccionar fotos</label>
                        <input type="file" class="form-control" id="fotos" name="fotos[]" multiple accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <div id="previewFotos" class="preview-container d-flex flex-wrap"></div>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción (opcional)</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <input type="hidden" name="perro_id" value="<?= $perro['id'] ?>">
                    <input type="hidden" name="action" value="subirFotos">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Subir Fotos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="../../public/js/razas.js"></script>
<script src="../../public/js/perfil.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<?php if (isset($perro['latitud']) && isset($perro['longitud']) && $perro['latitud'] && $perro['longitud']): ?>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<?php endif; ?>
<?php if (isset($perro['latitud']) && isset($perro['longitud']) && $perro['latitud'] && $perro['longitud']): ?>
    <script src="../../public/js/mapa.js"></script>
<?php endif; ?>

<script>
// *** NUEVO: Pasar los perros del usuario a JavaScript ***
const misPerros = <?= json_encode($misPerros); ?>;

// Mostrar/ocultar sección de condiciones de apareamiento
document.getElementById('disponible_apareamiento').addEventListener('change', function() {
    document.getElementById('seccionApareamiento').style.display = this.checked ? 'block' : 'none';
});

document.addEventListener('DOMContentLoaded', function() {
    // Sistema de valoración interactivo
    const estrellas = document.querySelectorAll('.estrella-valoracion');
    const mensajeValoracion = document.getElementById('mensajeValoracion');

    estrellas.forEach(estrella => {
        // Hover effect
        estrella.addEventListener('mouseenter', function() {
            const valor = this.dataset.valor;
            estrellas.forEach(e => {
                if (e.dataset.valor <= valor) {
                    e.classList.remove('bi-star');
                    e.classList.add('bi-star-fill');
                } else {
                    e.classList.remove('bi-star-fill');
                    e.classList.add('bi-star');
                }
            });
        });

        // Click event
        estrella.addEventListener('click', function() {
            const valor = this.dataset.valor;
            valorarPerro(valor);
        });
    });

    // Restaurar valoración original al quitar el mouse
    const contenedorEstrellas = document.querySelector('.estrellas-interactivas');
    if (contenedorEstrellas) {
        contenedorEstrellas.addEventListener('mouseleave', restaurarValoracionOriginal);
    }

    function restaurarValoracionOriginal() {
        const valoracionActual = <?= $mi_valoracion['puntuacion'] ?? 0 ?>;
        estrellas.forEach(estrella => {
            if (estrella.dataset.valor <= valoracionActual) {
                estrella.classList.remove('bi-star');
                estrella.classList.add('bi-star-fill');
            } else {
                estrella.classList.remove('bi-star-fill');
                estrella.classList.add('bi-star');
            }
        });
    }

    function valorarPerro(puntuacion) {
        fetch('../../api/valorar_perro.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                perro_id: <?= $perro_id ?>,
                puntuacion: parseInt(puntuacion)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mensajeValoracion.textContent = '¡Valoración guardada!';
                mensajeValoracion.className = 'text-success';
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                throw new Error(data.error);
            }
        })
        .catch(error => {
            mensajeValoracion.textContent = 'Error al guardar la valoración';
            mensajeValoracion.className = 'text-danger';
        });
    }
});

function mostrarAlertaNoMatch() {
    Swal.fire({
        icon: 'info',
        title: 'Chat Bloqueado',
        text: 'Debes tener un match confirmado para poder chatear con el dueño de este perro.',
        confirmButtonText: 'Entendido'
    });
}

// *** FUNCIÓN COMPLETAMENTE REHECHA ***
function enviarSolicitudMatch(perroIdDestino) {
    if (!misPerros || misPerros.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'No tienes perros registrados',
            text: 'Debes registrar al menos un perro en tu perfil para poder enviar solicitudes de match.',
            confirmButtonText: 'Llevame a mi Perfil'
        }).then(() => {
            window.location.href = 'perfil.php';
        });
        return;
    }

    if (misPerros.length === 1) {
        const miPerroId = misPerros[0].id;
        confirmarYEnviar(perroIdDestino, miPerroId);
    } else {
        const inputOptions = {};
        misPerros.forEach(perro => {
            // Asumiendo que 'razas' es un string, tomamos la primera si hay varias
            const raza = perro.razas ? perro.razas.split(',')[0].trim() : 'Raza no especificada';
            inputOptions[perro.id] = `${perro.nombre} (${raza})`;
        });

        Swal.fire({
            title: '¿Con qué perro quieres hacer match?',
            input: 'select',
            inputOptions: inputOptions,
            inputPlaceholder: 'Selecciona tu perro',
            showCancelButton: true,
            confirmButtonText: 'Enviar Solicitud',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return '¡Necesitas elegir un perro!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const miPerroId = result.value;
                confirmarYEnviar(perroIdDestino, miPerroId);
            }
        });
    }
}

function confirmarYEnviar(perroIdDestino, miPerroId) {
     Swal.fire({
        title: '¿Confirmar solicitud?',
        text: "Se enviará una solicitud de match. ¿Deseas continuar?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../../controllers/match/solicitar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    perro_id: perroIdDestino,
                    interesado_id: miPerroId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Solicitud Enviada!', data.message, 'success');
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            });
        }
    });
}
</script>

</body>
</html>
