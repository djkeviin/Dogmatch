document.addEventListener("DOMContentLoaded", function () {
  const toggleBtn = document.getElementById("toggleSidebar");
  const sidebar = document.getElementById("sidebar");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", function () {
      sidebar.classList.toggle("active");
    });
  }

  // Actualizar el valor mostrado del rango de distancia
  const distanceSlider = document.getElementById('distance');
  const distanceValue = document.getElementById('distance-value');
  
  if (distanceSlider && distanceValue) {
    distanceSlider.addEventListener('input', function() {
      distanceValue.textContent = this.value + ' km';
    });
  }

  // Inicializar Select2 para el selector de razas
  if (typeof $ !== 'undefined' && $.fn.select2) {
    $('#breed').select2({
      placeholder: 'Selecciona razas',
      allowClear: true,
      theme: 'bootstrap-5'
    });
  }

  // Manejar el envío del formulario de búsqueda
  const searchForm = document.getElementById('searchForm');
  const searchResults = document.getElementById('searchResults');

  if (searchForm) {
    searchForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      // Mostrar indicador de carga
      if (searchResults) {
        searchResults.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
      }

      const formData = {
        breeds: $('#breed').val(),
        sizes: Array.from(document.querySelectorAll('input[name="size[]"]:checked')).map(cb => cb.value),
        ageMin: document.getElementById('age-min').value,
        ageMax: document.getElementById('age-max').value,
        health: {
          vaccinated: document.getElementById('health-vaccinated').checked,
          sterilized: document.getElementById('health-sterilized').checked
        },
        distance: document.getElementById('distance').value
      };

      try {
        const response = await fetch('/DogMatch/api/buscar_perros.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(formData)
        });

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          throw new Error('La respuesta del servidor no es JSON válido');
        }

        const data = await response.json();
        
        if (!response.ok) {
          throw new Error(data.error || 'Error en la búsqueda');
        }

        displayResults(data);
      } catch (error) {
        console.error('Error:', error);
        if (searchResults) {
          searchResults.innerHTML = `
            <div class="alert alert-danger" role="alert">
              <h4 class="alert-heading">Error en la búsqueda</h4>
              <p>${error.message}</p>
              <hr>
              <p class="mb-0">Por favor, intenta de nuevo o contacta al soporte si el problema persiste.</p>
            </div>
          `;
        }
      }
    });
  }

  // Función para mostrar los resultados
  function displayResults(results) {
    if (!searchResults) return;

    if (!Array.isArray(results) || !results.length) {
      searchResults.innerHTML = `
        <div class="alert alert-info" role="alert">
          <h4 class="alert-heading">No se encontraron resultados</h4>
          <p>No hay perros que coincidan con los criterios de búsqueda seleccionados.</p>
          <hr>
          <p class="mb-0">Intenta ajustar los filtros para ver más resultados.</p>
        </div>
      `;
      return;
    }

    searchResults.innerHTML = results.map(dog => `
      <div class="dog-card position-relative">
        <img src="${dog.foto ? '/DogMatch/public/img/' + dog.foto : '/DogMatch/public/img/default-dog.jpg'}" 
             alt="${dog.nombre}"
             onerror="this.src='/DogMatch/public/img/default-dog.jpg'">
        <div class="compatibility-score">${dog.compatibilidad || '?'}%</div>
        <div class="dog-card-body">
          <h5>${dog.nombre}</h5>
          <div class="dog-card-stats">
            <span class="dog-stat"><i class="bi bi-calendar"></i> ${dog.edad} años</span>
            <span class="dog-stat"><i class="bi bi-rulers"></i> ${dog.tamaño || 'No especificado'}</span>
            <span class="dog-stat"><i class="bi bi-geo-alt"></i> ${typeof dog.distancia === 'number' ? dog.distancia.toFixed(1) : '?'} km</span>
          </div>
          <div class="mt-2">
            ${dog.razas ? `<small class="text-muted">Razas: ${dog.razas}</small>` : ''}
          </div>
          <div class="mt-3">
            <button class="btn btn-sm btn-outline-primary w-100" onclick="verPerfil(${dog.id})">
              <i class="bi bi-eye"></i> Ver Perfil
            </button>
          </div>
        </div>
      </div>
    `).join('');
  }

  // Función para ver el perfil de un perro
  window.verPerfil = function(id) {
    window.location.href = `/DogMatch/views/perros/perfil.php?id=${id}`;
  };

  // Función para iniciar chat con un perro
  function iniciarChat(id) {
    // Por ahora solo mostraremos una alerta
    alert('Función de chat en desarrollo');
    // Aquí puedes agregar la lógica para abrir el chat cuando esté implementado
  }

  // Esperar a que el documento esté listo
  document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const filtrosForm = document.getElementById('filtrosForm');
    const busquedaInput = document.getElementById('busqueda');
    const filtroRaza = document.getElementById('filtroRaza');
    const filtroEdad = document.getElementById('filtroEdad');
    const filtroValoracion = document.getElementById('filtroValoracion');
    const perrosGrid = document.getElementById('perrosGrid');

    // Cargar razas al inicio
    cargarRazas();
    
    // Cargar perros iniciales
    aplicarFiltros();

    // Manejar envío del formulario de filtros
    if (filtrosForm) {
        filtrosForm.addEventListener('submit', function(e) {
            e.preventDefault();
            aplicarFiltros();
        });
    }

    // Aplicar filtros cuando cambian los selects
    [filtroRaza, filtroEdad, filtroValoracion].forEach(filtro => {
        if (filtro) {
            filtro.addEventListener('change', aplicarFiltros);
        }
    });

    // Aplicar filtros al escribir (con debounce)
    let timeout = null;
    if (busquedaInput) {
        busquedaInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(aplicarFiltros, 500);
        });
    }

    // Función para cargar las razas
    async function cargarRazas() {
        try {
            const response = await fetch('api/razas.php');
            const data = await response.json();
            
            if (data.success && filtroRaza) {
                filtroRaza.innerHTML = '<option value="">Todas las razas</option>';
                data.razas.forEach(raza => {
                    filtroRaza.innerHTML += `<option value="${raza.id}">${raza.nombre}</option>`;
                });
            } else {
                console.error('Error al cargar razas:', data.error);
            }
        } catch (error) {
            console.error('Error al cargar razas:', error);
        }
    }

    // Función para aplicar los filtros
    async function aplicarFiltros() {
        try {
            // Mostrar indicador de carga
            if (perrosGrid) {
                perrosGrid.innerHTML = `
                    <div class="col-12 text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>`;
            }

            const filtros = {
                busqueda: busquedaInput ? busquedaInput.value : '',
                raza: filtroRaza ? filtroRaza.value : '',
                edad: filtroEdad ? filtroEdad.value : '',
                valoracion: filtroValoracion ? filtroValoracion.value : ''
            };

            console.log('Aplicando filtros:', filtros); // Para depuración

            const response = await fetch('api/filtrar_perros.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(filtros)
            });

            const data = await response.json();
            
            if (data.success) {
                actualizarGrid(data.perros);
            } else {
                throw new Error(data.error || 'Error al filtrar perros');
            }
        } catch (error) {
            console.error('Error al aplicar filtros:', error);
            if (perrosGrid) {
                perrosGrid.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger" role="alert">
                            Error al filtrar perros: ${error.message}
                        </div>
                    </div>`;
            }
        }
    }

    // Función para actualizar el grid de perros
    function actualizarGrid(perros) {
        if (!perrosGrid) return;
        
        if (perros.length === 0) {
            perrosGrid.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        No se encontraron perros con los filtros seleccionados.
                    </div>
                </div>`;
            return;
        }

        perrosGrid.innerHTML = '';
        
        perros.forEach(perro => {
            const valoracionEstrellas = generarEstrellas(perro.valoracion_promedio);
            
            perrosGrid.innerHTML += `
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100 dog-card">
                        <img src="../../public/img/${perro.foto || 'default-dog.jpg'}" 
                             class="card-img-top dog-image" 
                             alt="${perro.nombre}">
                        <div class="card-body">
                            <h5 class="card-title d-flex justify-content-between align-items-center">
                                ${perro.nombre}
                                ${perro.disponible_apareamiento ? '<span class="badge bg-success">Disponible</span>' : ''}
                            </h5>
                            <p class="card-text">
                                ${perro.razas || 'Sin raza'} • ${perro.edad} ${perro.edad == 1 ? 'mes' : 'meses'} • ${perro.sexo}
                            </p>
                            <div class="rating-stars mb-2">
                                ${valoracionEstrellas}
                                <small class="text-muted">(${perro.total_valoraciones || 0})</small>
                            </div>
                            <div class="characteristics mb-3">
                                ${perro.vacunado ? '<span class="badge bg-info"><i class="bi bi-shield-check"></i> Vacunado</span>' : ''}
                                ${perro.pedigri ? '<span class="badge bg-warning"><i class="bi bi-award"></i> Pedigrí</span>' : ''}
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-flex justify-content-between">
                                <a href="perfil.php?id=${perro.id}" 
                                   class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-eye"></i> Ver Perfil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>`;
        });
    }

    // Función para manejar la valoración de un perro
    async function valorarPerro(perroId, puntuacion) {
        try {
            const response = await fetch('api/valoraciones.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    perro_id: perroId,
                    puntuacion: puntuacion
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Actualizar la visualización de las estrellas
                const cardPerro = document.querySelector(`[data-perro-id="${perroId}"]`);
                if (cardPerro) {
                    const ratingStars = cardPerro.querySelector('.rating-stars');
                    const valoracionEstrellas = generarEstrellas(data.promedio);
                    ratingStars.innerHTML = `${valoracionEstrellas} <small class="text-muted">(${data.total})</small>`;
                }

                // Mostrar mensaje de éxito
                mostrarMensaje('Valoración guardada correctamente', 'success');
            } else {
                throw new Error(data.error);
            }
        } catch (error) {
            console.error('Error al valorar:', error);
            mostrarMensaje('Error al guardar la valoración: ' + error.message, 'error');
        }
    }

    // Función para mostrar mensajes al usuario
    function mostrarMensaje(mensaje, tipo) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${tipo === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
        alertDiv.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertDiv);

        // Remover el mensaje después de 3 segundos
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }
  });
});

// Constantes
const DEBOUNCE_DELAY = 500;
let timeoutId = null;

// Funciones de utilidad
function mostrarCarga() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) {
        loadingIndicator.classList.remove('d-none');
    }
}

function ocultarCarga() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) {
        loadingIndicator.classList.add('d-none');
    }
}

function mostrarNoResultados() {
    const contenedor = document.getElementById('perrosGrid');
    contenedor.innerHTML = `
        <div class="col-12 text-center py-5">
            <i class="bi bi-emoji-frown" style="font-size: 3rem;"></i>
            <h4 class="mt-3">No se encontraron perros</h4>
            <p>Intenta ajustar los filtros de búsqueda</p>
        </div>
    `;
}

function mostrarError(mensaje) {
    const contenedor = document.getElementById('perrosGrid');
    contenedor.innerHTML = `
        <div class="col-12">
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                ${mensaje}
            </div>
        </div>
    `;
}

function generarEstrellas(valoracion) {
    let estrellas = '';
    const valoracionNum = parseFloat(valoracion) || 0;
    
    for (let i = 1; i <= 5; i++) {
        if (i <= valoracionNum) {
            estrellas += '<i class="bi bi-star-fill text-warning"></i>';
        } else if (i - 0.5 <= valoracionNum) {
            estrellas += '<i class="bi bi-star-half text-warning"></i>';
        } else {
            estrellas += '<i class="bi bi-star text-warning"></i>';
        }
    }
    return estrellas;
}

// Función para cargar los filtros
async function cargarFiltros() {
    try {
        const response = await fetch('../../api/filtros.php');
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Error al cargar los filtros');
        }

        // Cargar razas
        const filtroRaza = document.getElementById('filtroRaza');
        if (filtroRaza && data.filtros.razas) {
            filtroRaza.innerHTML = '<option value="">Todas las razas</option>' +
                data.filtros.razas.map(raza => 
                    `<option value="${raza.id}">${raza.nombre}</option>`
                ).join('');
        }

        // Cargar rangos de edad
        const filtroEdad = document.getElementById('filtroEdad');
        if (filtroEdad && data.filtros.rangos_edad) {
            filtroEdad.innerHTML = '<option value="">Cualquier edad</option>' +
                data.filtros.rangos_edad.map(rango => 
                    `<option value="${rango.rango_edad}">${rango.rango_edad} meses (${rango.total})</option>`
                ).join('');
        }

        // Cargar valoraciones
        const filtroValoracion = document.getElementById('filtroValoracion');
        if (filtroValoracion && data.filtros.valoraciones) {
            filtroValoracion.innerHTML = '<option value="">Cualquier valoración</option>' +
                data.filtros.valoraciones.map(val => 
                    `<option value="${val.valoracion}">${val.valoracion}+ estrellas (${val.total})</option>`
                ).join('');
        }

    } catch (error) {
        console.error('Error al cargar filtros:', error);
        mostrarError('Error al cargar los filtros. Por favor, recarga la página.');
    }
}

// Función principal para filtrar perros
async function filtrarPerros() {
    mostrarCarga();
    
    const busqueda = document.getElementById('busqueda').value;
    const raza = document.getElementById('filtroRaza').value;
    const edad = document.getElementById('filtroEdad').value;
    const valoracion = document.getElementById('filtroValoracion').value;

    try {
        const response = await fetch('../../api/filtrar_perros.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                busqueda,
                raza,
                edad,
                valoracion
            })
        });

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Error al filtrar perros');
        }

        const contenedor = document.getElementById('perrosGrid');
        
        if (!data.perros || data.perros.length === 0) {
            mostrarNoResultados();
            return;
        }

        contenedor.innerHTML = data.perros.map(perro => crearCardPerro(perro)).join('');
        
        // Actualizar contador de resultados
        const contadorResultados = document.getElementById('contador-resultados');
        if (contadorResultados) {
            contadorResultados.textContent = `${data.total} perro${data.total !== 1 ? 's' : ''} encontrado${data.total !== 1 ? 's' : ''}`;
        }

    } catch (error) {
        console.error('Error:', error);
        mostrarError('Error al cargar los perros. Por favor, intenta de nuevo más tarde.');
    } finally {
        ocultarCarga();
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Cargar filtros primero
        await cargarFiltros();
        
        // Luego cargar perros
        await filtrarPerros();

        // Configurar event listeners para los filtros
        const filtros = ['busqueda', 'filtroRaza', 'filtroEdad', 'filtroValoracion'];
        
        filtros.forEach(filtroId => {
            const elemento = document.getElementById(filtroId);
            if (elemento) {
                elemento.addEventListener('input', () => {
                    clearTimeout(timeoutId);
                    timeoutId = setTimeout(filtrarPerros, DEBOUNCE_DELAY);
                });
            }
        });

        // Manejar el envío del formulario
        const filtrosForm = document.getElementById('filtrosForm');
        if (filtrosForm) {
            filtrosForm.addEventListener('submit', (e) => {
                e.preventDefault();
                filtrarPerros();
            });
        }
    } catch (error) {
        console.error('Error en la inicialización:', error);
        mostrarError('Error al inicializar la página. Por favor, recarga.');
    }
});

function crearCardPerro(perro) {
    return `
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 dog-card">
                <img src="../../public/img/${perro.foto || 'default-dog.jpg'}" 
                     class="card-img-top dog-image" 
                     alt="${perro.nombre}">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between align-items-center">
                        ${perro.nombre}
                        ${perro.disponible_apareamiento ? '<span class="badge bg-success">Disponible</span>' : ''}
                    </h5>
                    <p class="card-text">
                        ${perro.razas || 'Sin raza'} • ${perro.edad} ${perro.edad == 1 ? 'mes' : 'meses'} • ${perro.sexo}
                    </p>
                    <div class="rating-stars mb-2">
                        ${generarEstrellas(perro.valoracion_promedio)}
                        <small class="text-muted">(${perro.total_valoraciones || 0})</small>
                    </div>
                    <div class="characteristics mb-3">
                        ${perro.vacunado ? '<span class="badge bg-info"><i class="bi bi-shield-check"></i> Vacunado</span>' : ''}
                        ${perro.pedigri ? '<span class="badge bg-warning"><i class="bi bi-award"></i> Pedigrí</span>' : ''}
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <div class="d-flex justify-content-between">
                        <a href="perfil.php?id=${perro.id}" 
                           class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-eye"></i> Ver Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
}

