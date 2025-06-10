<?php
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <h2>Encuentra tu match perfecto</h2>
    
    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Búsqueda -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="busqueda">Buscar</label>
                                <input type="text" class="form-control" id="busqueda" placeholder="Nombre, raza o dueño">
                            </div>
                        </div>
                        
                        <!-- Filtro de Raza -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="raza">Raza</label>
                                <select class="form-control" id="raza">
                                    <option value="">Todas las razas</option>
                                    <?php
                                    require_once '../config/database.php';
                                    $stmt = $conn->query("SELECT id, nombre FROM razas ORDER BY nombre");
                                    while ($raza = $stmt->fetch()) {
                                        echo "<option value='{$raza['id']}'>{$raza['nombre']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Filtro de Edad -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edad">Edad</label>
                                <select class="form-control" id="edad">
                                    <option value="">Todas las edades</option>
                                    <option value="cachorro">Cachorro (0-1 año)</option>
                                    <option value="joven">Joven (1-7 años)</option>
                                    <option value="adulto">Adulto (7+ años)</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Filtro de Valoración -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="valoracion">Valoración mínima</label>
                                <select class="form-control" id="valoracion">
                                    <option value="">Cualquier valoración</option>
                                    <option value="5">⭐⭐⭐⭐⭐ (5 estrellas)</option>
                                    <option value="4">⭐⭐⭐⭐ (4+ estrellas)</option>
                                    <option value="3">⭐⭐⭐ (3+ estrellas)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contador de resultados -->
    <div class="row mb-3">
        <div class="col">
            <p id="contador-resultados" class="text-muted"></p>
        </div>
    </div>

    <!-- Contenedor de perros -->
    <div class="row" id="perros-container">
        <!-- Las cards de perros se cargarán aquí dinámicamente -->
    </div>
</div>

<!-- Font Awesome para las estrellas -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Scripts -->
<script src="public/js/dashboard.js"></script>

<?php
require_once 'includes/footer.php';
?> 