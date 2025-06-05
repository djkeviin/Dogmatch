$(document).ready(function() {
    // Inicializar Select2 para el selector de razas
    $('.raza-select').select2({
        placeholder: 'Selecciona una raza',
        allowClear: true,
        ajax: {
            url: '../../controllers/RazaController.php?action=buscar',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(raza) {
                        return {
                            id: raza.id,
                            text: raza.nombre,
                            tamanio: raza.tamanio,
                            grupo: raza.grupo_raza,
                            descripcion: raza.descripcion,
                            caracteristicas: raza.caracteristicas
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 2
    }).on('select2:select', function(e) {
        var data = e.params.data;
        updateRazaCard(data);
    });

    function updateRazaCard(data) {
        $('.raza-nombre').text(data.text);
        $('.raza-descripcion').text(data.descripcion || 'Sin descripción disponible');
        $('.raza-tamanio').text(data.tamanio || 'No especificado');
        $('.raza-grupo').text(data.grupo || 'No especificado');
        
        // Actualizar características
        var caracteristicas = data.caracteristicas ? data.caracteristicas.split(',') : [];
        var caracteristicasHtml = caracteristicas.map(function(c) {
            return '<span class="badge bg-primary me-1">' + c.trim() + '</span>';
        }).join('');
        $('.caracteristicas-lista').html(caracteristicasHtml || 'No hay características especificadas');
        
        $('.raza-card').show();
    }
}); 