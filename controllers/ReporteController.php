<?php
require_once __DIR__ . '/../models/Reporte.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Perro.php';

class ReporteController {
    private $reporteModel;
    private $usuarioModel;
    private $perroModel;

    public function __construct() {
        $this->reporteModel = new Reporte();
        $this->usuarioModel = new Usuario();
        $this->perroModel = new Perro();
    }

    /**
     * Crear un nuevo reporte
     */
    public function crearReporte() {
        try {
            // Verificar autenticación
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception('No autorizado');
            }

            // Validar datos de entrada
            $reportado_id = $_POST['reportado_id'] ?? null;
            $perro_id = $_POST['perro_id'] ?? null;
            $tipo_reporte = $_POST['tipo_reporte'] ?? null;
            $descripcion = $_POST['descripcion'] ?? '';

            if (!$reportado_id || !$tipo_reporte) {
                throw new Exception('Datos incompletos');
            }

            // Validar tipo de reporte
            $tipos_validos = ['perfil_falso', 'contenido_inapropiado', 'spam', 'acoso', 'otro'];
            if (!in_array($tipo_reporte, $tipos_validos)) {
                throw new Exception('Tipo de reporte inválido');
            }

            // Verificar que el usuario reportado existe
            $usuario_reportado = $this->usuarioModel->obtenerPorId($reportado_id);
            if (!$usuario_reportado) {
                throw new Exception('Usuario no encontrado');
            }

            // Verificar que el perro existe si se proporciona
            if ($perro_id) {
                $perro = $this->perroModel->obtenerPorId($perro_id);
                if (!$perro) {
                    throw new Exception('Perro no encontrado');
                }
            }

            // Crear el reporte
            $reporte_id = $this->reporteModel->crear(
                $_SESSION['usuario_id'],
                $reportado_id,
                $perro_id,
                $tipo_reporte,
                $descripcion
            );

            return [
                'success' => true,
                'message' => 'Reporte creado exitosamente',
                'reporte_id' => $reporte_id
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener reportes para moderación
     */
    public function obtenerReportes($estado = 'pendiente') {
        try {
            // Verificar si es moderador (aquí puedes agregar tu lógica de roles)
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception('No autorizado');
            }

            $reportes = $this->reporteModel->obtenerPorEstado($estado);
            
            return [
                'success' => true,
                'data' => $reportes
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener un reporte específico
     */
    public function obtenerReporte($id) {
        try {
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception('No autorizado');
            }

            $reporte = $this->reporteModel->obtenerPorId($id);
            
            if (!$reporte) {
                throw new Exception('Reporte no encontrado');
            }

            return [
                'success' => true,
                'data' => $reporte
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar estado de un reporte
     */
    public function actualizarEstado() {
        try {
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception('No autorizado');
            }

            $reporte_id = $_POST['reporte_id'] ?? null;
            $estado = $_POST['estado'] ?? null;
            $comentario = $_POST['comentario'] ?? null;

            if (!$reporte_id || !$estado) {
                throw new Exception('Datos incompletos');
            }

            $estados_validos = ['pendiente', 'en_revision', 'resuelto', 'descartado'];
            if (!in_array($estado, $estados_validos)) {
                throw new Exception('Estado inválido');
            }

            $resultado = $this->reporteModel->actualizarEstado(
                $reporte_id,
                $estado,
                $_SESSION['usuario_id'],
                $comentario
            );

            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Estado actualizado correctamente'
                ];
            } else {
                throw new Exception('No se pudo actualizar el estado');
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Tomar acción contra un usuario reportado
     */
    public function tomarAccion() {
        try {
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception('No autorizado');
            }

            $reporte_id = $_POST['reporte_id'] ?? null;
            $usuario_id = $_POST['usuario_id'] ?? null;
            $accion = $_POST['accion'] ?? null;
            $duracion = $_POST['duracion'] ?? null;
            $motivo = $_POST['motivo'] ?? '';

            if (!$reporte_id || !$usuario_id || !$accion) {
                throw new Exception('Datos incompletos');
            }

            $acciones_validas = ['advertencia', 'bloqueo_temporal', 'bloqueo_permanente', 'eliminacion'];
            if (!in_array($accion, $acciones_validas)) {
                throw new Exception('Acción inválida');
            }

            $resultado = $this->reporteModel->tomarAccion(
                $reporte_id,
                $usuario_id,
                $accion,
                $duracion,
                $motivo,
                $_SESSION['usuario_id']
            );

            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Acción aplicada correctamente'
                ];
            } else {
                throw new Exception('No se pudo aplicar la acción');
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas de reportes
     */
    public function obtenerEstadisticas() {
        try {
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception('No autorizado');
            }

            $estadisticas = $this->reporteModel->obtenerEstadisticas();
            
            return [
                'success' => true,
                'data' => $estadisticas
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar si un usuario puede reportar a otro
     */
    public function puedeReportar($reportado_id) {
        try {
            if (!isset($_SESSION['usuario_id'])) {
                return false;
            }

            return $this->reporteModel->puedeReportar($_SESSION['usuario_id'], $reportado_id);

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Mostrar formulario de reporte
     */
    public function mostrarFormularioReporte($perro_id = null) {
        try {
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception('No autorizado');
            }

            $perro = null;
            $usuario_reportado = null;

            if ($perro_id) {
                $perro = $this->perroModel->obtenerPorId($perro_id);
                if ($perro) {
                    $usuario_reportado = $this->usuarioModel->obtenerPorId($perro['usuario_id']);
                }
            }

            include '../views/reportes/formulario_reporte.php';

        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Mostrar panel de moderación
     */
    public function mostrarPanelModeracion() {
        try {
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception('No autorizado');
            }

            $reportes_pendientes = $this->reporteModel->obtenerPorEstado('pendiente');
            $estadisticas = $this->reporteModel->obtenerEstadisticas();

            include '../views/reportes/panel_moderacion.php';

        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
} 