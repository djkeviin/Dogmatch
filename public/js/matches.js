// Inicialización cuando el documento está listo
$(document).ready(function() {
    console.log('Inicializando Select2...');
    
    // Inicializar Select2 para la selección de raza
    $('.raza-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Buscar una raza...',
        ajax: {
            url: '../../public/index.php?action=razas/buscar',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                console.log('Parámetros de búsqueda:', params);
                return {
                    q: params.term || ''
                };
            },
            processResults: function(response) {
                console.log('Respuesta del servidor:', response);
                
                if (!response || !response.success) {
                    console.error('Error en la respuesta:', response);
                    return { results: [] };
                }
                
                if (!response.data || !Array.isArray(response.data)) {
                    console.error('Datos inválidos:', response.data);
                    return { results: [] };
                }
                
                const results = response.data.map(function(raza) {
                    return {
                        id: raza.id,
                        text: raza.nombre,
                        data: raza
                    };
                });
                
                console.log('Resultados procesados:', results);
                return { results: results };
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX:', {
                    xhr: xhr,
                    status: status,
                    error: error
                });
            },
            cache: true
        },
        minimumInputLength: 2,
        templateResult: formatRazaOption,
        templateSelection: formatRazaSelection
    }).on('select2:open', function() {
        console.log('Select2 abierto');
    }).on('select2:closing', function() {
        console.log('Select2 cerrándose');
    }).on('select2:select', function(e) {
        console.log('Raza seleccionada:', e.params.data);
    });

    // Actualizar valor de distancia en tiempo real
    $('#distanciaRange').on('input', function() {
        $('#distanciaValue').text($(this).val());
    });

    // Manejar envío del formulario de filtros
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        const filters = {
            raza_id: $('.raza-select').val(),
            edad: $('select[name="edad"]').val(),
            distancia: $('input[name="distancia"]').val(),
            tamanio: $('select[name="tamanio"]').val(),
            grupo: $('select[name="grupo"]').val(),
            esterilizado: $('#esterilizado').is(':checked'),
            pedigri: $('#pedigri').is(':checked'),
            sociable_perros: $('#sociable_perros').is(':checked')
        };
        loadDogs(filters);
    });

    // Cargar perros inicialmente
    loadDogs();
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

// Template para generar una card
function createDogCard(dog) {
    const caracteristicas = [];
    if (dog.tamanio) caracteristicas.push(`<span class="badge bg-secondary">${dog.tamanio}</span>`);
    if (dog.grupo_raza) caracteristicas.push(`<span class="badge bg-primary">${dog.grupo_raza}</span>`);
    if (dog.esterilizado) caracteristicas.push('<span class="badge bg-success">Esterilizado</span>');
    if (dog.pedigri) caracteristicas.push('<span class="badge bg-warning">Pedigrí</span>');
    if (dog.sociable_perros) caracteristicas.push('<span class="badge bg-info">Sociable con perros</span>');

    return `
        <div class="card" data-dog-id="${dog.id}">
            <img src="../../public/img/${dog.foto}" class="card-img-top" alt="${dog.nombre}">
            <div class="card-body">
                <h3 class="card-title">${dog.nombre}, ${dog.edad} meses</h3>
                <p class="card-text">
                    <span class="badge bg-primary">${dog.raza}</span>
                    ${caracteristicas.join(' ')}
                </p>
                <p class="card-text">${dog.descripcion || ''}</p>
                <p class="small">A ${dog.distancia}km de distancia</p>
            </div>
            <div class="swipe-hint">
                <p>← Desliza para no match | Desliza para match →</p>
            </div>
        </div>
    `;
}

// Función para cargar perros según los filtros
async function loadDogs(filters = {}) {
    try {
        const response = await fetch('?action=perros/buscar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(filters)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.error) {
            throw new Error(result.error);
        }

        if (!result.success) {
            throw new Error('Respuesta inválida del servidor');
        }

        const dogs = result.data;
        const container = document.querySelector('.card-container');
        container.innerHTML = '';
        
        if (!dogs || dogs.length === 0) {
            container.innerHTML = `
                <div class="text-center p-5">
                    <i class="bi bi-emoji-frown" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">No se encontraron perros</h4>
                    <p>Intenta ajustar los filtros de búsqueda</p>
                </div>
            `;
            return;
        }

        dogs.forEach(dog => {
            container.innerHTML += createDogCard(dog);
        });
        initializeSwipe();
    } catch (error) {
        console.error('Error cargando perros:', error);
        showError('Error al cargar los perros: ' + error.message);
    }
}

// Función para match
async function match() {
    const currentCard = document.querySelector('.card');
    if (!currentCard) return;
    
    const dogId = currentCard.dataset.dogId;
    try {
        const response = await fetch('../../public/index.php?action=matches/crear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ dog_id: dogId, action: 'match' })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.error) {
            throw new Error(result.error);
        }

        currentCard.classList.add('swipe-right');
        setTimeout(() => {
            currentCard.remove();
            checkEmptyState();
        }, 300);
    } catch (error) {
        console.error('Error al crear match:', error);
        showError('Error al crear match: ' + error.message);
    }
}

// Función para no match
function noMatch() {
    const currentCard = document.querySelector('.card');
    if (!currentCard) return;
    
    currentCard.classList.add('swipe-left');
    setTimeout(() => {
        currentCard.remove();
        checkEmptyState();
    }, 300);
}

// Función para verificar si no hay más cards
function checkEmptyState() {
    const container = document.querySelector('.card-container');
    if (!container.querySelector('.card')) {
        container.innerHTML = `
            <div class="text-center p-5">
                <i class="bi bi-emoji-smile" style="font-size: 3rem;"></i>
                <h4 class="mt-3">¡No hay más perros por el momento!</h4>
                <p>Vuelve más tarde o ajusta tus filtros de búsqueda</p>
                <button class="btn btn-primary mt-3" onclick="loadDogs()">
                    <i class="bi bi-arrow-repeat"></i> Recargar
                </button>
            </div>
        `;
    }
}

// Función para mostrar errores
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger alert-dismissible fade show';
    errorDiv.innerHTML = `
        <i class="bi bi-exclamation-triangle-fill"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container').prepend(errorDiv);
}

// Función para inicializar el swipe en las cards
function initializeSwipe() {
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        const hammer = new Hammer(card);
        
        hammer.on('swipeleft', function() {
            noMatch();
        });
        
        hammer.on('swiperight', function() {
            match();
        });
    });
} 