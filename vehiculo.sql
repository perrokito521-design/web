-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-01-2026 a las 00:36:42
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bschiwa_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculo`
--

CREATE TABLE `vehiculo` (
  `id` int(11) UNSIGNED NOT NULL,
  `conductor_id` int(11) UNSIGNED NOT NULL COMMENT 'FK al usuario responsable del vehículo',
  `placa` varchar(20) NOT NULL COMMENT 'Placa o matrícula del vehículo',
  `marca` varchar(50) NOT NULL,
  `modelo` varchar(50) NOT NULL,
  `anio` year(4) NOT NULL COMMENT 'Año de fabricación',
  `capacidad` int(3) NOT NULL COMMENT 'Capacidad de pasajeros',
  `estado` enum('Activo','Mantenimiento','Inactivo') NOT NULL DEFAULT 'Activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `color` varchar(50) NOT NULL DEFAULT 'Sin especificar'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculo`
--

INSERT INTO `vehiculo` (`id`, `conductor_id`, `placa`, `marca`, `modelo`, `anio`, `capacidad`, `estado`, `created_at`, `color`) VALUES
(1, 1, 'AB1232CD', 'Mitsubishi', 'Linux', '2008', 2, 'Activo', '2025-11-26 19:54:00', 'rojo'),
(3, 1, 'CC4848Df', 'Toyota', 'Corolla', '2017', 5, 'Activo', '2025-11-26 21:00:02', 'Sin especificar'),
(6, 1, '777opo8', 'wolswagen', 'corolla', '2017', 4, 'Inactivo', '2025-11-29 18:16:35', 'marron'),
(7, 2, 'fff888', 'toyo', 'carro', '1999', 8, 'Activo', '2025-12-16 18:46:02', 'Sin especificar'),
(8, 5, 'GGGGGGGg', 'mecano', 'Car', '2020', 3, 'Activo', '2026-01-08 22:47:15', 'Rojo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `vehiculo`
--
ALTER TABLE `vehiculo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `placa` (`placa`),
  ADD UNIQUE KEY `uk_placa` (`placa`),
  ADD KEY `fk_vehiculo_conductor` (`conductor_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `vehiculo`
--
ALTER TABLE `vehiculo`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `vehiculo`
--
ALTER TABLE `vehiculo`
  ADD CONSTRAINT `fk_vehiculo_conductor` FOREIGN KEY (`conductor_id`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
