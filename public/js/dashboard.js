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
            <button class="btn btn-sm btn-outline-primary" onclick="verPerfil(${dog.id})">
              Ver perfil
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="iniciarChat(${dog.id})">
              <i class="bi bi-chat-dots"></i> Chat
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
});
