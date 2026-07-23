-- ============================================================
-- Base de datos: evospace (VERSIÓN MEJORADA)
-- ============================================================
CREATE DATABASE IF NOT EXISTS evospace
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE evospace;

-- ============================================================
-- Tabla: roles (NUEVA - para sistema de permisos)
-- ============================================================
CREATE TABLE IF NOT EXISTS `roles` (
  `id_rol` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255),
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `roles` (`nombre`, `descripcion`) VALUES
('admin', 'Administrador con acceso total'),
('profesor', 'Profesor con acceso limitado a alumnos y asistencia'),
('padre', 'Padre con acceso solo a sus hijos'),
('auxiliar', 'Auxiliar con acceso a listas de alumnos y asistencia');

-- ============================================================
-- Tabla: permisos (NUEVA)
-- ============================================================
CREATE TABLE IF NOT EXISTS `permisos` (
  `id_permiso` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255),
  PRIMARY KEY (`id_permiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `permisos` (`nombre`, `descripcion`) VALUES
('ver_alumnos', 'Ver listado de alumnos'),
('editar_alumnos', 'Crear/editar/eliminar alumnos'),
('ver_pagos', 'Ver pagos y deudas'),
('editar_pagos', 'Registrar/modificar pagos'),
('ver_eventos', 'Ver eventos'),
('editar_eventos', 'Crear/editar/eliminar eventos'),
('ver_asistencia', 'Ver registros de asistencia'),
('editar_asistencia', 'Tomar/modificar asistencia'),
('ver_profesores', 'Ver listado de profesores'),
('editar_profesores', 'Gestionar profesores'),
('ver_configuracion', 'Ver configuración'),
('editar_configuracion', 'Modificar configuración'),
('gestionar_usuarios', 'Crear/editar/eliminar usuarios'),
('ver_padres', 'Ver lista de padres'),
('editar_padres', 'Gestionar padres');

-- ============================================================
-- Tabla: rol_permiso (NUEVA - relación)
-- ============================================================
CREATE TABLE IF NOT EXISTS `rol_permiso` (
  `id_rol` INT NOT NULL,
  `id_permiso` INT NOT NULL,
  PRIMARY KEY (`id_rol`, `id_permiso`),
  FOREIGN KEY (`id_rol`) REFERENCES `roles`(`id_rol`) ON DELETE CASCADE,
  FOREIGN KEY (`id_permiso`) REFERENCES `permisos`(`id_permiso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Asignar permisos a ADMIN (todos)
INSERT IGNORE INTO `rol_permiso` (`id_rol`, `id_permiso`)
SELECT r.id_rol, p.id_permiso
FROM `roles` r, `permisos` p
WHERE r.nombre = 'admin';

-- Asignar permisos a PROFESOR
INSERT IGNORE INTO `rol_permiso` (`id_rol`, `id_permiso`)
SELECT r.id_rol, p.id_permiso
FROM `roles` r, `permisos` p
WHERE r.nombre = 'profesor' 
AND p.nombre IN ('ver_alumnos', 'editar_alumnos', 'ver_asistencia', 'editar_asistencia', 'ver_eventos');

-- Asignar permisos a AUXILIAR
INSERT IGNORE INTO `rol_permiso` (`id_rol`, `id_permiso`)
SELECT r.id_rol, p.id_permiso
FROM `roles` r, `permisos` p
WHERE r.nombre = 'auxiliar' 
AND p.nombre IN ('ver_alumnos', 'ver_asistencia', 'editar_asistencia');

-- ============================================================
-- Tabla: usuarios (MODIFICADA - ahora usa id_rol en lugar de rol ENUM)
-- ============================================================
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `cedula` VARCHAR(20) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `id_rol` INT NOT NULL,
  `nombre_completo` VARCHAR(150),
  `telefono` VARCHAR(20),
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  FOREIGN KEY (`id_rol`) REFERENCES `roles`(`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuarios de prueba (contraseñas: admin123, profe123, padre123, aux123)
INSERT IGNORE INTO `usuarios` (`usuario`, `email`, `cedula`, `password_hash`, `id_rol`, `nombre_completo`) VALUES
('admin', 'admin@evospace.com', '1234567', '$2y$10$LBSyD2UFwBLJA/G1i4CRh.pVZJ/q/n2zhkSGNilT5OxM6IK3ccyBC', 
 (SELECT id_rol FROM roles WHERE nombre = 'admin'), 'Administrador'),
('profesor', 'profe@evospace.com', '2345678', '$2y$10$19GT94aaJLh/UxAhesrC9OwP5oY57xYuzes4FJeHSsUtyhyv7Rp3q',
 (SELECT id_rol FROM roles WHERE nombre = 'profesor'), 'Profesor Ejemplo'),
('padre', 'padre@evospace.com', '3456789', '$2y$10$XobVID8qYtFtqHFsbL3XKuBuoiji5c3s1po8FWLYXXyi9oLSwjL1u',
 (SELECT id_rol FROM roles WHERE nombre = 'padre'), 'Padre Ejemplo'),
('auxiliar', 'auxiliar@evospace.com', '5678901', '$2y$10$LBSyD2UFwBLJA/G1i4CRh.pVZJ/q/n2zhkSGNilT5OxM6IK3ccyBC',
 (SELECT id_rol FROM roles WHERE nombre = 'auxiliar'), 'Auxiliar Ejemplo');

-- ============================================================
-- Tabla: cursos (SIN CAMBIOS)
-- ============================================================
CREATE TABLE IF NOT EXISTS `cursos` (
  `id_curso` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `tipo` ENUM('Acrotelas', 'Infantil', 'Superior') NOT NULL,
  `orden` INT NOT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id_curso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `cursos` (`nombre`, `tipo`, `orden`) VALUES
('Inicial', 'Acrotelas', 1),
('Primer Curso', 'Acrotelas', 2),
('Segundo Curso', 'Acrotelas', 3),
('Tercer Curso', 'Acrotelas', 4),
('Cuarto Curso', 'Acrotelas', 5),
('Quinto Curso', 'Acrotelas', 6),
('Nivel Inicial I', 'Infantil', 1),
('Nivel Inicial II', 'Infantil', 2),
('Primer Grado', 'Infantil', 3),
('Segundo Grado', 'Infantil', 4),
('Tercer Grado', 'Infantil', 5),
('Cuarto Grado', 'Infantil', 6),
('Quinto Grado', 'Infantil', 7),
('Sexto Grado', 'Infantil', 8),
('Principiante Superior', 'Superior', 1),
('Preparatorio Superior', 'Superior', 2),
('Primer Curso', 'Superior', 3),
('Segundo Curso', 'Superior', 4),
('Tercer Curso', 'Superior', 5),
('Cuarto Curso', 'Superior', 6),
('Quinto Curso', 'Superior', 7);

-- ============================================================
-- Tabla: alumnos (MODIFICADA - ahora usamos id_curso en lugar de curso texto)
-- ============================================================
CREATE TABLE IF NOT EXISTS `alumnos` (
  `id_alumno` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `id_curso` INT NOT NULL,
  `anio_ingreso` YEAR NOT NULL,
  `horas_profesionales` DECIMAL(6,2) DEFAULT 0.00,
  `ci` VARCHAR(20) NOT NULL UNIQUE,
  `telefono` VARCHAR(20),
  `id_padre` INT DEFAULT NULL,
  `becado` TINYINT(1) DEFAULT 0,
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_alumno`),
  FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id_curso`),
  FOREIGN KEY (`id_padre`) REFERENCES `usuarios`(`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de prueba
INSERT IGNORE INTO `alumnos` (`nombre`, `apellido`, `id_curso`, `anio_ingreso`, `horas_profesionales`, `ci`, `telefono`, `id_padre`, `becado`) VALUES
('Mariela', 'Nuñez Esteche', 20, 2022, 1312.00, '283892', '254234', 
 (SELECT id_usuario FROM usuarios WHERE usuario = 'padre'), 0),
('Natan', 'Levy', 1, 2023, 0.00, '123456', '098765',
 (SELECT id_usuario FROM usuarios WHERE usuario = 'padre'), 1),
('Clara', 'Vallejos', 9, 2024, 0.00, '654321', '099888', NULL, 0),
('Jessica', 'Giménez', 18, 2021, 800.00, '111222', '456789', NULL, 1),
('Carlos', 'Ruiz', 2, 2023, 0.00, '987654', '555123',
 (SELECT id_usuario FROM usuarios WHERE usuario = 'auxiliar'), 0),
('Sofía', 'Martínez', 14, 2024, 0.00, '456123', '555456',
 (SELECT id_usuario FROM usuarios WHERE usuario = 'padre'), 0);

-- ============================================================
-- Tabla: precios (SIN CAMBIOS, pero con más datos)
-- ============================================================
CREATE TABLE IF NOT EXISTS `precios` (
  `id_precio` INT NOT NULL AUTO_INCREMENT,
  `id_curso` INT NOT NULL,
  `concepto` VARCHAR(50) NOT NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `descuento_beca` DECIMAL(5,2) DEFAULT 0.00,
  `aplica_beca` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id_precio`),
  UNIQUE KEY `id_curso` (`id_curso`, `concepto`),
  FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id_curso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar precios (usando subconsultas para los id_curso)
INSERT IGNORE INTO `precios` (`id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`)
SELECT id_curso, 'matrícula', 150000, 0, 0 FROM cursos WHERE tipo = 'Acrotelas';
INSERT IGNORE INTO `precios` (`id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`)
SELECT id_curso, 'cuota', 200000, 45.45, 1 FROM cursos WHERE tipo = 'Acrotelas';
INSERT IGNORE INTO `precios` (`id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`)
SELECT id_curso, 'vestuarios', 150000, 0, 0 FROM cursos WHERE tipo = 'Acrotelas';
INSERT IGNORE INTO `precios` (`id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`)
SELECT id_curso, 'entradas', 80000, 0, 0 FROM cursos WHERE tipo = 'Acrotelas';

INSERT IGNORE INTO `precios` (`id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`)
SELECT id_curso, 'matrícula', 180000, 0, 0 FROM cursos WHERE tipo = 'Infantil';
INSERT IGNORE INTO `precios` (`id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`)
SELECT id_curso, 'cuota', 220000, 45.45, 1 FROM cursos WHERE tipo = 'Infantil';
INSERT IGNORE INTO `precios` (`id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`)
SELECT id_curso, 'vestuarios', 150000, 0, 0 FROM cursos WHERE tipo = 'Infantil';
INSERT IGNORE INTO `precios` (`id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`)
SELECT id_curso, 'entradas', 80000, 0, 0 FROM cursos WHERE tipo = 'Infantil';
INSERT IGNORE INTO `precios` (`id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`)
SELECT id_curso, 'folleto', 25000, 0, 0 FROM cursos WHERE tipo = 'Infantil';

INSERT IGNORE INTO `precios` (`id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`)
SELECT id_curso, 'matrícula', 180000, 0, 0 FROM cursos WHERE tipo = 'Superior';
INSERT IGNORE INTO `precios` (`id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`)
SELECT id_curso, 'cuota', 250000, 45.45, 1 FROM cursos WHERE tipo = 'Superior';
INSERT IGNORE INTO `precios` (`id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`)
SELECT id_curso, 'vestuarios', 150000, 0, 0 FROM cursos WHERE tipo = 'Superior';
INSERT IGNORE INTO `precios` (`id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`)
SELECT id_curso, 'entradas', 80000, 0, 0 FROM cursos WHERE tipo = 'Superior';

-- Folletos para Superior con precios por nivel
UPDATE `precios` SET `precio` = 30000 WHERE `concepto` = 'folleto' AND `id_curso` IN (15, 16);
UPDATE `precios` SET `precio` = 40000 WHERE `concepto` = 'folleto' AND `id_curso` IN (17, 18);
UPDATE `precios` SET `precio` = 50000 WHERE `concepto` = 'folleto' AND `id_curso` IN (19, 20);
UPDATE `precios` SET `precio` = 60000 WHERE `concepto` = 'folleto' AND `id_curso` = 21;

-- ============================================================
-- Tabla: pagos (MODIFICADA - cálculo de beca integrado)
-- ============================================================
CREATE TABLE IF NOT EXISTS `pagos` (
  `id_pago` INT NOT NULL AUTO_INCREMENT,
  `id_alumno` INT NOT NULL,
  `fecha` DATE NOT NULL,
  `concepto` VARCHAR(200) NOT NULL,
  `cantidad` INT DEFAULT 1,
  `monto` DECIMAL(10,2) NOT NULL,
  `descuento` DECIMAL(5,2) DEFAULT 0.00,
  `recargo` DECIMAL(10,2) DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL,
  `metodo_pago` ENUM('Efectivo', 'Transferencia', 'Tarjeta', 'Otro') DEFAULT 'Efectivo',
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pago`),
  FOREIGN KEY (`id_alumno`) REFERENCES `alumnos`(`id_alumno`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: eventos (SIN CAMBIOS)
-- ============================================================
CREATE TABLE IF NOT EXISTS `eventos` (
  `id_evento` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(200) NOT NULL,
  `descripcion` TEXT,
  `fecha` DATE NOT NULL,
  `hora` TIME,
  `lugar` VARCHAR(200),
  `enlace_ubicacion` VARCHAR(255),
  `color` VARCHAR(7) DEFAULT '#c81015',
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_evento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evento_curso` (
  `id_evento` INT NOT NULL,
  `id_curso` INT NOT NULL,
  PRIMARY KEY (`id_evento`, `id_curso`),
  FOREIGN KEY (`id_evento`) REFERENCES `eventos`(`id_evento`) ON DELETE CASCADE,
  FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id_curso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: configuracion (SIN CAMBIOS)
-- ============================================================
CREATE TABLE IF NOT EXISTS `configuracion` (
  `clave` VARCHAR(50) NOT NULL,
  `valor` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `configuracion` (`clave`, `valor`) VALUES
('porcentaje_beca', '45.45'),
('recargo_por_dia', '1000'),
('dia_limite_pago', '10');

-- ============================================================
-- Tabla: asistencia
-- ============================================================
CREATE TABLE IF NOT EXISTS `asistencia` (
  `id_asistencia` INT NOT NULL AUTO_INCREMENT,
  `id_alumno` INT NOT NULL,
  `id_curso` INT NOT NULL,
  `fecha` DATE NOT NULL,
  `presente` TINYINT(1) DEFAULT 0,
  `observaciones` TEXT,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_asistencia`),
  UNIQUE KEY `id_alumno` (`id_alumno`, `fecha`),
  FOREIGN KEY (`id_alumno`) REFERENCES `alumnos`(`id_alumno`) ON DELETE CASCADE,
  FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id_curso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: notificaciones
-- ============================================================
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id_notificacion` INT NOT NULL AUTO_INCREMENT,
  `id_evento` INT NOT NULL,
  `id_curso` INT NOT NULL,
  `titulo` VARCHAR(200) NOT NULL,
  `mensaje` TEXT,
  `tipo` ENUM('evento', 'pago', 'general') DEFAULT 'evento',
  `leida` TINYINT(1) DEFAULT 0,
  `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_notificacion`),
  FOREIGN KEY (`id_evento`) REFERENCES `eventos`(`id_evento`) ON DELETE CASCADE,
  FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id_curso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: profesores (extensión de usuarios)
-- ============================================================
CREATE TABLE IF NOT EXISTS `profesores` (
  `id_profesor` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL UNIQUE,
  `especialidad` VARCHAR(100),
  `salario_base` DECIMAL(10,2),
  `fecha_contratacion` DATE,
  `activo` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id_profesor`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT '✅ Base de datos actualizada correctamente' AS mensaje;