<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - DogMatch</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
   <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

  <link rel="stylesheet" href="../../public/css/dashboard.css">
</head>
<body>

<!-- Sidebar -->
<div class="d-flex">
  <nav id="sidebar" class="bg-danger text-white p-3 vh-100 position-fixed">
    <h4 class="text-center mb-4">DogMatch</h4>
    <ul class="nav flex-column">
      <li class="nav-item mb-2">
        <a href="../auth/perfil.php" class="nav-link text-white">
          <i class="bi bi-person-bounding-box me-2"></i>Ver perfil de mi perro
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="../match/perros_cards.php" class="nav-link text-white">
          <i class="bi bi-heart-fill me-2"></i>Buscar Matches (Tinder)
        </a>
      </li>
      <li class="nav-item mb-2">
        <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#modalMapa">
          <i class="bi bi-geo-alt-fill"></i> Ver perros cercanos
        </button>
      </li>
      <li class="nav-item mb-2">
        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalMatches">
          <i class="bi bi-heart-fill"></i> Ver Match
        </button>
      </li>
      <li class="nav-item mb-2">
        <a href="#" class="nav-link text-white">
          <i class="bi bi-gear me-2"></i>Configuración
        </a>
      </li>
      <li class="nav-item mt-4">
        <a href="logout.php" class="nav-link text-white">
          <i class="bi bi-box-arrow-right me-2"></i>Salir
        </a>
      </li>
    </ul>
  </nav>

  <!-- Contenido principal -->
  <div class="content-wrapper container-fluid">
    <!-- Filtros -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form class="row g-3" id="filtrosForm">
              <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Buscar por raza, edad..." id="busqueda">
              </div>
              <div class="col-md-2">
                <select class="form-select" id="filtroRaza">
                  <option value="">Todas las razas</option>
                  <!-- Se llenará dinámicamente -->
                </select>
              </div>
              <div class="col-md-2">
                <select class="form-select" id="filtroEdad">
                  <option value="">Cualquier edad</option>
                  <option value="0-6">0-6 meses</option>
                  <option value="7-12">7-12 meses</option>
                  <option value="13+">Más de 1 año</option>
                </select>
              </div>
              <div class="col-md-2">
                <select class="form-select" id="filtroValoracion">
                  <option value="">Cualquier valoración</option>
                  <option value="4">4+ estrellas</option>
                  <option value="3">3+ estrellas</option>
                  <option value="2">2+ estrellas</option>
                </select>
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="bi bi-search"></i> Buscar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Grid de perros -->
    <div class="row g-4" id="perrosGrid">
      <?php
      require_once __DIR__ . '/../../models/Perro.php';
      require_once __DIR__ . '/../../models/Valoracion.php';
      
      $perroModel = new Perro();
      $valoracionModel = new Valoracion();
      $perros = $perroModel->obtenerPerrosConValoraciones();

      foreach ($perros as $perro):
        $valoracion = $valoracionModel->obtenerPromedio($perro['id']);
        $promedio = number_format($valoracion['promedio'] ?? 0, 1);
        $totalValoraciones = $valoracion['total'] ?? 0;
      ?>
      <div class="col-md-6 col-lg-4 col-xl-3">
        <div class="card h-100 dog-card">
          <!-- Imagen del perro -->
          <img src="../../public/img/<?= htmlspecialchars($perro['foto'] ?? 'default-dog.jpg') ?>" 
               class="card-img-top dog-image" 
               alt="<?= htmlspecialchars($perro['nombre']) ?>">
          
          <!-- Información básica -->
          <div class="card-body">
            <h5 class="card-title d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($perro['nombre']) ?>
              <?php if ($perro['disponible_apareamiento']): ?>
                <span class="badge bg-success">Disponible</span>
              <?php endif; ?>
            </h5>
            
            <p class="card-text">
              <?= htmlspecialchars($perro['razas']) ?> • 
              <?= htmlspecialchars($perro['edad']) ?> <?= $perro['edad'] == 1 ? 'mes' : 'meses' ?> • 
              <?= htmlspecialchars($perro['sexo']) ?>
            </p>

            <!-- Sistema de valoración -->
            <div class="rating-stars mb-2">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="bi bi-star<?= $i <= $promedio ? '-fill' : ($i - 0.5 <= $promedio ? '-half' : '') ?> text-warning"></i>
              <?php endfor; ?>
              <small class="text-muted">(<?= $totalValoraciones ?>)</small>
            </div>

            <!-- Características -->
            <div class="characteristics mb-3">
              <?php if ($perro['vacunado']): ?>
                <span class="badge bg-info"><i class="bi bi-shield-check"></i> Vacunado</span>
              <?php endif; ?>
              <?php if ($perro['pedigri']): ?>
                <span class="badge bg-warning"><i class="bi bi-award"></i> Pedigrí</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="card-footer bg-white border-top-0">
            <div class="d-flex justify-content-between">
              <a href="../chat/mensajes.php?perro_id=<?= $perro['id'] ?>" 
                 class="btn btn-outline-primary btn-sm flex-grow-1 me-2">
                <i class="bi bi-chat"></i> Chat
              </a>
              <a href="perfil.php?id=<?= $perro['id'] ?>" 
                 class="btn btn-primary btn-sm flex-grow-1">
                <i class="bi bi-eye"></i> Ver Perfil
              </a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Modal del Chat -->
<div class="modal fade" id="chatModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Chat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="mensajesChat" class="chat-messages"></div>
        <div class="chat-input mt-3">
          <form id="formChat" class="d-flex">
            <input type="text" class="form-control me-2" id="mensajeChat" placeholder="Escribe un mensaje...">
            <button type="submit" class="btn btn-primary">Enviar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modales existentes -->
<?php include '../modals/mapa_modal.php'; ?>
<?php include '../modals/matches_modal.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="../../public/js/dashboard.js"></script>
<script src="../../public/js/ubicacion.js"></script>
<script src="../../public/js/chat.js"></script>

</body>
</html>
