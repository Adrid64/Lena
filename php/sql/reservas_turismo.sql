-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-06-2025 a las 00:55:10
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `reservas_turismo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puntos_interes`
--

CREATE TABLE `puntos_interes` (
  `id` int(11) NOT NULL,
  `recurso_id` int(11) NOT NULL,
  `texto` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `puntos_interes`
--

INSERT INTO `puntos_interes` (`id`, `recurso_id`, `texto`) VALUES
(1, 1, 'El Museo Histórico alberga una colección única de fósiles.'),
(2, 2, 'La Ruta de los Molinos incluye un tramo por el antiguo canal de agua.'),
(3, 3, 'El restaurante Los Collacios es famoso por su fabada.'),
(4, 4, 'Desde el Hotel Ruta de la Plata puedes ver la Vía Carisa.'),
(5, 5, 'Recomendamos traer prismáticos para la observación de aves.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recursos_turisticos`
--

CREATE TABLE `recursos_turisticos` (
  `id` int(11) NOT NULL,
  `tipo_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `limite_ocupacion` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `fecha_fin` date NOT NULL,
  `hora_fin` time NOT NULL,
  `precio` decimal(8,2) NOT NULL,
  `otros_detalles` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recursos_turisticos`
--

INSERT INTO `recursos_turisticos` (`id`, `tipo_id`, `nombre`, `descripcion`, `limite_ocupacion`, `fecha_inicio`, `hora_inicio`, `fecha_fin`, `hora_fin`, `precio`, `otros_detalles`) VALUES
(1, 1, 'Museo Histórico.', 'Exhibición de artefactos prehistóricos.', 30, '2025-06-10', '09:00:00', '2025-06-10', '18:00:00', 5.00, NULL),
(2, 2, 'Ruta de los Molinos', 'Paseo a pie por antiguos molinos del concejo.', 20, '2025-06-11', '10:00:00', '2025-06-11', '14:00:00', 10.00, 'Dificultad baja'),
(3, 3, 'Restaurante Los Collacios', 'Degustación de platos típicos asturianos.', 50, '2025-06-01', '13:00:00', '2025-06-01', '16:00:00', 25.00, 'Menú del día a 15€'),
(4, 4, 'Hotel Ruta de la plata', 'Alojamiento con vistas a la montaña.', 15, '2025-06-01', '14:00:00', '2025-06-01', '20:00:00', 60.00, 'Incluye desayuno'),
(5, 5, 'Observación de Aves', 'Actividad guiada para avistamiento de especies locales.', 15, '2025-06-15', '08:00:00', '2025-06-15', '12:00:00', 12.50, 'Llevar prismáticos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `recurso_id` int(11) NOT NULL,
  `fecha_reserva` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_inicio_evento` date NOT NULL,
  `dias` int(11) NOT NULL DEFAULT 1,
  `hora_evento` time NOT NULL,
  `precio_final` decimal(8,2) NOT NULL,
  `estado` enum('activa','anulada') NOT NULL DEFAULT 'activa',
  `numero_personas` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `usuario_id`, `recurso_id`, `fecha_reserva`, `fecha_inicio_evento`, `dias`, `hora_evento`, `precio_final`, `estado`, `numero_personas`) VALUES
(50, 7, 4, '2025-06-12 00:00:00', '2025-06-12', 7, '00:00:00', 420.00, 'activa', 1),
(51, 8, 4, '2025-06-12 00:00:00', '2025-06-12', 1, '00:00:00', 120.00, 'activa', 2),
(102, 3, 4, '2025-06-15 00:00:00', '2025-06-15', 3, '14:00:00', 1080.00, 'activa', 6),
(103, 3, 4, '2025-06-15 00:00:00', '2025-06-15', 3, '14:00:00', 1080.00, 'activa', 6),
(104, 3, 1, '2025-06-15 00:00:00', '2025-06-15', 1, '09:00:00', 5.00, 'activa', 1),
(105, 3, 1, '2025-06-16 00:00:00', '2025-06-16', 1, '09:00:00', 5.00, 'activa', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_recursos`
--

CREATE TABLE `tipo_recursos` (
  `id` int(11) NOT NULL,
  `nombre_tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_recursos`
--

INSERT INTO `tipo_recursos` (`id`, `nombre_tipo`) VALUES
(1, 'Museo'),
(2, 'Ruta'),
(3, 'Restaurante'),
(4, 'Hotel'),
(5, 'Actividad');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellidos`, `email`, `password_hash`, `fecha_registro`) VALUES
(1, 'Ana', 'García', 'ana.garcia@example.com', '48fee0a05ccaa361eac83d23d599f03c4bd339d857fb5a60250e12e3fe18d00a', '2025-06-06 14:53:10'),
(2, 'Luis', 'Pérez', 'luis.perez@example.com', '10a8ca5d329c9d094380fb11f31b14cc01e0426ef7593ba545a9c40b29f45374', '2025-06-06 14:53:10'),
(3, 'adrian', 'dumitru', 'adridumitru01@gmail.com', '$2y$10$3F6Yb5ti9clFxqWbl9ZaAOVLvXgI4PkYX19RxFmFwVbyAg9ekMCEu', '2025-06-06 15:04:04'),
(4, 'juan', 'juan', 'juan@gmail.com', '$2y$10$ZtyRiekMjtloLT4aKmxhfuZmnmFP9NMJKRrZeCG4qhwcyrC31A.SC', '2025-06-06 17:35:09'),
(5, 'Adrian', 'Dumitru', 'adrian@gmail.com', '$2y$10$l/P4RGMGc/IUWc4olzVup.kjVIFSux0sCIg.9L/2ThHRA/BpL58jW', '2025-06-11 18:46:15'),
(6, 'Adrian', 'Dumitru', 'adridumitru02@gmail.com', '$2y$10$dOWR9QsglFowkAH4mMqtquaK..g7u6AXwI1uZ133LSDHJ7Yrs5QPW', '2025-06-11 18:46:51'),
(7, 'Valentin', 'Dumitru', 'valentindmtr115@gmail.com', '$2y$10$X2TyVdb01EE1M2OX931P/.fWTF9osz/N1p7vZaxlCB0HnJpuZC0/K', '2025-06-12 15:00:20'),
(8, 'Valentin', 'Dumitru', 'mihaela@gmail.com', '$2y$10$xqE9exAPf2/NvfKQb5Z46.RIZEN9UL6RjemXA9nlnwY/u66sFzQHq', '2025-06-12 18:41:50'),
(9, 'adrian', 'dumitru', 'adridumitru04@gmail.com', '$2y$10$XHvNT4XEILW21K6Yce2fkeeIdxPDH1CILy5O9SU78pYcy94bcSK.O', '2025-06-15 17:56:03');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `puntos_interes`
--
ALTER TABLE `puntos_interes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recurso_id` (`recurso_id`);

--
-- Indices de la tabla `recursos_turisticos`
--
ALTER TABLE `recursos_turisticos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tipo_id` (`tipo_id`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `recurso_id` (`recurso_id`);

--
-- Indices de la tabla `tipo_recursos`
--
ALTER TABLE `tipo_recursos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `puntos_interes`
--
ALTER TABLE `puntos_interes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `recursos_turisticos`
--
ALTER TABLE `recursos_turisticos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT de la tabla `tipo_recursos`
--
ALTER TABLE `tipo_recursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `puntos_interes`
--
ALTER TABLE `puntos_interes`
  ADD CONSTRAINT `puntos_interes_ibfk_1` FOREIGN KEY (`recurso_id`) REFERENCES `recursos_turisticos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `recursos_turisticos`
--
ALTER TABLE `recursos_turisticos`
  ADD CONSTRAINT `recursos_turisticos_ibfk_1` FOREIGN KEY (`tipo_id`) REFERENCES `tipo_recursos` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`recurso_id`) REFERENCES `recursos_turisticos` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
