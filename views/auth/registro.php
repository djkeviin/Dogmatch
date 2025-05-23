<!-- /views/auth/registro.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro | DogMatch</title>
    <link rel="stylesheet" href="../../public/css/styles.css">

</head>
<body>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

            <select name="raza" required>
                <option value="">Selecciona la raza</option>
                <option value="Labrador Retriever">Labrador Retriever</option>
                <option value="French Poodle">French Poodle</option>
                <option value="Bulldog Francés">Bulldog Francés</option>
                <option value="Golden Retriever">Golden Retriever</option>
                <option value="Shih Tzu">Shih Tzu</option>
                <option value="Yorkshire Terrier">Yorkshire Terrier</option>
                <option value="Pastor Alemán">Pastor Alemán</option>
                <option value="Beagle">Beagle</option>
                <option value="Chihuahua">Chihuahua</option>
                <option value="Cocker Spaniel">Cocker Spaniel</option>
                <option value="Criollo">Criollo</option>
            </select>

            <input type="number" name="edad" placeholder="Edad (en meses)" min="1" required>

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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
