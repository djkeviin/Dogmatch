<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DogMatch - Encuentra tu match perfecto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Hammer.js para gestos táctiles -->
    <script src="https://hammerjs.github.io/dist/hammer.min.js"></script>
    <style>
        .card-container {
            position: relative;
            width: 100%;
            max-width: 600px;
            height: 600px;
            margin: 0 auto;
            perspective: 1000px;
        }

        .card {
            position: absolute;
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
            transition: all 0.5s ease;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .card-img-top {
            height: 70%;
            object-fit: cover;
        }

        .card-body {
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
            position: absolute;
            bottom: 0;
            width: 100%;
            color: white;
            padding: 20px;
        }

        .action-buttons {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 20px;
            z-index: 1000;
        }

        .action-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .action-button:hover {
            transform: scale(1.1);
        }

        .btn-no-match {
            background-color: #dc3545;
            color: white;
        }

        .btn-match {
            background-color: #28a745;
            color: white;
        }

        .filters-section {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .badge {
            font-size: 0.9em;
            margin-right: 5px;
        }

        .swipe-hint {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            background-color: rgba(0,0,0,0.5);
            padding: 10px 20px;
            border-radius: 20px;
            display: none;
        }

        .card:hover .swipe-hint {
            display: block;
        }

        /* Animaciones para swipe */
        .swipe-left {
            transform: translateX(-150%) rotate(-30deg);
            opacity: 0;
        }

        .swipe-right {
            transform: translateX(150%) rotate(30deg);
            opacity: 0;
        }

        /* Estilos para Select2 */
        .select2-container--bootstrap-5 .select2-selection {
            border-color: #ced4da;
        }

        .select2-container--bootstrap-5 .select2-selection:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #0d6efd;
            color: white;
        }

        .raza-option {
            padding: 8px;
        }

        .raza-nombre {
            font-weight: bold;
        }

        .raza-detalle {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Sección de Filtros -->
        <div class="filters-section mb-4">
            <h4 class="mb-3">Filtros de Búsqueda</h4>
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Raza</label>
                    <select class="form-control raza-select" name="raza">
                        <option value="">Todas las razas</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tamaño</label>
                    <select class="form-select" name="tamanio">
                        <option value="">Cualquier tamaño</option>
                        <option value="Pequeño">Pequeño</option>
                        <option value="Mediano">Mediano</option>
                        <option value="Grande">Grande</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Grupo</label>
                    <select class="form-select" name="grupo">
                        <option value="">Cualquier grupo</option>
                        <option value="Pastoreo">Pastoreo</option>
                        <option value="Deportivo">Deportivo</option>
                        <option value="No deportivo">No deportivo</option>
                        <option value="Terrier">Terrier</option>
                        <option value="Toy">Toy</option>
                        <option value="Sabueso">Sabueso</option>
                        <option value="Trabajo">Trabajo</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Edad</label>
                    <select class="form-select" name="edad">
                        <option value="">Cualquier edad</option>
                        <option value="0-12">Cachorro (0-12 meses)</option>
                        <option value="13-36">Joven (1-3 años)</option>
                        <option value="37-96">Adulto (3-8 años)</option>
                        <option value="97+">Senior (8+ años)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Distancia máxima</label>
                    <input type="range" class="form-range" id="distanciaRange" name="distancia" min="1" max="50" value="10">
                    <div class="text-center"><span id="distanciaValue">10</span> km</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Características</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="esterilizado" id="esterilizado">
                        <label class="form-check-label" for="esterilizado">Esterilizado</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="pedigri" id="pedigri">
                        <label class="form-check-label" for="pedigri">Con pedigrí</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="sociable_perros" id="sociable_perros">
                        <label class="form-check-label" for="sociable_perros">Sociable con perros</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Aplicar Filtros
                    </button>
                    <button type="reset" class="btn btn-outline-secondary ms-2" onclick="loadDogs()">
                        <i class="bi bi-arrow-counterclockwise"></i> Restablecer
                    </button>
                </div>
            </form>
        </div>

        <!-- Contenedor de Cards -->
        <div class="card-container">
            <!-- Las cards se generarán dinámicamente aquí -->
        </div>

        <!-- Botones de Acción -->
        <div class="action-buttons">
            <button class="action-button btn-no-match" onclick="noMatch()">
                <i class="bi bi-x-lg"></i>
            </button>
            <button class="action-button btn-match" onclick="match()">
                <i class="bi bi-heart-fill"></i>
            </button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../../public/js/matches.js"></script>
</body>
</html> 