<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuraciones</title>
    <!-- Bootstrap 5 CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>

    <div class="container d-flex flex-column align-items-center justify-content-center vh-100">

        <!-- Encabezado con flecha y título -->
        <div class="d-flex align-items-center mb-5">
            <a href="dashboard.php" class="me-3 text-decoration-none text-dark">
            <i class="bi bi-arrow-left" style="font-size: 3rem;"></i>
            </a>
            <h1 class="mb-0 fw-bold" style="font-size: 2rem;">Configuraciones y datos personales</h1>
        </div>

        <!-- Grid de tarjetas -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow rounded-4 text-center p-4">
                    <a href="configuracion.php"><i class="bi bi-people-fill text-success" style="font-size: 3rem;"></i></a>
                    <h5 class="mt-3">Usuario</h5>
                    <p class="text-muted">Gestiona tu perfil y mantén tu información actualizada.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow rounded-4 text-center p-4">
                    <a href="config_historial.php"><i class="bi bi-clock-history text-info" style="font-size: 3rem;"></i></a>
                    <h5 class="mt-3">Historial de Match</h5>
                    <p class="text-muted">Visualiza los matches realizados y su estado.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">

</script>


</body>
</html>