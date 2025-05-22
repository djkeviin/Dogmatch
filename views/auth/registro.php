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

            <button type="submit" name="registrar" class="btn btn-success mt-3" onclick="mostrarModal('registroExitosoModal')">
                Registrarse</button>
        </form>

        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    </div>


    <!-- Modal de Registro Exitoso -->
<div class="modal fade" id="registroExitosoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
    <div class="modal-content text-center p-4">
      <div class="modal-body">
        <i class="bi bi-check-circle-fill" style="font-size: 3rem; color: #28a745;"></i>
        <h1 class="mt-3">¡Registro Exitoso!</h1>
        <p class="mt-2">Bienvenidos a <strong>Dog Match</strong></p>
        <p class="fst-italic">Conectando patas, creando lazos.</p>
      </div>
      <div class="modal-footer justify-content-center border-0">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">¡Vamos allá!</button>
      </div>
    </div>
  </div>
</div>

<!-- script de boostrap para el modal -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">


</script>

<!-- script de registro_modal -->
<script src="public/js/registro.js">

</script>


</body>
</html>
