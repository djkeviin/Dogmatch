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

                <!-- Contenedor del mapa -->
                <div id="mapaPerros" style="height: 400px;"></div>

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

<!-- Script para el mapa -->
<script>
let mapa;
let marcadores = [];
const coordenadasIniciales = [19.4326, -99.1332]; // Coordenadas por defecto (CDMX)

// Inicializar el mapa cuando se abre el modal
document.getElementById('modalMapa').addEventListener('shown.bs.modal', function () {
    if (!mapa) {
        mapa = L.map('mapaPerros').setView(coordenadasIniciales, 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(mapa);
    }
    
    // Obtener ubicación actual
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            posicion => {
                const { latitude, longitude } = posicion.coords;
                mapa.setView([latitude, longitude], 13);
                actualizarMarcadores([latitude, longitude]);
            },
            error => {
                console.error('Error al obtener ubicación:', error);
                mapa.setView(coordenadasIniciales, 13);
                actualizarMarcadores(coordenadasIniciales);
            }
        );
    }
});

// Función para actualizar marcadores
function actualizarMarcadores(ubicacionUsuario) {
    // Limpiar marcadores existentes
    marcadores.forEach(marcador => marcador.remove());
    marcadores = [];

    // Agregar marcador del usuario
    const marcadorUsuario = L.marker(ubicacionUsuario, {
        icon: L.divIcon({
            className: 'marcador-usuario',
            html: '<i class="bi bi-person-fill"></i>',
            iconSize: [30, 30]
        })
    }).addTo(mapa);
    marcadores.push(marcadorUsuario);

    // Obtener perros cercanos
    const radio = document.getElementById('filtroDistancia').value;
    fetch('../../api/perros_cercanos.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            latitud: ubicacionUsuario[0],
            longitud: ubicacionUsuario[1],
            radio: radio
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            data.perros.forEach(perro => {
                const marcadorPerro = L.marker([perro.latitud, perro.longitud], {
                    icon: L.divIcon({
                        className: `marcador-perro ${perro.match ? 'match' : ''}`,
                        html: `<i class="bi bi-paw-fill"></i>`,
                        iconSize: [30, 30]
                    })
                })
                .bindPopup(`
                    <div class="text-center">
                        <img src="../../public/img/${perro.foto}" class="img-fluid rounded mb-2" style="max-height: 100px;">
                        <h6>${perro.nombre}</h6>
                        <p class="mb-1">${perro.raza} • ${perro.edad} meses</p>
                        <a href="../auth/perfil.php?id=${perro.id}" class="btn btn-primary btn-sm">Ver perfil</a>
                    </div>
                `)
                .addTo(mapa);
                marcadores.push(marcadorPerro);
            });
        }
    })
    .catch(error => console.error('Error:', error));
}

// Event listeners
document.getElementById('filtroDistancia').addEventListener('change', () => {
    if (mapa) {
        const centro = mapa.getCenter();
        actualizarMarcadores([centro.lat, centro.lng]);
    }
});

document.getElementById('actualizarUbicacion').addEventListener('click', () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            posicion => {
                const { latitude, longitude } = posicion.coords;
                mapa.setView([latitude, longitude], 13);
                actualizarMarcadores([latitude, longitude]);
            },
            error => console.error('Error al actualizar ubicación:', error)
        );
    }
});
</script>

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