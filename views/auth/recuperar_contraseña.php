<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar contraseña</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap + Iconos -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/recuperar_contraseña.css">
</head>
<body>

<div class="contenedor-central">

  <!-- Contenedor izquierdo -->
  <div class="contenedor-izquierdo">
    <div class="boton">
      <a href="index.php">
        <i class="bi bi-arrow-left" style="font-size: 3rem;"></i>
      </a>
    </div>
    <img src="../../public/img/pata.png" alt="Huella de perro">
  </div>

  <!-- Contenedor derecho -->
  <div class="contenedor-derecho">
    <h1>Recuperar contraseña</h1>
    <p>Por favor, ingresa tu correo electrónico para recibir instrucciones sobre cómo recuperar tu contraseña.</p>
    <input type="email" name="recuperar_contraseña" placeholder="Ingresa tu correo electrónico" required>
    <div class="d-grid gap-2">
      <button class="btn btn-primary w-100" type="submit">Enviar</button>
    </div>
    <div class="reglas d-flex justify-content-center gap-4 mt-4">
      <p><a href="#">Ayuda</a></p>
      <p><a href="#">Términos y condiciones</a></p>
    </div>
  </div>

</div>

</body>
</html>

