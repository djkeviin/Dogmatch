$(document).ready(function() {
    // Inicializar Select2 para la selección de raza
    $('.raza-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Buscar una raza...',
        ajax: {
            url: '/DogMatch/api/razas/buscar',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(raza) {
                        return {
                            id: raza.id,
                            text: raza.nombre,
                            data: raza
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 2,
        templateResult: formatRazaOption,
        templateSelection: formatRazaSelection
    });

    // Cuando se selecciona una raza
    $('.raza-select').on('select2:select', function(e) {
        const raza = e.params.data.data;
        mostrarInformacionRaza(raza);
    });

    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Manejar la subida de fotos
    const formSubirFotos = document.getElementById('formSubirFotos');
    const inputFotos = document.querySelector('input[name="fotos[]"]');
    const preview = document.getElementById('previewFotos');
    
    if (formSubirFotos) {
        formSubirFotos.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    }

    if (inputFotos && preview) {
        inputFotos.addEventListener('change', function(e) {
            preview.innerHTML = '';
            
            if (e.target.files && e.target.files.length > 0) {
                Array.from(e.target.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        const imgContainer = document.createElement('div');
                        imgContainer.className = 'preview-image-container';
                        
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'preview-image';
                            imgContainer.appendChild(img);
                            preview.appendChild(imgContainer);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    }

    // Auto-ocultar notificaciones después de 5 segundos
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Inicializar el mapa si existe el contenedor
    const mapaContainer = document.getElementById('mapa');
    if (mapaContainer && typeof L !== 'undefined') {
        const lat = parseFloat(mapaContainer.dataset.lat);
        const lng = parseFloat(mapaContainer.dataset.lng);
        const nombre = mapaContainer.dataset.nombre;

        const mapa = L.map('mapa').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(mapa);

        L.marker([lat, lng])
            .addTo(mapa)
            .bindPopup(nombre);
    }

    // Validación del formulario de edición
    const formEditarPerfil = document.getElementById('formEditarPerfil');
    if (formEditarPerfil) {
        formEditarPerfil.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    }
});

// Función para formatear la opción en el dropdown
function formatRazaOption(raza) {
    if (!raza.data) return raza.text;
    
    return $(`
        <div class="raza-option">
            <div class="raza-info">
                <div class="raza-nombre">${raza.data.nombre}</div>
                <div class="raza-detalle">
                    ${raza.data.tamanio} - ${raza.data.grupo_raza}
                </div>
            </div>
        </div>
    `);
}

// Función para formatear la selección
function formatRazaSelection(raza) {
    return raza.text || 'Buscar una raza...';
}

// Función para mostrar la información detallada de la raza
function mostrarInformacionRaza(raza) {
    $('.raza-card').show();
    $('.raza-nombre').text(raza.nombre);
    $('.raza-descripcion').text(raza.descripcion);
    $('.raza-tamanio').text(raza.tamanio);
    $('.raza-grupo').text(raza.grupo_raza);
    
    // Mostrar características
    const caracteristicas = raza.caracteristicas.split(',');
    const caracteristicasHtml = caracteristicas.map(c => 
        `<span class="caracteristica-badge">${c.trim()}</span>`
    ).join('');
    $('.caracteristicas-lista').html(caracteristicasHtml);
}

// Función para actualizar la ubicación
function actualizarUbicacion(lat, lng, direccion) {
    $('#latitud').val(lat);
    $('#longitud').val(lng);
    $('#ubicacion').val(direccion);
} 