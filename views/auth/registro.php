<!-- /views/auth/registro.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro | DogMatch</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
</head>
<body>
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
            <input type="text" name="raza" placeholder="Raza" required>
            <input type="number" name="edad" placeholder="Edad (años)" min="0" required>

            <select name="sexo" required>
                <option value="">Selecciona sexo</option>
                <option value="Macho">Macho</option>
                <option value="Hembra">Hembra</option>
            </select>

            <label>Foto del perro:</label>
            <input type="file" name="foto" accept="image/*" required>

            <button type="submit" name="registrar">Registrarse</button>
        </form>

        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    </div>
</body>
</html>


