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
      <li>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalMapa">
          <i class="bi bi-geo-alt-fill"></i> Ver perros cercanos
        </button>
      </li>
      <li class="nav-item mb-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMatches">
          <i class="bi bi-heart-fill"></i> Ver Match
        </button>
      </li>
      <li class="nav-item mb-2">
        <a href="#" class="nav-link text-white"><i class="bi bi-gear me-2"></i>Configuración</a>
      </li>
      <li class="nav-item mt-4">
        <a href="logout.php" class="nav-link text-white"><i class="bi bi-box-arrow-right me-2"></i>Salir</a>
      </li>
    </ul>
  </nav>

  <!-- Contenido -->

   <!-- Botón toggle para pantallas pequeñas -->
    <button class="btn btn-outline-primary d-md-none mb-3" id="toggleSidebar">
      <i class="bi bi-list"></i> Menú
    </button>

      <div class="content-wrapper container-fluid">
        <h2 class="mb-4">Bienvenido a DogMatch</h2>
        <p>Gestiona el perfil de tu perro desde el menú lateral.</p>
    
    </div>
  </div>
</div>

<!-- Modal para mostrar los perros compatibles -->
<div class="modal fade" id="modalMatches" tabindex="-1" aria-labelledby="modalMatchesLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalMatchesLabel">Perros Compatibles</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <?php
          require_once __DIR__ . '/../../models/Perro.php';

          $perroModel = new Perro();
          $usuarioId = $_SESSION['usuario']['id'];
          $mis_perros = $perroModel->obtenerPerfilCompletoPorUsuarioId($usuarioId);
          $matches = $perroModel->buscarPerrosCompatibles($mis_perros);
          ?>

          <?php foreach ($matches as $match): ?>
            <div class="col-md-4">
              <div class="card mb-4">
                <img src="/DogMatch/public/img/<?= htmlspecialchars($match['foto']) ?>" class="card-img-top" alt="Foto del perro">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($match['nombre']) ?></h5>
                  <p class="card-text">
                    <strong>Raza:</strong> <?= htmlspecialchars($match['raza']) ?><br>
                    <strong>Edad:</strong> <?= htmlspecialchars($match['edad']) ?><br>
                    <strong>Sexo:</strong> <?= htmlspecialchars($match['sexo']) ?>
                  </p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

          <?php if (empty($matches)): ?>
            <div class="col-12">
              <div class="alert alert-info text-center">No se encontraron perros compatibles.</div>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


<!-- Modal del Mapa -->
<div id="modalMapa" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Perros cercanos</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label for="rangoBusqueda" class="form-label">
            Rango de búsqueda (<span id="kmValor">5</span> km)
          </label>
          <input type="range" class="form-range" min="1" max="50" value="5" id="rangoBusqueda">
        </div>

        <div id="mapa" style="height: 500px;"></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/dashboard.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="../../public/js/ubicacion.js"></script>

</body>
</html>
