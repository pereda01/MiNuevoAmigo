-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-11-2025 a las 16:18:29
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
-- Base de datos: `minuevoamigo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `adoptantes`
--

CREATE TABLE `adoptantes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `adoptantes`
--

INSERT INTO `adoptantes` (`id`, `nombre`, `apellidos`, `telefono`, `ciudad`) VALUES
(1, 'Londer Farid', 'Pereda Torres', '652463048', 'Avilés');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `animales`
--

CREATE TABLE `animales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('perro','gato','otro') NOT NULL,
  `edad_categoria` enum('cachorro','joven','adulto','mayor') NOT NULL,
  `sexo` enum('macho','hembra') NOT NULL,
  `raza` varchar(100) DEFAULT NULL,
  `tamano` enum('pequeño','mediano','grande') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `vacunado` tinyint(1) DEFAULT 0,
  `vacunas` text DEFAULT NULL,
  `esterilizado` tinyint(1) DEFAULT 0,
  `nivel_energia` enum('bajo','medio','alto') NOT NULL,
  `relacion_ninos` enum('excelente','buena','regular','mala') NOT NULL,
  `relacion_otros_animales` enum('excelente','buena','regular','mala') NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `peso` decimal(4,1) DEFAULT NULL,
  `necesidades_especiales` text DEFAULT NULL,
  `id_refugio` int(11) NOT NULL,
  `estado` enum('disponible','adoptado','pendiente') DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `animales`
--

INSERT INTO `animales` (`id`, `nombre`, `tipo`, `edad_categoria`, `sexo`, `raza`, `tamano`, `descripcion`, `vacunado`, `vacunas`, `esterilizado`, `nivel_energia`, `relacion_ninos`, `relacion_otros_animales`, `fecha_nacimiento`, `peso`, `necesidades_especiales`, `id_refugio`, `estado`) VALUES
(1, 'Lola', 'perro', 'adulto', 'hembra', 'Mestizo', 'grande', 'Lola es muy tranquila, tiene bastante edad y a pesar de ello no pierde las ganas de jugar. Come un montón y le gusta estar con su hermana Laika.', 1, '', 0, 'bajo', 'excelente', 'regular', NULL, 40.0, '', 3, 'disponible'),
(2, 'Timi', 'gato', 'adulto', 'macho', 'Atigrado', 'mediano', 'Timi es un gato muy dormilón, le encanta jugar y come como un desgraciado.', 1, 'Rabia y polivalente', 1, 'medio', 'buena', 'buena', NULL, 5.0, '', 4, 'disponible'),
(3, 'Tambor', 'otro', 'cachorro', 'macho', 'Conejo Toy', 'pequeño', 'Tambor es un conejito pequeñito de apenas un año, es muy juguetón con las personas de su circulo cercano. \r\nCuando esta feliz da saltitos de alegría.', 1, 'Rabia, Moquillo', 0, 'alto', 'mala', 'mala', NULL, 1.0, 'De noche tiene que estar bien abrigado con su heno, no puede pasar frio de ninguna manera.', 5, 'disponible'),
(4, 'Kiara', 'perro', 'joven', 'hembra', 'Boxer', 'mediano', 'Kiara es una bóxer extremadamente juguetona, no para quieta, mas que para comer y dormir.\r\nSobretodo es muy cariñosa con todo el mundo.', 1, 'Rabia, Polivalente.', 1, 'alto', 'excelente', 'excelente', NULL, 20.0, 'Al ser tan juguetona, lo ideal seria que en su hogar tuviera una finca o jardín donde pueda tener su espacio para descargar la gran energía que tiene.', 6, 'disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotos_animales`
--

CREATE TABLE `fotos_animales` (
  `id` int(11) NOT NULL,
  `id_animal` int(11) NOT NULL,
  `ruta_foto` varchar(255) NOT NULL,
  `es_principal` tinyint(1) DEFAULT 0,
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `fotos_animales`
--

INSERT INTO `fotos_animales` (`id`, `id_animal`, `ruta_foto`, `es_principal`, `orden`) VALUES
(1, 1, '69173a765611c_WhatsApp Image 2025-11-14 at 11.09.52 (1).jpeg', 1, 0),
(2, 2, '691a06ed3ff3b_WhatsApp Image 2025-10-13 at 11.41.44.jpeg', 1, 0),
(3, 2, '691a06ed41583_WhatsApp Image 2025-10-13 at 11.41.17.jpeg', 0, 1),
(4, 2, '691a06ed41d1b_WhatsApp Image 2025-10-13 at 11.41.17 (2).jpeg', 0, 2),
(5, 2, '691a06ed42816_WhatsApp Image 2025-10-13 at 11.41.17 (1).jpeg', 0, 3),
(6, 3, '691b94e4de25a_IMG_20190612_214924.jpg', 1, 0),
(7, 3, '691b94e4debe4_IMG_20190525_161117.jpg', 0, 1),
(8, 3, '691b94e4df4f7_IMG_20190508_190756.jpg', 0, 2),
(9, 4, '691b97b8b5dd9_WhatsApp Image 2025-11-17 at 20.26.18.jpeg', 1, 0),
(10, 4, '691b97b8b64ed_WhatsApp Image 2025-11-16 at 12.24.22.jpeg', 0, 1),
(11, 4, '691b97b8b6e3d_WhatsApp Image 2025-11-17 at 20.26.18 (1).jpeg', 0, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `refugios`
--

CREATE TABLE `refugios` (
  `id` int(11) NOT NULL,
  `nombre_refugio` varchar(200) NOT NULL,
  `nombre_contacto` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `refugios`
--

INSERT INTO `refugios` (`id`, `nombre_refugio`, `nombre_contacto`, `telefono`, `direccion`, `ciudad`, `descripcion`) VALUES
(2, 'DonPolloSave', 'Don Pollo', '23543254325324', NULL, 'DonPollolandia', NULL),
(3, 'FincaChacon', 'Yohan Chacon Sánchez', '123456789', NULL, 'Avilés', NULL),
(4, 'Raquel Safe', 'Raquel Hermida', '123456789', NULL, 'Avilés', NULL),
(5, 'AaronSafe', 'Londer Aaron Pereda Torres', '123456789', NULL, 'Avilés', NULL),
(6, 'Rossy Safe', 'Rosalynn Torres', '123456789', NULL, 'Avilés', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_adopcion`
--

CREATE TABLE `solicitudes_adopcion` (
  `id` int(11) NOT NULL,
  `id_animal` int(11) NOT NULL,
  `id_adoptante` int(11) NOT NULL,
  `estado` enum('pendiente','aceptada','rechazada','cancelada') DEFAULT 'pendiente',
  `fecha_solicitud` timestamp NULL DEFAULT current_timestamp(),
  `fecha_resolucion` timestamp NULL DEFAULT NULL,
  `mensaje_adoptante` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tipo` enum('adoptante','refugio') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `email`, `password`, `tipo`) VALUES
(1, 'Pereda', 'londerfarid@gmail.com', '$2y$10$G/wBtO3pZPjmq8JmHQer3uWFAst0qu2gxMIwFjTAXoQltuPag3fi2', 'adoptante'),
(2, 'DonPollo', 'donpollo@gmail.com', '$2y$10$JixKdxlReO4JXMzAG.U6qu4fyvpMlw66yb7F9C2Xm5yfy1tpISUzK', 'refugio'),
(3, 'Zombyra', 'yohan@gmail.com', '$2y$10$ub3Vm6u.pWU0vxYlNHqJpubsUjE5f/.ClJ7L93MwAqCCO5Eh6EfT2', 'refugio'),
(4, 'Raquel', 'raquel12345@gmail.com', '$2y$10$L4NdmUEe2oec8MpXdFswS.t14vD9NupNF0RB8HcWJk.hNSRZO2/8K', 'refugio'),
(5, 'Aaron', 'aaron@gmail.com', '$2y$10$El33RyK0yL0WcOLl/Jtoyu65Gp5N0NCy7T9Tptfan/2AyAjJXbghq', 'refugio'),
(6, 'Rossy', 'rossy@gmail.com', '$2y$10$cWSzuHQjg5uw8KQspBaxueXlxdVEA885dmuZWD/7OCpOl6Era41Kq', 'refugio');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `adoptantes`
--
ALTER TABLE `adoptantes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `animales`
--
ALTER TABLE `animales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_animales_usuarios_idx` (`id_refugio`);

--
-- Indices de la tabla `fotos_animales`
--
ALTER TABLE `fotos_animales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fotos_animales_animales_idx` (`id_animal`);

--
-- Indices de la tabla `refugios`
--
ALTER TABLE `refugios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `solicitudes_adopcion`
--
ALTER TABLE `solicitudes_adopcion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_solicitudes_animales_idx` (`id_animal`),
  ADD KEY `fk_solicitudes_usuarios_idx` (`id_adoptante`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username_UNIQUE` (`username`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `animales`
--
ALTER TABLE `animales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `fotos_animales`
--
ALTER TABLE `fotos_animales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `solicitudes_adopcion`
--
ALTER TABLE `solicitudes_adopcion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `adoptantes`
--
ALTER TABLE `adoptantes`
  ADD CONSTRAINT `fk_adoptantes_usuarios` FOREIGN KEY (`id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `animales`
--
ALTER TABLE `animales`
  ADD CONSTRAINT `fk_animales_usuarios` FOREIGN KEY (`id_refugio`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `fotos_animales`
--
ALTER TABLE `fotos_animales`
  ADD CONSTRAINT `fk_fotos_animales_animales` FOREIGN KEY (`id_animal`) REFERENCES `animales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `refugios`
--
ALTER TABLE `refugios`
  ADD CONSTRAINT `fk_refugios_usuarios` FOREIGN KEY (`id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitudes_adopcion`
--
ALTER TABLE `solicitudes_adopcion`
  ADD CONSTRAINT `fk_solicitudes_animales` FOREIGN KEY (`id_animal`) REFERENCES `animales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_solicitudes_usuarios` FOREIGN KEY (`id_adoptante`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
