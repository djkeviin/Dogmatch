<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: /Ignis360/Dogmatch/views/auth/login.php');
    exit;
}

require_once __DIR__ . '/../../models/Perro.php';

$perroModel = new Perro();
$perro_principal = null;

if (!isset($_SESSION['perro_id'])) {
    $misPerros = $perroModel->obtenerPorUsuario($_SESSION['usuario']['id']);
    if (!empty($misPerros)) {
        $_SESSION['perro_id'] = $misPerros[0]['id'];
    }
}

if (isset($_SESSION['perro_id'])) {
    $perro_principal = $perroModel->obtenerPorId($_SESSION['perro_id']);
}

if (!isset($_SESSION['usuario_id']) && isset($_SESSION['usuario']['id'])) {
    $_SESSION['usuario_id'] = $_SESSION['usuario']['id'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - DogMatch</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
   <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="../../public/css/dashboard.css?v=1.1">
</head>
<body>

<!-- Sidebar -->
<div class="d-flex">
  <nav id="sidebar" class="bg-naranja-dogmatch text-white p-3 vh-100 position-fixed">
    <div class="d-flex flex-column align-items-center mb-4">
      <img src="../../public/img/logo dg.jpg" alt="DogMatch Logo" class="logo-sidebar-dogmatch" />
      <button class="btn btn-link text-white d-lg-none" id="sidebarToggle">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <!-- Bloque de Perfil del Perro -->
    <?php if (isset($perro_principal) && $perro_principal): ?>
    <a href="perfil.php?id=<?= htmlspecialchars($perro_principal['id']) ?>" class="profile-block-sidebar">
        <img src="../../public/img/<?= htmlspecialchars($perro_principal['foto']) ?>" alt="Foto de <?= htmlspecialchars($perro_principal['nombre']) ?>">
        <div class="dog-name"><?= htmlspecialchars($perro_principal['nombre']) ?></div>
        <div class="view-profile-text">
            <i class="bi bi-paw-fill"></i> Ver Perfil
        </div>
    </a>
    <?php endif; ?>

    <ul class="nav flex-column">
      <li class="nav-item mb-2">
        <button class="btn btn-secondary w-100" data-bs-toggle="modal" data-bs-target="#modalMapa">
          <i class="bi bi-geo-alt-fill"></i> Ver perros cercanos
        </button>
      </li>
      <li class="nav-item mb-2">
        <a href="configuraciones.php" class="btn btn-secondary w-100">
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

  <!-- Overlay para móvil -->
  <div class="sidebar-overlay d-lg-none"></div>

  <!-- Contenido principal -->
  <div class="content-wrapper container-fluid position-relative" style="z-index:1; min-height: 100vh;">
    <!-- Indicador de carga -->
    <div id="loadingIndicator" class="text-center d-none">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form class="row g-3" id="filtrosForm">
              <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Buscar Por Nombre" id="busqueda">
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
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="bi bi-search"></i> Buscar
                </button>
              </div>
              <div class="col-md-1 d-flex align-items-center justify-content-center">
                <!-- Campana de notificaciones -->
                <div id="notiCampanaContainer" class="position-relative">
                    <button class="btn btn-link position-relative text-primary p-0" onclick="abrirModalNotificaciones()">
                        <i class="bi bi-bell fs-4"></i>
                        <span id="notiContador" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
                    </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Contador de resultados -->
    <div class="row mb-3">
      <div class="col">
        <p id="contador-resultados" class="text-muted"></p>
      </div>
    </div>

    <!-- Grid de perros -->
    <div class="row g-4" id="perrosGrid">
      <?php
      require_once __DIR__ . '/../../models/Perro.php';
      require_once __DIR__ . '/../../models/Valoracion.php';
      require_once __DIR__ . '/../../models/MatchPerro.php';
      
      $perroModel = new Perro();
      $valoracionModel = new Valoracion();
      $matchModel = new MatchPerro();
      $perros = $perroModel->obtenerPerrosConValoraciones();
      $miPerroId = $_SESSION['perro_id'] ?? null;

      foreach ($perros as $perro):
        $valoracion = $valoracionModel->obtenerPromedio($perro['id']);
        $promedio = number_format($valoracion['promedio'] ?? 0, 1);
        $totalValoraciones = $valoracion['total'] ?? 0;
        $mostrarChat = false;
        if ($miPerroId && $perro['id'] != $miPerroId) {
          $mostrarChat = $matchModel->esMatch($miPerroId, $perro['id']);
        }
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
              <?php else: ?>
                <span class="badge bg-secondary">No disponible</span>
              <?php endif; ?>
            </h5>
            
            <div class="card-text">
              <div><i class="bi bi-tag-fill me-2"></i><?= htmlspecialchars($perro['razas']) ?></div>
              <div>
                <i class="bi bi-calendar3 me-2"></i>
                <?php
                if (!empty($perro['fecha_nacimiento'])) {
                    $fecha_nacimiento = new DateTime($perro['fecha_nacimiento']);
                    $hoy = new DateTime();
                    $intervalo = $hoy->diff($fecha_nacimiento);
                    $meses = $intervalo->y * 12 + $intervalo->m;
                    if ($meses > 0) {
                        echo $meses . ' mes' . ($meses > 1 ? 'es' : '');
                    } else {
                        echo 'Recién nacido';
                    }
                } else if (isset($perro['edad'])) {
                    echo htmlspecialchars($perro['edad']) . ' mes' . ($perro['edad'] == 1 ? '' : 'es');
                } else {
                    echo 'N/D';
                }
                ?>
              </div>
              <div>
                <i class="bi <?= $perro['sexo'] == 'Macho' ? 'bi-gender-male' : 'bi-gender-female' ?> me-2"></i>
              <?= htmlspecialchars($perro['sexo']) ?>
              </div>
            </div>

            <!-- Sistema de valoración -->
            <div class="rating-stars mb-2 mt-3">
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
              <a href="perfil.php?id=<?= $perro['id'] ?>" 
                   class="btn btn-primary btn-sm w-100">
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

<!-- Modales existentes -->
<?php include '../modals/mapa_modal.php'; ?>

<!-- Botones en el sidebar (ya insertados por JS) -->

<!-- Modal: Solicitudes de Match Pendientes -->
<div class="modal fade" id="modalSolicitudesMatch" tabindex="-1" aria-labelledby="modalSolicitudesMatchLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="modalSolicitudesMatchLabel"><i class="bi bi-hourglass-split"></i> Solicitudes de Match Pendientes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="contenedorSolicitudesPendientes">
        <div class="text-center text-muted">Cargando solicitudes...</div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Matches Confirmados -->
<div class="modal fade" id="modalMatchesConfirmados" tabindex="-1" aria-labelledby="modalMatchesConfirmadosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalMatchesConfirmadosLabel"><i class="bi bi-people-fill"></i> Matches Confirmados</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
      <div class="modal-body" id="contenedorMatchesConfirmados">
        <div class="text-center text-muted">Cargando matches...</div>
      </div>
    </div>
  </div>
</div>

<!-- Modal de notificaciones -->
<div class="modal fade" id="modalNotificaciones" tabindex="-1" aria-labelledby="modalNotificacionesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalNotificacionesLabel"><i class="bi bi-bell"></i> Notificaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="contenedorNotificaciones">
        <div class="text-center text-muted">Cargando notificaciones...</div>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="../../public/js/dashboard.js"></script>
<script src="../../public/js/ubicacion.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Agregar botones al sidebar si existe
    const sidebar = document.querySelector('.sidebar, .dashboard-sidebar, .nav.flex-column');
    if (sidebar) {
        let btns = document.createElement('div');
        btns.innerHTML = `
            <button class="btn btn-secondary w-100 mb-2" onclick="scrollToSection('solicitudesPendientes')">
                <i class="bi bi-hourglass-split"></i> Solicitudes de Match
            </button>
            <button class="btn btn-secondary w-100 mb-2" onclick="scrollToSection('matchesConfirmados')">
                <i class="bi bi-people-fill"></i> Matches Confirmados
            </button>
        `;
        sidebar.prepend(btns);
    }
    cargarContadorNotificaciones();
});

function scrollToSection(id) {
    if (id === 'solicitudesPendientes') {
        abrirModalSolicitudes();
    } else if (id === 'matchesConfirmados') {
        abrirModalMatches();
    }
}

function abrirModalSolicitudes() {
    const modal = new bootstrap.Modal(document.getElementById('modalSolicitudesMatch'));
    modal.show();
    cargarSolicitudesPendientes();
}

function abrirModalMatches() {
    const modal = new bootstrap.Modal(document.getElementById('modalMatchesConfirmados'));
    modal.show();
    cargarMatchesConfirmados();
}

function cargarSolicitudesPendientes() {
    fetch('../../controllers/match/pendientes.php')
        .then(res => res.json())
        .then(data => {
            const cont = document.getElementById('contenedorSolicitudesPendientes');
            if (!data.success) {
                cont.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                return;
            }
            if (data.pendientes.length === 0) {
                cont.innerHTML = '<div class="text-center text-muted">No tienes solicitudes pendientes.</div>';
                return;
            }
            let html = '<ul class="list-group">';
            data.pendientes.forEach(s => {
                html += `<li class="list-group-item d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <img src="../../public/img/${s.foto_interesado || 'default-dog.png'}" alt="${s.nombre_interesado}" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;">
                        <div>
                            <strong>${s.nombre_interesado}</strong> quiere hacer match con <strong>${s.nombre_perro}</strong>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-success btn-sm me-1" onclick="responderMatch(${s.perro_id}, ${s.interesado_id}, 'aceptar')"><i class="bi bi-check"></i></button>
                        <button class="btn btn-danger btn-sm" onclick="responderMatch(${s.perro_id}, ${s.interesado_id}, 'rechazar')"><i class="bi bi-x"></i></button>
                    </div>
                </li>`;
            });
            html += '</ul>';
            cont.innerHTML = html;
        });
}

function responderMatch(perroId, interesadoId, accion) {
    fetch('../../controllers/match/responder.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            perro_id: perroId,
            interesado_id: interesadoId,
            accion: accion
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: accion === 'aceptar' ? '¡Match Confirmado!' : 'Solicitud Rechazada',
                text: data.message,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
            cargarSolicitudesPendientes();
            cargarMatchesConfirmados(); // Recargar por si se creó un nuevo match
        } else {
            Swal.fire({
                title: 'Error',
                text: data.error,
                icon: 'error'
            });
        }
    });
}

function cargarMatchesConfirmados() {
    const miPerroId = <?= isset($_SESSION['perro_id']) ? $_SESSION['perro_id'] : 'null' ?>;
    const cont = document.getElementById('contenedorMatchesConfirmados');
    if (!miPerroId) {
        cont.innerHTML = '<div class="alert alert-info">Debes registrar un perro propio para ver tus matches.</div>';
        return;
    }
    fetch('../../controllers/match/obtener_matches.php?perro_id=' + miPerroId)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                cont.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                return;
            }
            if (data.matches.length === 0) {
                cont.innerHTML = '<div class="text-center text-muted">No tienes matches confirmados aún.</div>';
                return;
            }
            let html = '<ul class="list-group">';
            data.matches.forEach(m => {
                const otroId = m.perro1_id == miPerroId ? m.perro2_id : m.perro1_id;
                html += `<li class="list-group-item d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <img src="../../public/img/${m.foto_perro}" alt="${m.nombre_perro}" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;">
                        <div>
                            <strong>${m.nombre_perro}</strong>
                        </div>
                    </div>
                    <a href="../chat/mensajes.php?perro_id=${otroId}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-chat-dots"></i> Chatear
                    </a>
                </li>`;
            });
            html += '</ul>';
            cont.innerHTML = html;
        });
}

function cargarContadorNotificaciones() {
    fetch('../../controllers/notificaciones/obtener.php')
        .then(res => res.json())
        .then(data => {
            const contador = document.getElementById('notiContador');
            if (data.success && data.notificaciones.length > 0) {
                contador.textContent = data.notificaciones.length;
                contador.style.display = 'inline-block';
            } else {
                contador.style.display = 'none';
            }
        });
}

function abrirModalNotificaciones() {
    const modal = new bootstrap.Modal(document.getElementById('modalNotificaciones'));
    modal.show();
    cargarNotificaciones();
}

function cargarNotificaciones() {
    fetch('../../controllers/notificaciones/obtener.php')
        .then(res => res.json())
        .then(data => {
            const cont = document.getElementById('contenedorNotificaciones');
            if (!data.success) {
                cont.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                return;
            }
            if (data.notificaciones.length === 0) {
                cont.innerHTML = '<div class="text-center text-muted">No tienes notificaciones nuevas.</div>';
                return;
            }
            let html = '<ul class="list-group">';
            data.notificaciones.forEach(n => {
                html += `<li class="list-group-item d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bg-secondary me-2">${n.tipo}</span>
                        ${n.mensaje}
                        <br><small class="text-muted">${new Date(n.fecha_creacion).toLocaleString()}</small>
                    </div>
                    ${n.url ? `<button class='btn btn-outline-primary btn-sm' onclick='marcarYRedirigir(${n.id}, "${n.url}")'>Ver</button>` : ''}
                </li>`;
            });
            html += '</ul>';
            cont.innerHTML = html;
        });
}

function marcarYRedirigir(id, url) {
    fetch('../../controllers/notificaciones/marcar_leida.php', {
        method: 'POST',
        body: new URLSearchParams({ id })
    })
    .then(res => res.json())
    .then(() => {
        window.location.href = url;
    });
}
</script>

</body>
</html>
