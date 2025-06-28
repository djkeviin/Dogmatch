document.addEventListener('DOMContentLoaded', () => {
  const modalMapa = document.getElementById('modalMapa');

  let mapa = null;
  let marcadorUsuario = null;
  let marcadoresPerros = [];
  let rangoKm = 10; // Rango fijo de 10 km

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
    fetch('http://localhost:3000/Ignis360/Dogmatch/api/perros_cercanos.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        latitud: lat,
        longitud: lng,
        radio: rangoKm
      })
    })
      .then(res => res.json())
      .then(data => {
        console.log('Respuesta API:', data);
        if (data.perros) {
          data.perros.forEach(perro => {
            const icon = L.icon({
              iconUrl: `/DogMatch/public/img/${perro.foto}`,
              iconSize: [40, 40],
              className: 'rounded-circle shadow'
            });

            const marker = L.marker([perro.latitud, perro.longitud], { icon })
              .addTo(mapa)
              .bindPopup(`
                <div class="text-center">
                  <a href="/Dogmatch/views/auth/perfil.php?id=${perro.id}" target="_blank">
                    <img src="/DogMatch/public/img/${perro.foto}" alt="${perro.nombre}" style="width:60px;height:60px;object-fit:cover;border-radius:50%;box-shadow:0 0 6px #000;">
                  </a>
                  <div class="fw-bold mt-2">${perro.nombre}</div>
                </div>
              `);

            marcadoresPerros.push(marker);
          });
        }
      })
      .catch(err => {
        console.error('Error al cargar perros:', err);
      });
  }
});
