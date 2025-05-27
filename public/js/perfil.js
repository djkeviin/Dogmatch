document.addEventListener('DOMContentLoaded', function() {
    // Inicializar los tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-ocultar notificaciones después de 5 segundos
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Vista previa de imágenes en el modal de subida
    const inputFotos = document.querySelector('input[name="fotos[]"]');
    if (inputFotos) {
        inputFotos.addEventListener('change', function(e) {
            const preview = document.getElementById('previewFotos');
            preview.innerHTML = '';
            
            Array.from(e.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'preview-image';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        });
    }

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

    // Validación del formulario de subir fotos
    const formSubirFotos = document.getElementById('formSubirFotos');
    if (formSubirFotos) {
        formSubirFotos.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    }
}); 