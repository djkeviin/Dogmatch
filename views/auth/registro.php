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
</body>
</html>
