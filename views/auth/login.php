
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dog Match</title>
  <link rel="stylesheet" href="../../public/css/login.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>
<body>


<!--contenedor principal-->
<div class="contenedor-principal">

  <!-- Lado derecho: Login -->
  <div class="contenedor-derecho">



 <?php if (isset($_GET['error'])): ?>
      <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

  <form action="/public/index.php?action=login" method="POST">
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

<!--script de bootstrap-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">

</script>

<!--script de chebok de mostrar contraseña-->
<script src="j.s/script.js">

</script>


<!--script del modal de aviso-->
<script src="j.s/script_modal.js">

</script>

</body>
</html>

