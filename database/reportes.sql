-- Tabla para almacenar reportes de usuarios
CREATE TABLE IF NOT EXISTS reportes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reportador_id INT NOT NULL,
    reportado_id INT NOT NULL,
    perro_id INT NULL,
    tipo_reporte ENUM('perfil_falso', 'contenido_inapropiado', 'spam', 'acoso', 'otro') NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('pendiente', 'en_revision', 'resuelto', 'descartado') DEFAULT 'pendiente',
    accion_tomada ENUM('ninguna', 'advertencia', 'bloqueo_temporal', 'bloqueo_permanente', 'eliminacion') DEFAULT 'ninguna',
    fecha_reporte TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_resolucion TIMESTAMP NULL,
    moderador_id INT NULL,
    comentario_moderador TEXT NULL,
    INDEX idx_reportador (reportador_id),
    INDEX idx_reportado (reportado_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_reporte),
    FOREIGN KEY (reportador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (reportado_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (perro_id) REFERENCES perros(id) ON DELETE SET NULL,
    FOREIGN KEY (moderador_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla para configuraciones de moderación
CREATE TABLE IF NOT EXISTS configuracion_moderacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_reporte ENUM('perfil_falso', 'contenido_inapropiado', 'spam', 'acoso', 'otro') NOT NULL,
    accion_automatica ENUM('ninguna', 'advertencia', 'bloqueo_temporal', 'bloqueo_permanente') DEFAULT 'ninguna',
    duracion_bloqueo INT DEFAULT 0, -- en días, 0 = permanente
    umbral_reportes INT DEFAULT 3, -- número de reportes antes de acción automática
    activo BOOLEAN DEFAULT TRUE,
    UNIQUE KEY unique_tipo (tipo_reporte)
);

-- Insertar configuraciones por defecto
INSERT INTO configuracion_moderacion (tipo_reporte, accion_automatica, duracion_bloqueo, umbral_reportes) VALUES
('perfil_falso', 'advertencia', 0, 2),
('contenido_inapropiado', 'bloqueo_temporal', 7, 3),
('spam', 'bloqueo_temporal', 14, 2),
('acoso', 'bloqueo_permanente', 0, 1),
('otro', 'advertencia', 0, 5)
ON DUPLICATE KEY UPDATE accion_automatica = VALUES(accion_automatica);

-- Tabla para historial de acciones de moderación
CREATE TABLE IF NOT EXISTS historial_moderacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporte_id INT NOT NULL,
    usuario_id INT NOT NULL,
    accion ENUM('advertencia', 'bloqueo_temporal', 'bloqueo_permanente', 'desbloqueo', 'eliminacion') NOT NULL,
    duracion INT NULL, -- en días, NULL para permanente
    motivo TEXT NOT NULL,
    fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    moderador_id INT NOT NULL,
    FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (moderador_id) REFERENCES usuarios(id) ON DELETE CASCADE
); 