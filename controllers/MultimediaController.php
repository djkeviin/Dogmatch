<?php
require_once __DIR__ . '/../models/Multimedia.php';
require_once __DIR__ . '/../models/Perro.php';

class MultimediaController {
    private $multimediaModel;
    private $perroModel;

    public function __construct() {
        $this->multimediaModel = new Multimedia();
        $this->perroModel = new Perro();
    }

    public function subirFotos() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/auth/perfil.php?error=método_no_permitido');
            exit;
        }

        if (!isset($_FILES['fotos']) || !isset($_POST['perro_id'])) {
            header('Location: ../views/auth/perfil.php?error=datos_incompletos');
            exit;
        }

        $perro_id = $_POST['perro_id'];
        $descripcion = $_POST['descripcion'] ?? '';
        $fotos = $_FILES['fotos'];

        try {
            // Procesar cada foto
            for ($i = 0; $i < count($fotos['name']); $i++) {
                if ($fotos['error'][$i] === UPLOAD_ERR_OK) {
                    $nombre_temporal = $fotos['tmp_name'][$i];
                    $nombre_archivo = uniqid() . '_' . $fotos['name'][$i];
                    $ruta_destino = __DIR__ . '/../public/img/' . $nombre_archivo;

                    // Mover el archivo
                    if (move_uploaded_file($nombre_temporal, $ruta_destino)) {
                        // Guardar en la base de datos
                        $this->multimediaModel->crear([
                            'perro_id' => $perro_id,
                            'tipo' => 'foto',
                            'url_archivo' => $nombre_archivo,
                            'descripcion' => $descripcion
                        ]);
                    }
                }
            }

            header('Location: ../views/auth/perfil.php?success=fotos_subidas');
            exit;

        } catch (Exception $e) {
            header('Location: ../views/auth/perfil.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}

// Procesar la acción
if (isset($_POST['action'])) {
    $controller = new MultimediaController();
    switch ($_POST['action']) {
        case 'subirFotos':
            $controller->subirFotos();
            break;
    }
} 