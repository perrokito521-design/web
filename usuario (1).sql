-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-01-2026 a las 00:36:32
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

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
