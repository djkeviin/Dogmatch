<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: /login");
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perros Compatibles</title>
    <link rel="stylesheet" href="../../css/ver_match.css"> <!-- Tu propio CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <?php if (!isset($matches)) $matches = []; ?>
    <h2>Perros Compatibles</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">Volver</a>
    

    <div class="row">
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
                <div class="alert alert-info">No se encontraron perros compatibles.</div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
