-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-01-2026 a las 00:35:48
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
-- Estructura de tabla para la tabla `conductores`
--

CREATE TABLE `conductores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `licencia_tipo` varchar(50) NOT NULL,
  `licencia_emision` date NOT NULL,
  `licencia_vencimiento` date NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `conductores`
--

INSERT INTO `conductores` (`id`, `nombre`, `apellido`, `cedula`, `licencia_tipo`, `licencia_emision`, `licencia_vencimiento`, `usuario_id`, `created_at`) VALUES
(1, 'Juan', 'Perez', 'V-30652914', 'D1', '2026-01-01', '2026-01-30', 1, '2026-01-08 22:42:31'),
(2, 'Yal', 'Belis', 'V-18599761', 'G', '2026-01-03', '2026-01-21', 5, '2026-01-08 22:48:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solvencia_financiera`
--

CREATE TABLE `solvencia_financiera` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `anio` year(4) NOT NULL,
  `mes` tinyint(4) NOT NULL CHECK (`mes` between 1 and 12),
  `estado` enum('PENDIENTE','PAGADO','ATRASADO') NOT NULL DEFAULT 'PENDIENTE',
  `monto` decimal(10,2) DEFAULT 0.00,
  `fecha_pago` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solvencia_financiera`
--

INSERT INTO `solvencia_financiera` (`id`, `usuario_id`, `anio`, `mes`, `estado`, `monto`, `fecha_pago`, `created_at`, `updated_at`) VALUES
(2, 1, '2026', 1, 'PENDIENTE', 50.00, '2026-01-16', '2026-01-13 02:37:32', '2026-01-13 02:37:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solvencia_fiscal`
--

CREATE TABLE `solvencia_fiscal` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `dia_semana` tinyint(4) NOT NULL CHECK (`dia_semana` between 1 and 7),
  `semana` varchar(7) NOT NULL,
  `estado` enum('PENDIENTE','CUMPLIDO','AUSENTE') NOT NULL DEFAULT 'PENDIENTE',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL COMMENT 'Correo electrónico (usado para login)',
  `password_hash` varchar(255) NOT NULL COMMENT 'Hash de la contraseña',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rol` enum('ADMIN','SOCIO','AFILIADO') NOT NULL DEFAULT 'SOCIO' COMMENT 'Rol del usuario en el sistema: ADMIN, SOCIO, AFILIADO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `email`, `password_hash`, `created_at`, `rol`) VALUES
(1, 'samu12meza@gmail.com', '$2y$10$v240nFJ4z.KfupOWakjAoenV1oARbIweKgkhLpJdBwqewquLcq0C6', '2025-11-26 17:55:54', 'ADMIN'),
(2, 'hernest@gmail.com', '$2y$10$vdrf2YAtB4f58AUCrPS1Sup9WgHYo494.tassHcZx8hZml1yy6BTy', '2025-12-16 18:44:53', 'AFILIADO'),
(3, 'ricardo@gmail.com', '$2y$10$kL3TK0FwlquEvWTLvpn6h.rmHMvix1.TD1iZ.Jq2L1dDR9QSsPFVO', '2025-12-16 19:02:27', 'SOCIO'),
(5, 'yal@gmail.com', '$2y$10$XA1zcQQmTvV2.SdXO9z6BOZzfV6X4601jr1HCHQuSMjtUmlzvsPCa', '2026-01-08 22:46:35', 'AFILIADO'),
(7, 'jose@gmail.com', '$2y$10$9p4AEnsl53Hj65glJAfsuedqaf8h/nGGVmtWXmunXwkRiKJST1EMe', '2026-01-12 20:49:35', 'SOCIO'),
(8, 'leidi@gmail.com', '$2y$10$hEInHfONA67fZ8m2K0duQe/KkG.lhwe73zKYvCVpKoTzPFVnt1CAW', '2026-01-12 20:58:25', 'AFILIADO');

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
-- Indices de la tabla `conductores`
--
ALTER TABLE `conductores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `solvencia_financiera`
--
ALTER TABLE `solvencia_financiera`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_mes_anio` (`usuario_id`,`anio`,`mes`);

--
-- Indices de la tabla `solvencia_fiscal`
--
ALTER TABLE `solvencia_fiscal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_dia_semana` (`usuario_id`,`dia_semana`,`semana`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

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
-- AUTO_INCREMENT de la tabla `conductores`
--
ALTER TABLE `conductores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `solvencia_financiera`
--
ALTER TABLE `solvencia_financiera`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `solvencia_fiscal`
--
ALTER TABLE `solvencia_fiscal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
