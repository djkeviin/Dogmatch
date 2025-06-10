// Variables globales
let perroActualId = null;
let ultimoMensajeId = 0;
let actualizacionInterval = null;
let ultimaActualizacion = 0;
let intervaloMensajes = null;
let intervaloEstados = null;
let mensajesIds = new Set(); // Para rastrear mensajes ya mostrados
let cargandoMensajes = false;

// Inicialización cuando el documento está listo
document.addEventListener('DOMContentLoaded', function() {
    // Obtener perro_id de la URL si existe
    const urlParams = new URLSearchParams(window.location.search);
    perroActualId = urlParams.get('perro_id');

    console.log('perroActualId al iniciar:', perroActualId);

    // Inicializar eventos
    inicializarEventos();
    
    // Cargar conversaciones
    cargarConversaciones();
    
    // Si hay un perro seleccionado, cargar sus mensajes
    if (perroActualId) {
        cargarMensajes();
        // Actualizar mensajes cada 5 segundos
        actualizacionInterval = setInterval(cargarMensajes, 5000);
    }

    // Actualizar estado cada minuto
    actualizarMiEstado();
    setInterval(actualizarMiEstado, 60000);

    // Actualizar estados en línea cada 10 segundos
    actualizarEstadosEnLinea();
    intervaloEstados = setInterval(actualizarEstadosEnLinea, 10000);

    // Cargar mensajes nuevos cada 3 segundos
    intervaloMensajes = setInterval(cargarMensajesNuevos, 3000);
});

// Inicializar eventos
function inicializarEventos() {
    console.log('Inicializando eventos...');
    
    // Formulario de chat
    const chatForm = document.getElementById('chatForm');
    const mensajeInput = document.getElementById('mensajeInput');
    
    if (chatForm && mensajeInput) {
        console.log('Formulario de chat y input encontrados');
        
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const mensaje = mensajeInput.value.trim();
            console.log('Intento de envío de mensaje:', mensaje);
            
            if (!mensaje) {
                console.log('Mensaje vacío, no se envía');
                return;
            }
            
            const enviado = await enviarMensaje(mensaje);
            if (enviado) {
                console.log('Mensaje enviado correctamente, limpiando input');
                mensajeInput.value = '';
                // Esperar un momento antes de cargar los mensajes
                setTimeout(cargarMensajes, 500);
            }
        });
    } else {
        console.error('No se encontró el formulario de chat o el input', {
            formFound: !!chatForm,
            inputFound: !!mensajeInput
        });
    }

    // Búsqueda de conversaciones
    const searchInput = document.querySelector('.search-container input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filtrarConversaciones(this.value);
        });
    }
}

// Función para cargar conversaciones
async function cargarConversaciones() {
    try {
        const response = await fetch('../../controllers/chat/obtener_conversaciones.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }

        const listaConversaciones = document.querySelector('.conversations-list');
        listaConversaciones.innerHTML = '';
        
        if (data.length === 0) {
            listaConversaciones.innerHTML = `
                <div class="text-center p-3 text-muted">
                    <p>No hay conversaciones aún</p>
                </div>
            `;
            return;
        }
        
        data.forEach(conv => {
            const conversacionHtml = `
                <div class="conversation-item" data-conversacion-id="${conv.id}" data-usuario-id="${conv.usuario_id}">
                    <img src="../../public/img/${conv.foto || 'default-dog.png'}" alt="${conv.nombre}">
                    <div class="conversation-info">
                        <h6>${conv.nombre}</h6>
                        <p class="ultimo-mensaje">${conv.ultimo_mensaje || 'No hay mensajes'}</p>
                    </div>
                    <div class="estado-usuario">Verificando...</div>
                </div>
            `;
            listaConversaciones.innerHTML += conversacionHtml;
        });
        
        // Actualizar estados después de cargar conversaciones
        actualizarEstadosEnLinea();
        
        // Agregar eventos click a las conversaciones
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', () => {
                window.location.href = `mensajes.php?perro_id=${item.dataset.conversacionId}`;
            });
        });
    } catch (error) {
        console.error('Error al cargar conversaciones:', error);
        const listaConversaciones = document.querySelector('.conversations-list');
        listaConversaciones.innerHTML = `
            <div class="alert alert-danger m-3" role="alert">
                Error al cargar las conversaciones. Por favor, recarga la página.
            </div>
        `;
    }
}

// Función para filtrar conversaciones
function filtrarConversaciones(busqueda) {
    const items = document.querySelectorAll('.conversation-item');
    busqueda = busqueda.toLowerCase();

    items.forEach(item => {
        const nombre = item.querySelector('h6').textContent.toLowerCase();
        if (nombre.includes(busqueda)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

// Función para seleccionar una conversación
function seleccionarConversacion(perroId) {
    // Actualizar URL sin recargar la página
    const newUrl = `${window.location.pathname}?perro_id=${perroId}`;
    window.history.pushState({ perroId }, '', newUrl);
    
    // Recargar la página para mostrar la nueva conversación
    window.location.reload();
}

// Función para cargar mensajes
async function cargarMensajes() {
    if (!perroActualId || cargandoMensajes) return;
    
    cargandoMensajes = true;
    
    try {
        const response = await fetch(`../../controllers/chat/obtener_mensajes.php?perro_id=${perroActualId}&ultima_actualizacion=${ultimaActualizacion}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();

        if (data.error) {
            throw new Error(data.error);
        }

        if (data.mensajes && data.mensajes.length > 0) {
            const chatContainer = document.querySelector('.chat-messages');
            let seAgregaron = false;
            
            data.mensajes.forEach(mensaje => {
                // Verificar si el mensaje ya existe usando el Set
                if (!mensajesIds.has(mensaje.id)) {
                    mensajesIds.add(mensaje.id);
                    
                    // Preparar el mensaje para mostrarlo
                    const mensajeProcesado = {
                        id: mensaje.id,
                        mensaje: mensaje.mensaje,
                        fecha_envio: mensaje.fecha_envio,
                        es_emisor: mensaje.emisor_id == userId,
                        emisor_nombre: mensaje.emisor_nombre
                    };

                    const mensajeHtml = crearElementoMensaje(mensajeProcesado);
                    chatContainer.appendChild(mensajeHtml);
                    seAgregaron = true;
                }
            });
            
            // Solo hacer scroll si se agregaron nuevos mensajes
            if (seAgregaron) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
            
            ultimaActualizacion = data.ultima_actualizacion;
        }
    } catch (error) {
        console.error('Error al cargar mensajes:', error);
    } finally {
        cargandoMensajes = false;
    }
}

// Función para crear elemento de mensaje
function crearElementoMensaje(mensaje) {
    const div = document.createElement('div');
    div.className = `mensaje ${mensaje.es_emisor ? 'mensaje-enviado' : 'mensaje-recibido'}`;
    div.setAttribute('data-mensaje-id', mensaje.id);

    // Formatear la fecha
    let fechaFormateada;
    if (mensaje.fecha_envio) {
        try {
            const fecha = new Date(mensaje.fecha_envio);
            if (!isNaN(fecha)) {
                fechaFormateada = fecha.toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } else {
                fechaFormateada = 'Ahora';
            }
        } catch (error) {
            console.error('Error al formatear la fecha:', error);
            fechaFormateada = 'Ahora';
        }
    } else {
        fechaFormateada = 'Ahora';
    }

    // Crear el contenido del mensaje
    div.innerHTML = `
        <div class="mensaje-contenido">
            ${!mensaje.es_emisor ? `<small class="mensaje-emisor">${mensaje.emisor_nombre || 'Usuario'}</small>` : ''}
            <p>${mensaje.mensaje}</p>
            <small class="mensaje-hora">${fechaFormateada}</small>
        </div>
    `;

    return div;
}

// Función para enviar mensaje
async function enviarMensaje(mensaje) {
    console.log('Función enviarMensaje llamada con:', { mensaje, perroActualId });

    // Validación de datos
    if (!perroActualId) {
        console.error('No hay perro seleccionado para enviar el mensaje');
        mostrarAlerta('Error: No hay perro seleccionado', 'error');
        return false;
    }

    if (!mensaje || typeof mensaje !== 'string' || mensaje.trim() === '') {
        console.error('Mensaje inválido:', mensaje);
        mostrarAlerta('Error: El mensaje no puede estar vacío', 'error');
        return false;
    }

    try {
        const datosEnvio = {
            perro_id: perroActualId,
            mensaje: mensaje.trim()
        };
        
        console.log('Enviando datos al servidor:', datosEnvio);

        const response = await fetch('../../controllers/chat/enviar_mensaje.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datosEnvio)
        });

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const data = await response.json();
        console.log('Respuesta del servidor:', data);

        if (data.error) {
            console.error('Error del servidor:', data.error);
            mostrarAlerta(`Error: ${data.error}`, 'error');
            return false;
        }

        if (data.success) {
            console.log('Mensaje enviado exitosamente con ID:', data.mensaje_id);
            
            // Agregar el ID del mensaje al Set de mensajes mostrados
            mensajesIds.add(data.mensaje_id);
            
            // Actualizar la UI inmediatamente
            const chatContainer = document.querySelector('.chat-messages');
            if (chatContainer) {
                const mensajeHtml = crearElementoMensaje({
                    id: data.mensaje_id,
                    mensaje: mensaje,
                    es_emisor: true,
                    emisor_nombre: userName,
                    fecha_envio: new Date().toISOString()
                });
                chatContainer.appendChild(mensajeHtml);
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
            return true;
        }

        return false;
    } catch (error) {
        console.error('Error al enviar mensaje:', error);
        mostrarAlerta(`Error al enviar el mensaje: ${error.message}`, 'error');
        return false;
    }
}

// Funciones de UI móvil
function toggleSidebar() {
    document.getElementById('chatSidebar').classList.toggle('show');
}

function toggleInfo() {
    document.getElementById('chatInfo').classList.toggle('show');
}

// Función para mostrar alertas
function mostrarAlerta(mensaje, tipo) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.querySelector('.chat-messages').appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Limpiar intervalo cuando se cierra la página
window.addEventListener('beforeunload', function() {
    if (actualizacionInterval) {
        clearInterval(actualizacionInterval);
    }
});

// Función para actualizar el estado en línea del usuario actual
function actualizarMiEstado() {
    fetch('../../controllers/chat/actualizar_estado.php', {
        method: 'POST'
    })
    .catch(error => console.error('Error al actualizar estado:', error));
}

// Función para obtener y actualizar estados en línea
function actualizarEstadosEnLinea() {
    const usuarios = document.querySelectorAll('[data-usuario-id]');
    const usuarioIds = Array.from(usuarios).map(el => el.dataset.usuarioId);
    
    if (usuarioIds.length === 0) return;

    fetch('../../controllers/chat/obtener_estados.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ usuarios: usuarioIds })
    })
    .then(response => response.json())
    .then(estados => {
        if (estados.error) {
            throw new Error(estados.error);
        }
        usuarios.forEach(usuario => {
            const id = usuario.dataset.usuarioId;
            const estadoElement = usuario.querySelector('.estado-usuario');
            if (estadoElement) {
                estadoElement.classList.toggle('en-linea', estados[id] === true);
                estadoElement.textContent = estados[id] ? 'En línea' : 'Desconectado';
            }
        });
    })
    .catch(error => console.error('Error al obtener estados:', error));
}

// Función para cargar mensajes nuevos
function cargarMensajesNuevos() {
    const chatActivo = document.querySelector('.chat-activo');
    if (!chatActivo) return;

    const conversacionId = chatActivo.dataset.conversacionId;
    
    fetch(`../../controllers/chat/obtener_mensajes.php?conversacion_id=${conversacionId}&ultima_actualizacion=${ultimaActualizacion}`)
        .then(response => response.json())
        .then(data => {
            if (data.mensajes && data.mensajes.length > 0) {
                const chatContainer = document.querySelector('.mensajes-container');
                data.mensajes.forEach(mensaje => {
                    const mensajeHtml = crearElementoMensaje(mensaje);
                    chatContainer.appendChild(mensajeHtml);
                });
                chatContainer.scrollTop = chatContainer.scrollHeight;
                ultimaActualizacion = data.ultima_actualizacion;
            }
        });
} 