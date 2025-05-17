<div class="container mt-4">
  <h2 class="mb-4"><i class="bi bi-heart-pulse me-2"></i> Perros compatibles</h2>
  <div class="row">
    <?php
    if (empty($perrosCompatibles)) {
        echo "<p>No hay perros compatibles por el momento.</p>";
    } else {
        foreach ($perrosCompatibles as $perro) {
            echo '<div class="col-md-4 mb-3">';
            echo '<div class="card shadow-sm">';
            echo '<img src="../../public/img/' . htmlspecialchars($perro['foto']) . '" class="card-img-top">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . htmlspecialchars($perro['nombre']) . '</h5>';
            echo '<p class="card-text">Raza: ' . htmlspecialchars($perro['raza']) . '</p>';
            echo '<p class="card-text">Edad: ' . htmlspecialchars($perro['edad']) . '</p>';
            echo '<p class="card-text">Sexo: ' . htmlspecialchars($perro['sexo']) . '</p>';
            echo '<form action="../../public/index.php?accion=hacerMatch" method="POST">';
            echo '<input type="hidden" name="perro_id" value="' . $perro['id'] . '">';
            echo '<button type="submit" class="btn btn-outline-danger w-100 mt-2"><i class="bi bi-heart-fill"></i> Hacer Match</button>';
            echo '</form>';
            echo '</div></div></div>';
        }
    }
    ?>
  </div>
</div>
