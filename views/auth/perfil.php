<?php
require_once __DIR__ . '/../../models/Perro.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];
$perroModel = new Perro();
$perro = $perroModel->obtenerUnicoPorUsuarioId($usuario_id);

if (!$perro) {
    header('Location: dashboard.php?error=no_perro');
    exit;
}

$multimedia = $perroModel->obtenerMultimediaPorPerroId($perro['id']);
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
    <?php if (isset($perro['latitud']) && isset($perro['longitud']) && $perro['latitud'] && $perro['longitud']): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <?php endif; ?>
</head>
<body>

<!-- Notificaciones -->
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
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                </li>
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
            <img src="../../public/img/<?= htmlspecialchars($perro['foto']) ?>" alt="<?= htmlspecialchars($perro['nombre']) ?>" class="profile-image mb-3">
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
            <?= htmlspecialchars($perro['edad']) ?> <?= $perro['edad'] == 1 ? 'mes' : 'meses' ?> • 
            <?= htmlspecialchars($perro['sexo']) ?>
            <?php if ($perro['peso']): ?>
                • <?= htmlspecialchars($perro['peso']) ?> kg
            <?php endif; ?>
        </p>
        <button type="button" class="btn btn-light" onclick="$('#editarPerfilModal').modal('show')">
            <i class="bi bi-pencil"></i> Editar Perfil
        </button>
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
                    <div class="mb-3">
                        <?php if ($perro['sociable_perros']): ?>
                            <span class="badge bg-success badge-custom">
                                <i class="bi bi-check-circle"></i> Sociable con perros
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($perro['sociable_personas']): ?>
                            <span class="badge bg-success badge-custom">
                                <i class="bi bi-check-circle"></i> Sociable con personas
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($perro['esterilizado']): ?>
                            <span class="badge bg-info badge-custom">
                                <i class="bi bi-shield-check"></i> Esterilizado
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($perro['pedigri']): ?>
                            <span class="badge bg-warning badge-custom">
                                <i class="bi bi-award"></i> Pedigrí
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
                                <?php if (!empty($perro['razas'])): ?>
                                    <?php foreach ($perro['razas'] as $raza): ?>
                                        <?php if ($raza['es_principal']): ?>
                                            <option value="<?= htmlspecialchars($raza['raza_id']) ?>" selected>
                                                <?= htmlspecialchars($raza['nombre']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
                            <label class="form-label">Edad (meses)</label>
                            <input type="number" class="form-control" name="edad" value="<?= htmlspecialchars($perro['edad']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sexo</label>
                            <select class="form-select" name="sexo" required>
                                <option value="Macho" <?= $perro['sexo'] == 'Macho' ? 'selected' : '' ?>>Macho</option>
                                <option value="Hembra" <?= $perro['sexo'] == 'Hembra' ? 'selected' : '' ?>>Hembra</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" step="0.1" class="form-control" name="peso" value="<?= htmlspecialchars($perro['peso'] ?? '') ?>">
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
                            <label class="form-label">Foto de perfil</label>
                            <input type="file" class="form-control" name="foto" accept="image/*">
                        </div>
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
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="esterilizado" name="esterilizado" <?= $perro['esterilizado'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="esterilizado">Esterilizado</label>
                            </div>
                        </div>
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
<?php if (isset($perro['latitud']) && isset($perro['longitud']) && $perro['latitud'] && $perro['longitud']): ?>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<?php endif; ?>
<?php if (isset($perro['latitud']) && isset($perro['longitud']) && $perro['latitud'] && $perro['longitud']): ?>
    <script src="../../public/js/mapa.js"></script>
<?php endif; ?>

</body>
</html>
