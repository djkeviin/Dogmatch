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
        session_start();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Método no permitido";
            header('Location: ../views/auth/perfil.php');
            exit;
        }

        if (!isset($_FILES['fotos']) || !isset($_POST['perro_id'])) {
            $_SESSION['error'] = "Faltan datos necesarios para subir las fotos";
            header('Location: ../views/auth/perfil.php');
            exit;
        }

        $perro_id = $_POST['perro_id'];
        $descripcion = $_POST['descripcion'] ?? '';
        $fotos = $_FILES['fotos'];
        $fotosSubidas = 0;

        try {
            // Verificar que el directorio de destino existe y tiene permisos
            $directorio_destino = __DIR__ . '/../public/img/';
            if (!is_dir($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
            }

            // Procesar cada foto
            for ($i = 0; $i < count($fotos['name']); $i++) {
                if ($fotos['error'][$i] === UPLOAD_ERR_OK) {
                    // Validar tipo de archivo
                    $tipo = $fotos['type'][$i];
                    if (!in_array($tipo, ['image/jpeg', 'image/png', 'image/gif'])) {
                        continue; // Saltar archivos que no son imágenes
                    }

                    $nombre_temporal = $fotos['tmp_name'][$i];
                    $extension = pathinfo($fotos['name'][$i], PATHINFO_EXTENSION);
                    $nombre_archivo = uniqid() . '_' . time() . '.' . $extension;
                    $ruta_destino = $directorio_destino . $nombre_archivo;

                    // Mover el archivo
                    if (move_uploaded_file($nombre_temporal, $ruta_destino)) {
                        // Guardar en la base de datos
                        $this->multimediaModel->crear([
                            'perro_id' => $perro_id,
                            'tipo' => 'foto',
                            'url_archivo' => $nombre_archivo,
                            'descripcion' => $descripcion
                        ]);
                        $fotosSubidas++;
                    }
                }
            }

            if ($fotosSubidas > 0) {
                $_SESSION['mensaje'] = "Se han subido $fotosSubidas fotos correctamente";
            } else {
                $_SESSION['error'] = "No se pudo subir ninguna foto";
            }
            
            header('Location: ../views/auth/perfil.php');
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = "Error al subir las fotos: " . $e->getMessage();
            header('Location: ../views/auth/perfil.php');
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