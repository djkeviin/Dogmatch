<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dog Match</title>
  <link rel="stylesheet" href="../../public/css/login.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <div class="body-paws"></div>

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
    <div class="login-logo">
      <img src="../../public/img/logo dg.jpg" alt="DogMatch Logo" id="loginLogo" />
    </div>
    <div class="mascota-animada">
      <img src="../../public/img/dog_wave.png" alt="Perrito saludando" id="dogMascot" style="height:60px; width:auto;"/>
    </div>
    <div class="login-bienvenida">¡Bienvenido A DogMatch!</div>

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
      <div class="password-container">
        <input type="password" name="password" placeholder="Ingresar contraseña..." required id="passwordInput">
        <span class="toggle-password" id="togglePassword">&#128065;</span>
      </div>

      <div class="ub1">
       <a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a>
      </div>

      <div class="botones">
        <button type="submit" name="iniciar_sesion">Entrar</button>
        <!--<input type="reset" value="Cancelar">-->
      </div>

      <div class="ref1">
        <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">

</script>
<script src="../../public/js/login.js"></script>
<script>
// Animación de rebote al cargar el logo
window.addEventListener('DOMContentLoaded', function() {
  const logo = document.getElementById('loginLogo');
  logo.classList.add('logo-bounce');
  setTimeout(() => logo.classList.remove('logo-bounce'), 1200);
});
// Giro al hacer hover
const logo = document.getElementById('loginLogo');
logo.addEventListener('mouseenter', () => logo.classList.add('logo-spin'));
logo.addEventListener('mouseleave', () => logo.classList.remove('logo-spin'));
// Mascota saluda al enfocar contraseña
const dog = document.getElementById('dogMascot');
const passInput = document.getElementById('passwordInput');
passInput.addEventListener('focus', () => dog.classList.add('dog-wave'));
passInput.addEventListener('blur', () => dog.classList.remove('dog-wave'));
</script>

</body>
</html>


