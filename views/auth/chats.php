<!-- /views/chat/index.php -->
<?php
$chats = [
    ['id' => 1, 'nombre' => 'Luna', 'foto' => '../../public/img/luna.jpg', 'ultimo' => '¿Vamos al parque?'],
    ['id' => 2, 'nombre' => 'Toby', 'foto' => '../../public/img/toby.jpg', 'ultimo' => '¡jaaajj!'],
];

// Chat simulado seleccionado
$chatSeleccionado = $chats[0];
$mensajes = [
    ['tipo' => 'recibido', 'texto' => 'Hola, ¿cómo estás?'],
    ['tipo' => 'enviado', 'texto' => 'Muy bien, ¿y tú?'],
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chats | DogMatch</title>
    <link rel="stylesheet" href="/Dogmatch/public/css/chats.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="main-container">

    <!-- Panel izquierdo: lista de chats -->
    <div class="chats-list">
        <h2>Chats</h2>
        <input type="text" placeholder="Buscar chats..." class="search-chat" />
        <div class="chats-scroll">
            <?php foreach ($chats as $chat): ?>
                <div class="chat-preview <?= $chat['id'] === $chatSeleccionado['id'] ? 'active' : '' ?>">
                    <img src="<?= $chat['foto'] ?>" alt="Foto de <?= $chat['nombre'] ?>" />
                    <div>
                        <strong><?= $chat['nombre'] ?></strong>
                        <p><?= $chat['ultimo'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Panel derecho: conversación activa -->
    <div class="chat-box">
        <div class="chat-header">
            <img src="<?= $chatSeleccionado['foto'] ?>" alt="Perro" />
            <h3><?= $chatSeleccionado['nombre'] ?></h3>
        </div>

        <div class="chat-messages" id="chatMessages">
            <?php foreach ($mensajes as $msg): ?>
                <div class="mensaje <?= $msg['tipo'] ?>">
                    <p><?= htmlspecialchars($msg['texto']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <form class="chat-input" id="chatForm">
            <input type="text" id="mensaje" placeholder="Escribe tu mensaje..." required autocomplete="off">
            <button type="submit">Enviar</button>
        </form>
    </div>

</div>

<script src="">"/Dogmatch/public/js/chats.js"</script>
</body>
</html>
