<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../public/css/configuracion.css">
</head>
<body>



<!-- Configuración de cuenta -->
<div class="container d-flex justify-content-center align-items-center vh-100">
  <div class="row shadow-lg rounded-4 overflow-hidden w-100" style="max-width: 1000px; background-color: #ffffff;">
    
    <!-- Columna izquierda (advertencia) -->
    <div class="col-md-5 bg-light d-flex flex-column align-items-center justify-content-center p-4 position-relative">

      <!-- Icono en la esquina interna -->
      <div style="position: absolute; top: 20px; left: 20px;">
        <a href="configuraciones.php"><i class="bi bi-arrow-left" style="font-size: 3rem;"></i></a>
      </div>

      <img src="#" alt="Advertencia" style="max-width: 100px;">
      <h5 class="mt-3 text-danger fw-bold">¡Atención!</h5>
      <p class="text-muted text-center">Estás a punto de modificar tus credenciales de acceso. Asegúrate de que los datos sean correctos para evitar perder el acceso.</p>
    </div>

    <!-- Columna derecha (formularios) -->
    <div class="col-md-7 p-5">
      <h3 class="mb-4 fw-bold">Editar credenciales</h3>

      <!-- Cambiar correo -->
      <form action="../../models/configuracion_perfil.php" method="POST" class="mb-4">
        <label class="form-label fw-semibold">Nuevo email</label>
        <input type="email" name="nuevo_correo" class="form-control mb-2" placeholder="ejemplo@email.com" >
        <label class="form-label fw-semibold">Cambiar contraseña</label>
        <input type="password" name="contrasena_actual" class="form-control mb-2" placeholder="Contraseña actual" >
        <input type="password" name="nueva_contrasena" class="form-control mb-2" placeholder="Nueva contraseña" >
        <input type="password" name="confirmar_contrasena" class="form-control mb-3" placeholder="Confirmar nueva contraseña" >
        <label class="form-label fw-semibold">Número de Teléfono</label>
        <input type="tel" name="numero_telefono" class="form-control mb-2" placeholder="3003456611" >
        <button class="btn btn-outline-primary w-100" type="submit">Guardar cambios</button>
      </form>
      <hr>
      <button id="btnUbicacion" class="btn btn-outline-success w-100 mb-2"><i class="bi bi-geo-alt"></i> Actualizar mi ubicación</button>
      <div id="ubicacionMsg" class="mt-2"></div>
      <script>
      document.getElementById('btnUbicacion').addEventListener('click', function() {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(pos) {
            fetch('../../models/guardar_ubicacion_usuario.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ lat: pos.coords.latitude, lng: pos.coords.longitude })
            })
            .then(res => res.text())
            .then(msg => {
              document.getElementById('ubicacionMsg').innerHTML = '<span class="text-success">' + msg + '</span>';
            })
            .catch(() => {
              document.getElementById('ubicacionMsg').innerHTML = '<span class="text-danger">Error al guardar la ubicación</span>';
            });
          }, function() {
            document.getElementById('ubicacionMsg').innerHTML = '<span class="text-danger">No se pudo obtener la ubicación</span>';
          });
        } else {
          document.getElementById('ubicacionMsg').innerHTML = '<span class="text-danger">Geolocalización no soportada</span>';
        }
      });
      </script>
    </div>

  </div>
</div>




<?php
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
    echo "
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: '¡Actualización exitosa!',
            text: " . json_encode($mensaje) . ",
            confirmButtonText: 'Ir al configuraciones',
            allowOutsideClick: false,
        }).then(() => {
            window.location.href = 'configuraciones.php';
        });
    });
    </script>
    ";
}
?>

<!-- Alerta de error con SweetAlert2 -->
<?php
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
    echo "
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: '¡Error!',
            text: " . json_encode($error) . ",
            confirmButtonText: 'Volver'
        });
    });
    </script>
    ";
}
?>







<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">

</script>


    
</body>
</html>

