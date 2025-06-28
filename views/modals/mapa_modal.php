<?php
// Asegurarse de que hay una sesión activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Modal del Mapa -->
<div class="modal fade" id="modalMapa" tabindex="-1" aria-labelledby="modalMapaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMapaLabel">
                    <i class="bi bi-geo-alt-fill"></i> Perros cercanos a tu ubicación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Filtros de distancia -->
                <div class="mb-3">
                    <label for="filtroDistancia" class="form-label">Mostrar perros en un radio de:</label>
                    <select class="form-select" id="filtroDistancia">
                        <option value="1">1 km</option>
                        <option value="5" selected>5 km</option>
                        <option value="10">10 km</option>
                        <option value="20">20 km</option>
                        <option value="50">50 km</option>
                    </select>
                </div>

                <!-- Contenedor del mapa para ubicacion.js -->
                <div id="mapa" style="height: 400px;"></div>

                <!-- Leyenda del mapa -->
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-circle-fill text-primary"></i> Tu ubicación
                        <i class="bi bi-circle-fill text-danger ms-3"></i> Perros disponibles
                        <i class="bi bi-circle-fill text-warning ms-3"></i> Perros con match
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="actualizarUbicacion">
                    <i class="bi bi-geo-alt"></i> Actualizar mi ubicación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Estilos para los marcadores del mapa -->
<style>
.marcador-usuario {
    color: #007bff;
    font-size: 24px;
    text-align: center;
}

.marcador-perro {
    color: #dc3545;
    font-size: 24px;
    text-align: center;
}

.marcador-perro.match {
    color: #ffc107;
}

#mapaPerros {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style> 