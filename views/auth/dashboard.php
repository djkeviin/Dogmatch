<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}
$usuario = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - DogMatch</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS exclusivo -->
    <link rel="stylesheet" href="../../public/css/dashboard.css" />
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="#">DogMatch</a>
    <div class="ms-auto">
        <span class="navbar-text me-3">Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?>!</span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
    </div>
</nav>

<div class="container my-4">

    <h2 class="mb-4">Tus perros registrados</h2>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalAgregarPerro">
        <i class="bi bi-plus-circle"></i> Añadir nuevo perro
    </button>

    <div id="listaPerros" class="row">
        <?php
        require_once __DIR__ . '/../../models/Perro.php';
        $perroModel = new Perro();
        $perros = $perroModel->obtenerPorUsuarioId($usuario['id']); 
        if (empty($perros)) {
            echo '<p>No tienes perros registrados.</p>';
        } else {
            foreach ($perros as $perro) {
                echo '<div class="col-md-4 mb-3">';
                echo '<div class="card shadow-sm">';
                echo '<img src="../../public/img/' . htmlspecialchars($perro['foto']) . '" class="card-img-top" alt="Foto de ' . htmlspecialchars($perro['nombre']) . '">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . htmlspecialchars($perro['nombre']) . '</h5>';
                echo '<p class="card-text">Raza: ' . htmlspecialchars($perro['raza']) . '</p>';
                echo '<p class="card-text">Edad: ' . htmlspecialchars($perro['edad']) . '</p>';
                echo '<p class="card-text">Sexo: ' . htmlspecialchars($perro['sexo']) . '</p>';
                echo '</div></div></div>';
            }
        }
        ?>
    </div>

</div>

<!-- Modal para agregar perro -->
<div class="modal fade" id="modalAgregarPerro" tabindex="-1" aria-labelledby="modalAgregarPerroLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" action="../../public/index.php?accion=registrarPerro" method="POST" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAgregarPerroLabel">Agregar nuevo perro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
            <label for="nombre_perro" class="form-label">Nombre del perro</label>
            <input type="text" class="form-control" id="nombre_perro" name="nombre_perro" required />
          </div>
          <div class="mb-3">
            <label for="raza" class="form-label">Raza</label>
            <input type="text" class="form-control" id="raza" name="raza" required />
          </div>
          <div class="mb-3">
            <label for="edad" class="form-label">Edad</label>
            <input type="number" class="form-control" id="edad" name="edad" required min="0" />
          </div>
          <div class="mb-3">
            <label for="sexo" class="form-label">Sexo</label>
            <select class="form-select" id="sexo" name="sexo" required>
              <option value="">Selecciona</option>
              <option value="Macho">Macho</option>
              <option value="Hembra">Hembra</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="foto" class="form-label">Foto</label>
            <input type="file" class="form-control" id="foto" name="foto" accept="image/*" required />
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar perro</button>
      </div>
    </form>
  </div>
</div>

<!-- Bootstrap JS Bundle CDN (incluye Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Tu JS exclusivo -->
<script src="../../public/js/dashboard.js"></script>

</body>
</html>
