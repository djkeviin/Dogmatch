<?php
// Verificar si hay un usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
?>

<!-- Modal de Reporte -->
<div class="modal fade" id="modalReporte" tabindex="-1" aria-labelledby="modalReporteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReporteLabel">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                    Reportar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formReporte" method="POST">
                <div class="modal-body">
                    
                    <!-- Información del usuario reportado -->
                    <?php if ($usuario_reportado): ?>
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <?php if ($perro): ?>
                            <img src="../../public/img/<?= htmlspecialchars($perro['foto'] ?? 'default-dog.png') ?>" 
                                 alt="<?= htmlspecialchars($perro['nombre']) ?>" 
                                 class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php endif; ?>
                            <div>
                                <strong>Reportando a:</strong> <?= htmlspecialchars($usuario_reportado['nombre']) ?>
                                <?php if ($perro): ?>
                                <br><small class="text-muted">Perro: <?= htmlspecialchars($perro['nombre']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Campos ocultos -->
                    <input type="hidden" name="reportado_id" value="<?= htmlspecialchars($usuario_reportado['id'] ?? '') ?>">
                    <input type="hidden" name="perro_id" value="<?= htmlspecialchars($perro['id'] ?? '') ?>">

                    <!-- Tipo de reporte -->
                    <div class="mb-3">
                        <label for="tipo_reporte" class="form-label">
                            <strong>Tipo de Reporte *</strong>
                        </label>
                        <select class="form-select" id="tipo_reporte" name="tipo_reporte" required>
                            <option value="">Selecciona un tipo de reporte</option>
                            <option value="perfil_falso">Perfil Falso</option>
                            <option value="contenido_inapropiado">Contenido Inapropiado</option>
                            <option value="spam">Spam</option>
                            <option value="acoso">Acoso</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            <strong>Descripción del Problema *</strong>
                        </label>
                        <textarea class="form-control" id="descripcion" name="descripcion" 
                                  rows="4" placeholder="Describe detalladamente el problema..." required></textarea>
                        <div class="form-text">
                            Proporciona detalles específicos para ayudar a nuestros moderadores a entender la situación.
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i>
                        <strong>Importante:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Solo puedes reportar a un usuario una vez cada 24 horas</li>
                            <li>Los reportes falsos pueden resultar en sanciones</li>
                            <li>Nuestros moderadores revisarán tu reporte en las próximas 24-48 horas</li>
                        </ul>
                    </div>

                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-exclamation-triangle"></i> Enviar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script para manejar el formulario -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formReporte = document.getElementById('formReporte');
    
    if (formReporte) {
        formReporte.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Deshabilitar botón y mostrar loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviando...';
            
            try {
                const response = await fetch('../../controllers/reportes/crear_reporte.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Mostrar mensaje de éxito
                    mostrarAlerta('Reporte enviado exitosamente. Gracias por ayudarnos a mantener la comunidad segura.', 'success');
                    
                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalReporte'));
                    modal.hide();
                    
                    // Limpiar formulario
                    this.reset();
                    
                } else {
                    throw new Error(result.error || 'Error al enviar el reporte');
                }
                
            } catch (error) {
                console.error('Error:', error);
                mostrarAlerta('Error al enviar el reporte: ' + error.message, 'error');
            } finally {
                // Restaurar botón
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
});

// Función para mostrar alertas
function mostrarAlerta(mensaje, tipo) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo === 'error' ? 'danger' : tipo} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script> 