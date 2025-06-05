$(document).ready(function() {
    console.log('Inicializando Select2 en registro...');
    
    // Inicializar Select2 para la selección de raza
    $('.raza-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Buscar una raza...',
        ajax: {
            url: '../../controllers/RazaController.php',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    action: 'buscar',
                    q: params.term || ''
                };
            },
            processResults: function(data) {
                console.log('Respuesta del servidor:', data);
                
                // Si no hay datos, devolver array vacío
                if (!data || data.length === 0) {
                    return { results: [] };
                }
                
                // Mapear los resultados
                const results = data.map(function(raza) {
                    return {
                        id: raza.id,
                        text: raza.nombre,
                        data: {
                            id: raza.id,
                            nombre: raza.nombre,
                            tamanio: raza.tamanio,
                            grupo_raza: raza.grupo_raza,
                            caracteristicas: raza.caracteristicas
                        }
                    };
                });
                
                return { results: results };
            },
            error: function(xhr, status, error) {
                console.error('Error en la búsqueda:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar las razas. Por favor, intente nuevamente.'
                });
            },
            cache: true
        },
        minimumInputLength: 1,
        language: {
            inputTooShort: function() {
                return 'Por favor ingrese 1 o más caracteres';
            },
            noResults: function() {
                return 'No se encontraron razas';
            },
            searching: function() {
                return 'Buscando...';
            }
        }
    }).on('select2:select', function(e) {
        const raza = e.params.data.data;
        mostrarInformacionRaza(raza);
    });

    // Validación del formulario
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});

// Función para formatear la opción en el dropdown
function formatRazaOption(raza) {
    if (!raza.id) {
        return raza.text;
    }
    
    return $(`
        <div class="raza-option">
            <div class="raza-info">
                <div class="raza-nombre">${raza.text}</div>
                ${raza.data ? `
                    <div class="raza-detalle">
                        ${raza.data.tamanio} - ${raza.data.grupo_raza}
                    </div>
                ` : ''}
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
    if (!raza) {
        $('.raza-card').hide();
        return;
    }

    $('.raza-card').show();
    $('.raza-nombre').text(raza.nombre || '');
    $('.raza-tamanio').text(raza.tamanio || '');
    $('.raza-grupo').text(raza.grupo_raza || '');
    
    if (raza.caracteristicas) {
        const caracteristicas = raza.caracteristicas.split(',');
        const caracteristicasHtml = caracteristicas.map(c => 
            `<span class="caracteristica-badge">${c.trim()}</span>`
        ).join('');
        $('.caracteristicas-lista').html(caracteristicasHtml);
    } else {
        $('.caracteristicas-lista').html('');
    }
} 