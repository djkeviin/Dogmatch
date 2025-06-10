<!-- Modal del Chat -->
<div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chatModalLabel">Chat con <span id="chatPerroNombre"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Mensajes -->
                <div id="chatMensajes" class="chat-messages p-3" style="height: 400px; overflow-y: auto;">
                    <!-- Los mensajes se cargarán dinámicamente aquí -->
                </div>
                
                <!-- Formulario de envío -->
                <form id="chatForm" class="p-3 border-top">
                    <div class="input-group">
                        <input type="text" id="mensajeInput" class="form-control" placeholder="Escribe un mensaje...">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.chat-messages {
    display: flex;
    flex-direction: column;
}

.mensaje {
    max-width: 80%;
    margin-bottom: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 1rem;
    position: relative;
}

.mensaje.enviado {
    align-self: flex-end;
    background-color: #007bff;
    color: white;
}

.mensaje.recibido {
    align-self: flex-start;
    background-color: #e9ecef;
    color: black;
}

.mensaje .tiempo {
    font-size: 0.75rem;
    opacity: 0.7;
    margin-top: 0.25rem;
}

.mensaje.enviado .tiempo {
    text-align: right;
}

.mensaje-usuario {
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
    font-weight: bold;
}
</style> 