<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dog Match</title>
  <link rel="stylesheet" href="../../public/css/login.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

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
        });
    });
    </script>
    ";
}
?>

<!--contenedor principal-->
<div class="contenedor-principal">
  <!-- Lado derecho: Login -->
  <div class="contenedor-derecho">

    <?php if (isset($_GET['error'])): ?>
      <p id="loginError" style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php else: ?>
      <p id="loginError" style="color: red; display: none;"></p>
    <?php endif; ?>

    <form id="loginForm" action="/public/index.php?action=login" method="POST">
      <div class="formtlo">Iniciar sesión</div>

      <div class="ub1">&#128273; Ingrese correo</div>
      <input type="email" name="email" placeholder="Ingresar correo" required>

      <div class="ub1">&#128274; Ingresar Contraseña</div>
      <input type="password" name="password" placeholder="Ingresar contraseña..." required>

      <div class="ub1">
        <input type="checkbox" onclick="verpassword()"> Mostrar contraseña
      </div>

      <div class="botones">
        <button type="submit" name="iniciar_sesion">Entrar</button>
        <input type="reset" value="Cancelar">
      </div>

      <div class="ref1">
        <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../public/js/login.js"></script>

</body>
</html>

