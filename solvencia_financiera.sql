-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-01-2026 a las 00:36:13
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

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `solvencia_financiera`
--
ALTER TABLE `solvencia_financiera`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_mes_anio` (`usuario_id`,`anio`,`mes`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `solvencia_financiera`
--
ALTER TABLE `solvencia_financiera`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
