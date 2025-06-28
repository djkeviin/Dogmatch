<!-- /views/auth/registro.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | DogMatch</title>
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>
<body>

<!-- Scripts base -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<?php
session_start();
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
    echo "
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: '¡Registro exitoso!',
            text: " . json_encode($mensaje) . ",
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'login.php';
            }
        });
    });
    </script>
    ";
}

if (isset($_SESSION['mensaje_error'])) {
    $mensaje_error = $_SESSION['mensaje_error'];
    unset($_SESSION['mensaje_error']);
    echo "
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: " . json_encode($mensaje_error) . ",
            confirmButtonText: 'Aceptar'
        });
    });
    </script>
    ";
}
?>

    <div class="form-container">
        <h2>Crear cuenta</h2>
        <form action="/public/index.php?action=registrar" method="POST" enctype="multipart/form-data">
            <!-- Datos del dueño -->
            <h3>Datos del dueño</h3>
            <input type="text" name="nombre_dueño" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="text" name="telefono" placeholder="Teléfono" required>

            <!-- Datos del perro -->
            <h3>Datos del perro</h3>
            <input type="text" name="nombre_perro" placeholder="Nombre del perro" required>
            
            <select class="raza-select" name="raza" required>
                <option value="">Buscar raza...</option>
            </select>

            <!-- Tarjeta de información de la raza -->
            <div class="raza-card">
                <h4 class="raza-nombre"></h4>
                <p class="raza-descripcion"></p>
                <div>
                    <p><strong>Tamaño:</strong> <span class="raza-tamanio"></span></p>
                    <p><strong>Grupo:</strong> <span class="raza-grupo"></span></p>
                    <p><strong>Características:</strong></p>
                    <div class="caracteristicas-lista"></div>
                </div>
            </div>

            <!-- Reemplazar campo de edad por fecha de nacimiento -->
            <label for="fecha_nacimiento">Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" required>

            <select name="sexo" required>
                <option value="">Selecciona sexo</option>
                <option value="Macho">Macho</option>
                <option value="Hembra">Hembra</option>
            </select>

            <label for="foto">Agregar foto:</label>
            <input type="file" id="foto" name="foto" accept="image/*" required>

            <button type="submit" name="registrar">Registrarse</button>
        </form>

        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    </div>

    <!-- Script personalizado -->
    <script src="../../public/js/registro.js"></script>
</body>
</html> 