/**
 * Sistema de Chat para DogMatch
 * Refactorizado para mayor eficiencia y claridad.
 */

// Estado global del chat
const chatState = {
    usuarioId: null,
    conversacionActivaId: null,
    ultimoTimestamp: 0,
    intervaloActualizacion: null,
    cargandoMensajes: false,
    mensajesMostrados: new Set(),
    escribiendoTimeout: null,
    ultimaActividadEscritura: 0,
    enviandoMensaje: false
};

document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.querySelector('.chat-container');
    if (!chatContainer) return;

    chatState.usuarioId = parseInt(chatContainer.dataset.usuarioId, 10);
    const perroIdInicial = chatContainer.dataset.conversacionActivaId;
    
    cargarConversaciones();
    if (perroIdInicial) {
        seleccionarConversacion(perroIdInicial, true);
    }
    
    iniciarMotorDeActualizacion();
    configurarEventosUI();
    configurarBusqueda();
});

function iniciarMotorDeActualizacion() {
    if (chatState.intervaloActualizacion) {
        clearInterval(chatState.intervaloActualizacion);
    }
    chatState.intervaloActualizacion = setInterval(actualizarChat, 2000);
}

function configurarEventosUI() {
    const chatForm = document.getElementById('chatForm');
    if (chatForm) {
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (chatState.enviandoMensaje) return;
            
            const mensajeInput = document.getElementById('mensajeInput');
            const mensaje = mensajeInput.value.trim();
            if (mensaje && chatState.conversacionActivaId) {
                mensajeInput.value = '';
                await enviarMensaje(mensaje);
            }
        });
    }

    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
    }

    const mensajeInput = document.getElementById('mensajeInput');
    if (mensajeInput) {
        mensajeInput.addEventListener('input', function() {
            if (chatState.conversacionActivaId) {
                enviarEstadoEscritura(true);
                clearTimeout(chatState.escribiendoTimeout);
                chatState.escribiendoTimeout = setTimeout(() => enviarEstadoEscritura(false), 3000);
            }
        });
    }
}

/**
 * MANEJO DE IMÁGENES
 */
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file || !chatState.conversacionActivaId) return;

    if (!file.type.startsWith('image/')) {
        Swal.fire('Error', 'Solo puedes enviar archivos de imagen.', 'error');
        return;
    }
    
    // Ya no se renderiza una vista previa. Solo se envía la imagen.
    // La actualización automática se encargará de mostrarla.
    enviarImagen(file);
    
    event.target.value = null; // Reset input
}

async function enviarImagen(file) {
    const formData = new FormData();
    formData.append('imagen', file);
    formData.append('perro_destinatario_id', chatState.conversacionActivaId);
    // Ya no necesitamos un ID temporal en el frontend.
    // El backend puede seguir usándolo si quiere, pero no es crucial para la UI.

    try {
        const response = await fetch('../../controllers/chat/enviar_imagen.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (!data.success) {
            Swal.fire('Error', data.error || 'No se pudo enviar la imagen.', 'error');
        }
        // En caso de éxito, no hacemos nada. La actualización automática la mostrará.
    } catch (error) {
        Swal.fire('Error de Conexión', 'No se pudo subir la imagen.', 'error');
    }
}

/**
 * LÓGICA DE MENSAJERÍA Y RENDERIZADO
 */
async function actualizarChat() {
    if (!chatState.usuarioId) return;

    try {
        const url = `../../controllers/chat/actualizar_chat.php?conversacion_activa_id=${chatState.conversacionActivaId || ''}&ultimo_timestamp=${chatState.ultimoTimestamp}`;
        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
            if (data.conversaciones) actualizarListaConversaciones(data.conversaciones);
            if (data.mensajes) renderizarMensajes(data.mensajes);
            chatState.ultimoTimestamp = data.timestamp;
        }
    } catch (error) {
        // console.error('Fallo la actualización del chat:', error);
    }
}

function renderizarMensajes(mensajes) {
    const chatContainer = document.getElementById('chatMessages');
    if (!chatContainer) return;
    
    const debeScroll = (chatContainer.scrollTop + chatContainer.clientHeight) >= chatContainer.scrollHeight - 100;

    mensajes.forEach(msg => {
        // Lógica de reemplazo de mensajes temporales eliminada.
        if (chatState.mensajesMostrados.has(msg.id)) return;

        const msgElement = crearElementoMensaje(msg);
        chatContainer.appendChild(msgElement);
        chatState.mensajesMostrados.add(msg.id);
    });

    if (debeScroll) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
}


function crearElementoMensaje(msg) {
    const msgElement = document.createElement('div');
    msgElement.className = `mensaje ${msg.es_emisor ? 'mensaje-enviado' : 'mensaje-recibido'}`;
    if(msg.id) msgElement.dataset.mensajeId = msg.id;
    
    const fecha = new Date(msg.fecha_envio);
    const horaFormateada = fecha.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });

    let estadoIcono = '';
    if (msg.es_emisor) {
        if (msg.leido) {
            estadoIcono = '<i class="bi bi-check2-all text-primary"></i>';
        } else {
            estadoIcono = '<i class="bi bi-check2-all text-muted"></i>';
        }
    }

    let imagePath;
    if (msg.multimedia_url) {
        if (msg.multimedia_url.startsWith('blob:')) {
            // Es una URL de vista previa local, se usa directamente.
            imagePath = msg.multimedia_url;
        } else if (msg.multimedia_url.startsWith('chatimg_')) {
            // Es una imagen nueva de la carpeta de chat.
            imagePath = `../../public/img/chat/${msg.multimedia_url}`;
        } else {
            // Es una imagen antigua de la carpeta principal.
            imagePath = `../../public/img/${msg.multimedia_url}`;
        }
    }

    const contenido = msg.multimedia_url 
        ? `<img src="${imagePath}" alt="Imagen" class="chat-image" onclick="verImagenCompleta('${imagePath}')">`
        : `<p class="mensaje-texto">${htmlspecialchars(msg.mensaje)}</p>`;

    msgElement.innerHTML = `
        <div class="mensaje-contenido ${msg.multimedia_url ? 'mensaje-imagen' : ''}">
            ${contenido}
            <div class="mensaje-footer">
                <span class="mensaje-hora">${horaFormateada}</span>
                ${estadoIcono}
            </div>
        </div>`;
    return msgElement;
}

async function enviarMensaje(mensaje) {
    chatState.enviandoMensaje = true;
    try {
        await fetch('../../controllers/chat/enviar_mensaje.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                perro_destinatario_id: chatState.conversacionActivaId,
                mensaje: mensaje
            })
        });
    } catch (error) {
        Swal.fire('Error', 'No se pudo enviar el mensaje.', 'error');
    } finally {
        chatState.enviandoMensaje = false;
    }
}

/**
 * FUNCIONES AUXILIARES Y DE UI
 */
async function cargarConversaciones() {
    try {
        const response = await fetch('../../controllers/chat/obtener_conversaciones.php');
        const conversaciones = await response.json();
        actualizarListaConversaciones(conversaciones);
    } catch (error) {
        // console.error('Error cargando conversaciones:', error);
    }
}

function actualizarListaConversaciones(conversaciones) {
    const listaContainer = document.querySelector('.conversations-list');
    if (!listaContainer) return;

    conversaciones.forEach(conv => {
        let convElement = document.querySelector(`.conversation-item[data-perro-id='${conv.perro_id}']`);
        const estadoOnline = conv.online ? 'online' : 'offline';
        const ultimoMensaje = conv.es_multimedia ? '<i class="bi bi-camera-fill"></i> Imagen' : htmlspecialchars(conv.ultimo_mensaje);

        if (!convElement) {
            convElement = document.createElement('div');
            convElement.className = 'conversation-item';
            convElement.dataset.perroId = conv.perro_id;
            convElement.addEventListener('click', () => seleccionarConversacion(conv.perro_id));
        }
        
        convElement.innerHTML = `
            <div class="profile-pic-container ${estadoOnline}">
                <img src="../../public/img/${conv.foto || 'default-dog.png'}" alt="${conv.nombre}">
            </div>
            <div class="conversation-info">
                <h6>${conv.nombre}</h6>
                <p class="ultimo-mensaje">${ultimoMensaje || ''}</p>
            </div>
            ${conv.no_leidos > 0 ? `<span class="badge bg-danger rounded-pill unread-count">${conv.no_leidos}</span>` : ''}`;
        
        if (listaContainer.firstChild !== convElement) {
            listaContainer.prepend(convElement);
        }
    });
}

function seleccionarConversacion(perroId, esCargaInicial = false) {
    if (!perroId) return;
    
    chatState.conversacionActivaId = perroId;

    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.toggle('active', item.dataset.perroId == perroId);
    });
    
    const chatContainer = document.getElementById('chatMessages');
    chatContainer.innerHTML = '<div class="text-center p-3 text-muted">Cargando...</div>';
    chatState.mensajesMostrados.clear();
    
    actualizarInfoChat(perroId);
    cargarMensajesHistoricos(perroId);

    if (!esCargaInicial) {
        const url = new URL(window.location);
        url.searchParams.set('perro_id', perroId);
        window.history.pushState({ perroId }, '', url);
    }
}

async function cargarMensajesHistoricos(perroId) {
    try {
        const response = await fetch(`../../controllers/chat/obtener_mensajes.php?perro_id=${perroId}`);
        const data = await response.json();
        
        const chatContainer = document.getElementById('chatMessages');
        chatContainer.innerHTML = '';
        
        if (data.success && data.mensajes.length > 0) {
            renderizarMensajes(data.mensajes);
        } else {
            chatContainer.innerHTML = '<div class="text-center p-3 text-muted">Inicia la conversación.</div>';
        }
    } catch (error) {
        // console.error('Error al cargar mensajes:', error);
    }
}

async function actualizarInfoChat(perroId) {
    // Implementación para actualizar el header del chat
}

function htmlspecialchars(str) {
    if (typeof str !== 'string') return '';
    return str.replace(/[&<>"']/g, match => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[match]);
}

function configurarBusqueda() {
    // Implementación de la búsqueda
}

async function enviarEstadoEscritura(escribiendo) {
    if (!chatState.conversacionActivaId) return;
    try {
        await fetch('../../controllers/chat/estado_escritura.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                perro_id: chatState.conversacionActivaId,
                escribiendo: escribiendo
            })
        });
    } catch (error) {
        // console.error('Error enviando estado de escritura:', error);
    }
}

function verImagenCompleta(url) {
    Swal.fire({
        imageUrl: url,
        imageAlt: 'Imagen ampliada',
        showCloseButton: true,
        showConfirmButton: false,
        backdrop: `rgba(0,0,0,0.8)`
    });
}