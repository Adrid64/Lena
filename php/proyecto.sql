-- proyecto.sql

-- 1. Crear base de datos (si no existe) y usarla
CREATE DATABASE IF NOT EXISTS `reservas_turismo`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE `reservas_turismo`;

-- 2. Tabla usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(50) NOT NULL,
  `apellidos` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabla tipo_recursos
CREATE TABLE IF NOT EXISTS `tipo_recursos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre_tipo` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabla recursos_turisticos
CREATE TABLE IF NOT EXISTS `recursos_turisticos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tipo_id` INT NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `limite_ocupacion` INT NOT NULL,
  `fecha_inicio` DATE NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `fecha_fin` DATE NOT NULL,
  `hora_fin` TIME NOT NULL,
  `precio` DECIMAL(8,2) NOT NULL,
  `otros_detalles` TEXT,
  FOREIGN KEY (`tipo_id`) REFERENCES `tipo_recursos`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabla reservas
CREATE TABLE IF NOT EXISTS `reservas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `recurso_id` INT NOT NULL,
  `fecha_reserva` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_evento` DATE NOT NULL,
  `hora_evento` TIME NOT NULL,
  `precio_final` DECIMAL(8,2) NOT NULL,
  `estado` ENUM('activa','anulada') NOT NULL DEFAULT 'activa',
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`recurso_id`) REFERENCES `recursos_turisticos`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabla recursos_multimedia (opcional)
CREATE TABLE IF NOT EXISTS `recursos_multimedia` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `recurso_id` INT NOT NULL,
  `tipo` ENUM('imagen','video','audio') NOT NULL,
  `ruta_archivo` VARCHAR(255) NOT NULL,
  FOREIGN KEY (`recurso_id`) REFERENCES `recursos_turisticos`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Insertar tipos de recursos de ejemplo
INSERT INTO `tipo_recursos` (`nombre_tipo`) VALUES
  ('Museo'),
  ('Ruta'),
  ('Restaurante'),
  ('Hotel'),
  ('Actividad');

-- 8. Insertar recursos turísticos de ejemplo
INSERT INTO `recursos_turisticos`
  (`tipo_id`,`nombre`,`descripcion`,`limite_ocupacion`,`fecha_inicio`,`hora_inicio`,`fecha_fin`,`hora_fin`,`precio`,`otros_detalles`)
VALUES
  (1, 'Museo Histórico', 'Exhibición de artefactos prehistóricos.', 30, '2025-06-10','09:00','2025-06-10','18:00', 5.00, NULL),
  (2, 'Ruta de los Molinos', 'Paseo a pie por antiguos molinos del concejo.', 20, '2025-06-11','10:00','2025-06-11','14:00', 10.00, 'Dificultad baja'),
  (3, 'Restaurante Los Collacios', 'Degustación de platos típicos asturianos.', 50, '2025-06-01','13:00','2025-06-30','23:00', 25.00, 'Menú del día a 15€'),
  (4, 'Hotel Ruta de la plata', 'Alojamiento con vistas a la montaña.', 15, '2025-06-01','00:00','2025-12-31','23:59', 60.00, 'Incluye desayuno'),
  (5, 'Observación de Aves', 'Actividad guiada para avistamiento de especies locales.', 15, '2025-06-15','08:00','2025-06-15','12:00', 12.50, 'Llevar prismáticos');

-- 9. Insertar algunos usuarios de ejemplo
INSERT INTO `usuarios` (`nombre`,`apellidos`,`email`,`password_hash`)
VALUES
  ('Ana','García','ana.garcia@example.com', SHA2('Ana12345',256)),
  ('Luis','Pérez','luis.perez@example.com', SHA2('Luis12345',256));

-- 10. Insertar algunas reservas de ejemplo
INSERT INTO `reservas` (`usuario_id`,`recurso_id`,`fecha_evento`,`hora_evento`,`precio_final`)
VALUES
  (1, 2, '2025-06-11','10:00', 10.00),
  (2, 3, '2025-06-15','14:00', 25.00);
