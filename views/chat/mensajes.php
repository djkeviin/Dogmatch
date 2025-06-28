<?php
session_start();
require_once '../../config/conexion.php';
require_once '../../models/Mensaje.php';
require_once '../../models/Perro.php';
require_once '../../models/Usuario.php';
require_once '../../models/MatchPerro.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($_SESSION['usuario_id']) && isset($_SESSION['usuario']['id'])) {
    $_SESSION['usuario_id'] = $_SESSION['usuario']['id'];
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$perro_id = isset($_GET['perro_id']) ? intval($_GET['perro_id']) : null;
$mi_perro_id = $_SESSION['perro_id'] ?? null;
if ($perro_id && $mi_perro_id && $perro_id != $mi_perro_id) {
    $matchModel = new MatchPerro();
    if (!$matchModel->esMatch($mi_perro_id, $perro_id)) {
        echo '<div class="alert alert-danger m-5">Debes tener un match confirmado para chatear con este usuario.</div>';
        exit;
    }
}

$mensajeModel = new Mensaje();
$perroModel = new Perro();
$usuarioModel = new Usuario();

$perro_activo = null;
$dueno_perro = null;
if ($perro_id) {
    $perro_activo = $perroModel->obtenerPorId($perro_id);

    // Si se intenta abrir un chat con el perro del propio usuario,
    // redirigir al dashboard para evitar confusiones.
    if ($perro_activo && $perro_activo['usuario_id'] == $_SESSION['usuario_id']) {
        header('Location: ../auth/dashboard.php?mensaje=no_puedes_chatear_contigo_mismo');
        exit;
    }

    if ($perro_activo) {
        $dueno_perro = $usuarioModel->obtenerPorId($perro_activo['usuario_id']);
    }
}

$usuarioModel->actualizarActividad($_SESSION['usuario']['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - DogMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../public/css/chat.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/emoji-mart@latest/css/emoji-mart.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../auth/dashboard.php">🐶 DogMatch</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../auth/dashboard.php">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/perfil.php">
                        <i class="bi bi-person"></i> Mi Perfil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/logout.php">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="chat-container" 
     data-usuario-id="<?= $_SESSION['usuario_id'] ?>" 
     data-conversacion-activa-id="<?= $perro_id ?>">
    <div class="chat-sidebar" id="chatSidebar">
        <div class="search-container">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" placeholder="Buscar conversación...">
            </div>
        </div>
        <div class="conversations-list">
            <!-- Las conversaciones se cargarán dinámicamente aquí -->
        </div>
    </div>
    <div class="chat-main" id="chatMain">
        <?php if ($perro_activo && $dueno_perro): ?>
            <div class="chat-header">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link d-lg-none me-2" onclick="toggleSidebar()">
                        <i class="bi bi-list"></i>
                    </button>
                    <img src="../../public/img/<?= htmlspecialchars($perro_activo['foto'] ?? 'default-dog.png') ?>" 
                         alt="<?= htmlspecialchars($perro_activo['nombre']) ?>" 
                         class="chat-header-img">
                    <div class="chat-header-info" data-usuario-id="<?= htmlspecialchars($dueno_perro['id']) ?>">
                        <h5 class="mb-0"><?= htmlspecialchars($perro_activo['nombre']) ?></h5>
                        <small class="estado-usuario">Verificando estado...</small>
                    </div>
                </div>
                <button class="btn btn-link d-lg-none" onclick="toggleInfo()">
                    <i class="bi bi-info-circle"></i>
                </button>
            </div>
            <div class="chat-messages" id="chatMessages">
                <!-- Los mensajes se cargarán dinámicamente aquí -->
            </div>
            <?php if ($perro_activo['usuario_id'] != $_SESSION['usuario_id']): ?>
                <div class="chat-input">
                    <form id="chatForm" class="d-flex align-items-center">
                        <div class="input-actions">
                            <button type="button" class="btn btn-link" onclick="toggleEmojiPicker()">
                                <i class="bi bi-emoji-smile"></i>
                            </button>
                            <button type="button" class="btn btn-link" onclick="document.getElementById('fileInput').click()">
                            <i class="bi bi-image"></i>
                        </button>
                        </div>
                        <input type="text" class="form-control" id="mensajeInput" name="mensaje"
                               placeholder="Escribe un mensaje...">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i>
                        </button>
                    </form>
                    <!-- Input oculto para archivos -->
                    <input type="file" id="fileInput" accept="image/*" style="display: none;" onchange="handleFileSelect(event)">
                    <!-- Contenedor de emoji picker -->
                    <div id="emojiPickerContainer" class="emoji-picker-container" style="display: none;"></div>
                </div>
            <?php else: ?>
                <div class="chat-input text-center p-3 bg-light">
                    <p class="text-muted mb-0">
                        <i class="bi bi-info-circle"></i>
                        Este es tu perro, no puedes enviarte mensajes a ti mismo
                    </p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="chat-empty-state">
                <i class="bi bi-chat-dots"></i>
                <h4>Selecciona una conversación</h4>
                <p>O inicia una nueva desde el perfil de un perro</p>
            </div>
        <?php endif; ?>
    </div>
    <div class="chat-info" id="chatInfo">
        <?php if ($perro_activo): ?>
            <div class="text-center p-3">
                <img src="../../public/img/<?= htmlspecialchars($perro_activo['foto'] ?? 'default-dog.png') ?>" 
                     alt="<?= htmlspecialchars($perro_activo['nombre']) ?>" 
                     class="chat-info-img mb-3">
                <h4><?= htmlspecialchars($perro_activo['nombre']) ?></h4>
                <p class="text-muted">
                    <?php if (!empty($perro_activo['razas'])): ?>
                        <?= htmlspecialchars(implode(', ', array_column($perro_activo['razas'], 'nombre'))) ?> •
                    <?php endif; ?>
                    <?= htmlspecialchars($perro_activo['edad']) ?> meses
                </p>
                <div class="d-grid gap-2">
                    <a href="../auth/perfil.php?id=<?= $perro_activo['id'] ?>" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> Ver Perfil
                    </a>
                    <button class="btn btn-outline-danger" onclick="abrirModalReporte(<?= $perro_activo['id'] ?>, <?= $dueno_perro['id'] ?>)">
                        <i class="bi bi-exclamation-triangle"></i> Reportar
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
<script src="../../public/js/chat.js"></script>

<!-- Modal de Reporte -->
<div class="modal fade" id="modalReporte" tabindex="-1" aria-labelledby="modalReporteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReporteLabel">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                    Reportar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formReporte" method="POST">
                <div class="modal-body">
                    <!-- Información del usuario reportado -->
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <img src="../../public/img/default-dog.png" 
                                 alt="Perro" 
                                 class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <strong>Reportando a:</strong> <span id="nombreReportado">Usuario</span>
                                <br><small class="text-muted">Perro: <span id="nombrePerro">Perro</span></small>
                            </div>
                        </div>
                    </div>

                    <!-- Campos ocultos -->
                    <input type="hidden" name="reportado_id" id="reportado_id">
                    <input type="hidden" name="perro_id" id="perro_id">

                    <!-- Tipo de reporte -->
                    <div class="mb-3">
                        <label for="tipo_reporte" class="form-label">
                            <strong>Tipo de Reporte *</strong>
                        </label>
                        <select class="form-select" id="tipo_reporte" name="tipo_reporte" required>
                            <option value="">Selecciona un tipo de reporte</option>
                            <option value="perfil_falso">Perfil Falso</option>
                            <option value="contenido_inapropiado">Contenido Inapropiado</option>
                            <option value="spam">Spam</option>
                            <option value="acoso">Acoso</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            <strong>Descripción del Problema *</strong>
                        </label>
                        <textarea class="form-control" id="descripcion" name="descripcion" 
                                  rows="4" placeholder="Describe detalladamente el problema..." required></textarea>
                        <div class="form-text">
                            Proporciona detalles específicos para ayudar a nuestros moderadores a entender la situación.
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i>
                        <strong>Importante:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Solo puedes reportar a un usuario una vez cada 24 horas</li>
                            <li>Los reportes falsos pueden resultar en sanciones</li>
                            <li>Nuestros moderadores revisarán tu reporte en las próximas 24-48 horas</li>
                        </ul>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-exclamation-triangle"></i> Enviar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Función para abrir el modal de reporte
function abrirModalReporte(perroId, usuarioId) {
    // Obtener información del perro y usuario
    fetch(`../../controllers/chat/obtener_info_perro.php?id=${perroId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('nombrePerro').textContent = data.perro.nombre;
                document.getElementById('nombreReportado').textContent = data.perro.nombre_dueno || 'Usuario';
                document.getElementById('perro_id').value = perroId;
                document.getElementById('reportado_id').value = usuarioId;
                
                // Mostrar el modal
                const modal = new bootstrap.Modal(document.getElementById('modalReporte'));
                modal.show();
            } else {
                Swal.fire('Error', 'No se pudo obtener la información del perro', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al cargar la información', 'error');
        });
}

// Manejar el envío del formulario de reporte
document.addEventListener('DOMContentLoaded', function() {
    const formReporte = document.getElementById('formReporte');
    
    if (formReporte) {
        formReporte.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Deshabilitar botón y mostrar loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviando...';
            
            try {
                const response = await fetch('../../controllers/reportes/crear_reporte.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: 'Reporte Enviado',
                        text: 'Gracias por ayudarnos a mantener la comunidad segura.',
                        confirmButtonText: 'Aceptar'
                    });
                    
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalReporte'));
                    modal.hide();
                    
                    // Limpiar formulario
                    this.reset();
                    
                } else {
                    throw new Error(result.error || 'Error al enviar el reporte');
                }
                
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al enviar el reporte: ' + error.message
                });
            } finally {
                // Restaurar botón
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
});

// Funciones para el chat responsive
function toggleSidebar() {
    document.getElementById('chatSidebar').classList.toggle('active');
}

function toggleInfo() {
    document.getElementById('chatInfo').classList.toggle('active');
}

// Variables globales para emoji picker e imagen
let emojiPicker = null;
let selectedImage = null;

// Función para limpiar mensajes temporales (disponible globalmente)
function limpiarMensajeTemporal(tempId, realId) {
    const tempElement = document.querySelector(`.mensaje[data-mensaje-id='${tempId}']`);
    if (tempElement) {
        tempElement.dataset.mensajeId = realId;
        // Actualizar la URL de la imagen si es necesario
        const img = tempElement.querySelector('img');
        if (img && img.src.startsWith('blob:')) {
            // La imagen temporal se reemplazará con la real en la próxima actualización
            img.style.opacity = '0.7';
        }
    }
}

// Función para alternar el emoji picker
function toggleEmojiPicker() {
    const container = document.getElementById('emojiPickerContainer');
    const isVisible = container.style.display !== 'none';
    
    if (isVisible) {
        container.style.display = 'none';
    } else {
        // Ocultar preview de imagen si está visible
        const imagePreview = document.querySelector('.image-preview');
        if (imagePreview) {
            imagePreview.remove();
        }
        
        container.style.display = 'block';
        if (!emojiPicker) {
            emojiPicker = new EmojiMart.Picker({
                onEmojiSelect: (emoji) => {
                    const input = document.getElementById('mensajeInput');
                    const cursorPos = input.selectionStart;
                    const textBefore = input.value.substring(0, cursorPos);
                    const textAfter = input.value.substring(cursorPos);
                    input.value = textBefore + emoji.native + textAfter;
                    input.focus();
                    input.setSelectionRange(cursorPos + emoji.native.length, cursorPos + emoji.native.length);
                    container.style.display = 'none';
                },
                theme: 'light',
                set: 'native',
                style: {
                    width: '320px',
                    height: '400px'
                }
            });
            container.appendChild(emojiPicker);
        }
    }
}

// Función para manejar la selección de archivos
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Validar tipo de archivo
    if (!file.type.startsWith('image/')) {
        Swal.fire('Error', 'Solo se permiten archivos de imagen', 'error');
        return;
    }
    
    // Validar tamaño (máximo 5MB)
    if (file.size > 5 * 1024 * 1024) {
        Swal.fire('Error', 'La imagen no puede ser mayor a 5MB', 'error');
        return;
    }
    
    selectedImage = file;
    showImagePreview(file);
}

// Función para mostrar preview de imagen
function showImagePreview(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const chatInput = document.querySelector('.chat-input');
        
        // Ocultar emoji picker si está visible
        const emojiContainer = document.getElementById('emojiPickerContainer');
        if (emojiContainer && emojiContainer.style.display !== 'none') {
            emojiContainer.style.display = 'none';
        }
        
        // Remover preview anterior si existe
        const existingPreview = document.querySelector('.image-preview');
        if (existingPreview) {
            existingPreview.remove();
        }
        
        const preview = document.createElement('div');
        preview.className = 'image-preview';
        preview.innerHTML = `
            <div class="d-flex align-items-center justify-content-between mb-2">
                <strong>Vista previa de imagen</strong>
                <button type="button" class="btn-close" onclick="cancelarImagen()"></button>
            </div>
            <img src="${e.target.result}" alt="Preview" class="img-fluid rounded">
            <div class="preview-actions mt-3">
                <button type="button" class="btn btn-sm btn-primary" onclick="enviarImagen()">
                    <i class="bi bi-send"></i> Enviar
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="cancelarImagen()">
                    <i class="bi bi-x"></i> Cancelar
                </button>
            </div>
        `;
        
        chatInput.appendChild(preview);
    };
    reader.readAsDataURL(file);
}

// Función para enviar imagen
async function enviarImagen() {
    if (!selectedImage || !chatState.conversacionActivaId) return;
    
    // Evitar envío doble
    if (chatState.enviandoMensaje) {
        return;
    }
    
    chatState.enviandoMensaje = true;
    
    const formData = new FormData();
    formData.append('imagen', selectedImage);
    formData.append('perro_destinatario_id', chatState.conversacionActivaId);
    
    try {
        // Renderizar imagen temporalmente
        const tempId = `temp_img_${Date.now()}`;
        const reader = new FileReader();
        reader.onload = function(e) {
            renderizarMensajes([{
                id: tempId,
                multimedia_url: URL.createObjectURL(selectedImage),
                fecha_envio: new Date().toISOString(),
                es_emisor: true
            }]);
        };
        reader.readAsDataURL(selectedImage);
        
        const response = await fetch('../../controllers/chat/enviar_imagen.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Limpiar preview y archivo seleccionado
            cancelarImagen();
            
            // Reemplazar mensaje temporal con el real
            limpiarMensajeTemporal(tempId, result.nuevo_id);
            
            Swal.fire({
                icon: 'success',
                title: 'Imagen enviada',
                text: 'La imagen se envió correctamente',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error('Error enviando imagen:', error);
        Swal.fire('Error', 'No se pudo enviar la imagen: ' + error.message, 'error');
    } finally {
        chatState.enviandoMensaje = false;
    }
}

// Función para cancelar imagen
function cancelarImagen() {
    selectedImage = null;
    const preview = document.querySelector('.image-preview');
    if (preview) {
        preview.remove();
    }
    document.getElementById('fileInput').value = '';
}

// Función para ver imagen completa
function verImagenCompleta(src) {
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.innerHTML = `
        <img src="${src}" alt="Imagen completa">
        <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    document.body.appendChild(modal);
}

// Cerrar emoji picker al hacer clic fuera
document.addEventListener('click', function(e) {
    const emojiContainer = document.getElementById('emojiPickerContainer');
    const emojiButton = document.querySelector('[onclick="toggleEmojiPicker()"]');
    const imagePreview = document.querySelector('.image-preview');
    const imageButton = document.querySelector('[onclick="document.getElementById(\'fileInput\').click()"]');
    
    // Cerrar emoji picker
    if (emojiContainer && emojiContainer.style.display !== 'none') {
        if (!emojiContainer.contains(e.target) && !emojiButton.contains(e.target)) {
            emojiContainer.style.display = 'none';
        }
    }
    
    // Cerrar image preview
    if (imagePreview) {
        if (!imagePreview.contains(e.target) && !imageButton.contains(e.target)) {
            cancelarImagen();
        }
    }
});

// Cerrar al presionar Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const emojiContainer = document.getElementById('emojiPickerContainer');
        const imagePreview = document.querySelector('.image-preview');
        
        if (emojiContainer && emojiContainer.style.display !== 'none') {
            emojiContainer.style.display = 'none';
        }
        
        if (imagePreview) {
            cancelarImagen();
        }
    }
    });
</script>

</body>
</html> 
