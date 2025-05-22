document.addEventListener('DOMContentLoaded', () => {
  const modalMapa = document.getElementById('modalMapa');
  const slider = document.getElementById('rangoBusqueda');
  const kmValor = document.getElementById('kmValor');

  let mapa = null;
  let marcadorUsuario = null;
  let marcadoresPerros = [];
  let rangoKm = parseInt(slider.value);

  kmValor.textContent = rangoKm;

  // Actualizar valor y recargar marcadores al mover el slider
  slider.addEventListener('input', () => {
    rangoKm = parseInt(slider.value);
    kmValor.textContent = rangoKm;

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(pos => {
        cargarMapa(pos.coords.latitude, pos.coords.longitude);
      });
    }
  });

  // Al abrir modal, obtener ubicación y cargar mapa
  modalMapa.addEventListener('shown.bs.modal', () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(pos => {
        cargarMapa(pos.coords.latitude, pos.coords.longitude);
      }, () => {
        alert('No se pudo obtener la ubicación');
      });
    }
  });

  function cargarMapa(lat, lng) {
    if (!mapa) {
      mapa = L.map('mapa').setView([lat, lng], 13);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(mapa);
    } else {
      mapa.setView([lat, lng], 13);
    }

        if (marcadorUsuario) {
        marcadorUsuario.setLatLng([lat, lng]);
        } else {
        marcadorUsuario = L.marker([lat, lng])
            .addTo(mapa)
            .bindPopup('Tú estás aquí');
        }

    // Quitar marcadores viejos
    marcadoresPerros.forEach(m => mapa.removeLayer(m));
    marcadoresPerros = [];

    // Llamar a la API para perros cercanos
   fetch('http://localhost/Ignis360/Dogmatch1/api/perros_cercanos.php?lat=' + lat + '&lng=' + lng + '&rango=' + rangoKm)
      .then(res => res.json())
      .then(data => {
        data.forEach(perro => {
          const icon = L.icon({
            iconUrl: `/DogMatch/public/img/${perro.foto}`,
            iconSize: [40, 40],
            className: 'rounded-circle shadow'
          });

          const marker = L.marker([perro.latitud, perro.longitud], { icon })
            .addTo(mapa)
            .bindPopup(`
              <strong>${perro.nombre}</strong><br>
              Raza: ${perro.raza}
            `);

          marcadoresPerros.push(marker);
        });
      })
      .catch(err => {
        console.error('Error al cargar perros:', err);
      });
  }
});
