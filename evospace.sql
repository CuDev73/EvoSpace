-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 26, 2026 at 09:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `evospace`
--

-- --------------------------------------------------------

--
-- Table structure for table `alumnos`
--

CREATE TABLE `alumnos` (
  `id_alumno` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `anio_ingreso` year(4) NOT NULL,
  `horas_profesionales` decimal(6,2) DEFAULT 0.00,
  `ci` varchar(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `id_padre` int(11) DEFAULT NULL,
  `becado` tinyint(1) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `alumnos`
--

INSERT INTO `alumnos` (`id_alumno`, `nombre`, `apellido`, `id_curso`, `anio_ingreso`, `horas_profesionales`, `ci`, `telefono`, `id_padre`, `becado`, `activo`, `fecha_creacion`) VALUES
(1, 'Mariela', 'Nuñez Esteche', 20, '2022', 1312.00, '283892', '254234', 3, 0, 1, '2026-06-21 20:00:21'),
(2, 'Natan', 'Levy', 1, '2023', 0.00, '123456', '098765', 3, 1, 1, '2026-06-21 20:00:21'),
(3, 'Clara', 'Vallejos', 9, '2024', 0.00, '654321', '099888', NULL, 0, 1, '2026-06-21 20:00:21'),
(4, 'Jessica', 'Giménez', 18, '2021', 800.00, '111222', '456789', NULL, 1, 1, '2026-06-21 20:00:21'),
(5, 'Carlos', 'Ruiz', 2, '2023', 0.00, '987654', '555123', 9, 0, 1, '2026-06-21 20:00:21'),
(6, 'Sofía', 'Martínez', 14, '2024', 0.00, '456123', '555456', 3, 0, 1, '2026-06-21 20:00:21');

-- --------------------------------------------------------

--
-- Table structure for table `asistencia`
--

CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL,
  `id_alumno` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `presente` tinyint(1) DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asistencia`
--

INSERT INTO `asistencia` (`id_asistencia`, `id_alumno`, `id_curso`, `fecha`, `presente`, `observaciones`, `fecha_creacion`) VALUES
(1, 1, 20, '2026-05-24', 0, 'Marcación de mañana, baile del san juan', '2026-06-21 20:00:21'),
(2, 2, 1, '2026-05-24', 1, '', '2026-06-21 20:00:21'),
(3, 3, 9, '2026-05-24', 0, 'Llegó tarde', '2026-06-21 20:00:21');

-- --------------------------------------------------------

--
-- Table structure for table `cantina`
--

CREATE TABLE `cantina` (
  `id_compra` int(11) NOT NULL,
  `id_alumno` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `producto` varchar(200) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `pagado` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cantina`
--

INSERT INTO `cantina` (`id_compra`, `id_alumno`, `fecha`, `producto`, `monto`, `pagado`, `fecha_creacion`) VALUES
(1, 1, '2026-07-15', 'Empanada', 5000.00, 0, '2026-06-21 20:00:21'),
(2, 2, '2026-07-15', 'Gaseosa', 2500.00, 1, '2026-06-21 20:00:21');

-- --------------------------------------------------------

--
-- Table structure for table `configuracion`
--

CREATE TABLE `configuracion` (
  `clave` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `configuracion`
--

INSERT INTO `configuracion` (`clave`, `valor`) VALUES
('porcentaje_beca', '50');

-- --------------------------------------------------------

--
-- Table structure for table `cuotas_config`
--

CREATE TABLE `cuotas_config` (
  `id_curso` int(11) NOT NULL,
  `monto_base` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento_beca` decimal(5,2) DEFAULT 45.45
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cuotas_config`
--

INSERT INTO `cuotas_config` (`id_curso`, `monto_base`, `descuento_beca`) VALUES
(1, 2500.00, 45.45),
(2, 2500.00, 45.45),
(3, 2500.00, 45.45),
(4, 2500.00, 45.45),
(5, 2500.00, 45.45),
(6, 2500.00, 45.45),
(7, 3000.00, 45.45),
(8, 3000.00, 45.45),
(9, 3000.00, 45.45),
(10, 3000.00, 45.45),
(11, 3000.00, 45.45),
(12, 3000.00, 45.45),
(13, 3000.00, 45.45),
(14, 3000.00, 45.45),
(15, 3500.00, 45.45),
(16, 3500.00, 45.45),
(17, 3500.00, 45.45),
(18, 3500.00, 45.45),
(19, 3500.00, 45.45),
(20, 3500.00, 45.45),
(21, 3500.00, 45.45);

-- --------------------------------------------------------

--
-- Table structure for table `cursos`
--

CREATE TABLE `cursos` (
  `id_curso` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('Acrotelas','Infantil','Superior') NOT NULL,
  `orden` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cursos`
--

INSERT INTO `cursos` (`id_curso`, `nombre`, `tipo`, `orden`, `activo`) VALUES
(1, 'Inicial', 'Acrotelas', 1, 1),
(2, 'Primer Curso', 'Acrotelas', 2, 1),
(3, 'Segundo Curso', 'Acrotelas', 3, 1),
(4, 'Tercer Curso', 'Acrotelas', 4, 1),
(5, 'Cuarto Curso', 'Acrotelas', 5, 1),
(6, 'Quinto Curso', 'Acrotelas', 6, 1),
(7, 'Nivel Inicial I', 'Infantil', 1, 1),
(8, 'Nivel Inicial II', 'Infantil', 2, 1),
(9, 'Primer Grado', 'Infantil', 3, 1),
(10, 'Segundo Grado', 'Infantil', 4, 1),
(11, 'Tercer Grado', 'Infantil', 5, 1),
(12, 'Cuarto Grado', 'Infantil', 6, 1),
(13, 'Quinto Grado', 'Infantil', 7, 1),
(14, 'Sexto Grado', 'Infantil', 8, 1),
(15, 'Principiante Superior', 'Superior', 1, 1),
(16, 'Preparatorio Superior', 'Superior', 2, 1),
(17, 'Primer Curso', 'Superior', 3, 1),
(18, 'Segundo Curso', 'Superior', 4, 1),
(19, 'Tercer Curso', 'Superior', 5, 1),
(20, 'Cuarto Curso', 'Superior', 6, 1),
(21, 'Quinto Curso', 'Superior', 7, 1);

-- --------------------------------------------------------

--
-- Table structure for table `eventos`
--

CREATE TABLE `eventos` (
  `id_evento` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `lugar` varchar(200) DEFAULT NULL,
  `enlace_ubicacion` varchar(255) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#c81015',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `eventos`
--

INSERT INTO `eventos` (`id_evento`, `titulo`, `descripcion`, `fecha`, `hora`, `lugar`, `enlace_ubicacion`, `color`, `fecha_creacion`) VALUES
(1, 'Sesión de fotos para la obra', 'Llevar polleras, sombreros y utilería.', '2026-08-13', '10:00:00', 'Tal parte Avda. Lalaland c/12 de junio', 'https://maps.google.com', '#c81015', '2026-06-21 20:00:21'),
(2, 'Grabación de Mentiras', 'Ensayo general a las 18:00 hs.', '2026-08-20', '18:00:00', 'Teatro Municipal', NULL, '#c00ef1', '2026-06-21 20:00:21');

-- --------------------------------------------------------

--
-- Table structure for table `evento_curso`
--

CREATE TABLE `evento_curso` (
  `id_evento` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evento_curso`
--

INSERT INTO `evento_curso` (`id_evento`, `id_curso`) VALUES
(1, 1),
(1, 17),
(2, 1),
(2, 9),
(2, 20);

-- --------------------------------------------------------

--
-- Table structure for table `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_evento` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `mensaje` text DEFAULT NULL,
  `tipo` enum('evento','pago','general') DEFAULT 'evento',
  `leida` tinyint(1) DEFAULT 0,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_alumno` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `concepto` varchar(200) NOT NULL,
  `cantidad` int(11) DEFAULT 1,
  `monto` decimal(10,2) NOT NULL,
  `descuento` decimal(5,2) DEFAULT 0.00,
  `recargo` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `metodo_pago` enum('Efectivo','Transferencia','Tarjeta','Otro') DEFAULT 'Efectivo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_alumno`, `fecha`, `concepto`, `cantidad`, `monto`, `descuento`, `recargo`, `total`, `metodo_pago`, `fecha_creacion`) VALUES
(1, 2, '2026-07-15', 'Cuota mensual', 1, 2500.00, 45.45, 2000.00, 3363.75, 'Efectivo', '2026-06-21 20:00:21'),
(2, 1, '2026-07-12', 'Cuota mensual', 1, 2432.00, 0.00, 0.00, 2432.00, 'Efectivo', '2026-06-21 20:00:21'),
(3, 1, '2026-07-01', 'Entradas', 5, 131.00, 0.00, 0.00, 655.00, 'Transferencia', '2026-06-21 20:00:21'),
(4, 1, '2026-12-04', 'Folleto de poesías', 1, 1234.00, 0.00, 0.00, 1234.00, 'Efectivo', '2026-06-21 20:00:21'),
(5, 4, '2026-06-01', 'Cuota', 1, 150000.00, 0.00, 21000.00, 171000.00, 'Efectivo', '2026-06-21 20:09:39'),
(6, 4, '2026-06-01', 'Cuota', 1, 150000.00, 0.00, 21000.00, 171000.00, 'Efectivo', '2026-06-21 20:12:49'),
(7, 4, '2026-06-01', 'Cuota', 1, 150000.00, 0.00, 21000.00, 171000.00, 'Efectivo', '2026-06-21 20:13:57'),
(8, 1, '2026-06-21', 'Cuota', 1, 150000.00, 0.00, 11000.00, 161000.00, 'Efectivo', '2026-06-21 20:17:16'),
(9, 1, '2026-06-01', 'Cuota mensual', 1, 3500.00, 0.00, 0.00, 3500.00, 'Efectivo', '2026-06-21 20:27:27'),
(10, 2, '2026-06-01', 'Cuota mensual', 1, 2500.00, 45.45, 0.00, 1363.75, 'Efectivo', '2026-06-21 20:27:27'),
(11, 3, '2026-06-01', 'Cuota mensual', 1, 3000.00, 0.00, 0.00, 3000.00, 'Efectivo', '2026-06-21 20:27:27'),
(12, 4, '2026-06-01', 'Cuota mensual', 1, 3500.00, 45.45, 0.00, 1909.25, 'Efectivo', '2026-06-21 20:27:27'),
(13, 5, '2026-06-01', 'Cuota mensual', 1, 2500.00, 0.00, 0.00, 2500.00, 'Efectivo', '2026-06-21 20:27:27'),
(14, 6, '2026-06-01', 'Cuota mensual', 1, 3000.00, 0.00, 0.00, 3000.00, 'Efectivo', '2026-06-21 20:27:27'),
(15, 1, '2026-06-01', 'Cuota mensual', 1, 3500.00, 0.00, 0.00, 3500.00, 'Efectivo', '2026-06-21 20:32:21'),
(16, 2, '2026-06-01', 'Cuota mensual', 1, 2500.00, 45.45, 0.00, 1363.75, 'Efectivo', '2026-06-21 20:32:21'),
(17, 3, '2026-06-01', 'Cuota mensual', 1, 3000.00, 0.00, 0.00, 3000.00, 'Efectivo', '2026-06-21 20:32:21'),
(18, 4, '2026-06-01', 'Cuota mensual', 1, 3500.00, 45.45, 0.00, 1909.25, 'Efectivo', '2026-06-21 20:32:21'),
(19, 5, '2026-06-01', 'Cuota mensual', 1, 2500.00, 0.00, 0.00, 2500.00, 'Efectivo', '2026-06-21 20:32:21'),
(20, 6, '2026-06-01', 'Cuota mensual', 1, 3000.00, 0.00, 0.00, 3000.00, 'Efectivo', '2026-06-21 20:32:21'),
(21, 1, '2026-06-01', 'Cuota mensual', 1, 3500.00, 0.00, 0.00, 3500.00, 'Efectivo', '2026-06-21 20:32:56'),
(22, 2, '2026-06-01', 'Cuota mensual', 1, 2500.00, 45.45, 0.00, 1363.75, 'Efectivo', '2026-06-21 20:32:56'),
(23, 3, '2026-06-01', 'Cuota mensual', 1, 3000.00, 0.00, 0.00, 3000.00, 'Efectivo', '2026-06-21 20:32:56'),
(24, 4, '2026-06-01', 'Cuota mensual', 1, 3500.00, 45.45, 0.00, 1909.25, 'Efectivo', '2026-06-21 20:32:56'),
(25, 5, '2026-06-01', 'Cuota mensual', 1, 2500.00, 0.00, 0.00, 2500.00, 'Efectivo', '2026-06-21 20:32:56'),
(26, 6, '2026-06-01', 'Cuota mensual', 1, 3000.00, 0.00, 0.00, 3000.00, 'Efectivo', '2026-06-21 20:32:56'),
(27, 1, '2026-06-01', 'Cuota mensual', 1, 3500.00, 0.00, 0.00, 3500.00, 'Efectivo', '2026-06-21 20:33:13'),
(28, 2, '2026-06-01', 'Cuota mensual', 1, 2500.00, 45.45, 0.00, 1363.75, 'Efectivo', '2026-06-21 20:33:13'),
(29, 3, '2026-06-01', 'Cuota mensual', 1, 3000.00, 0.00, 0.00, 3000.00, 'Efectivo', '2026-06-21 20:33:13'),
(30, 4, '2026-06-01', 'Cuota mensual', 1, 3500.00, 45.45, 0.00, 1909.25, 'Efectivo', '2026-06-21 20:33:13'),
(31, 5, '2026-06-01', 'Cuota mensual', 1, 2500.00, 0.00, 0.00, 2500.00, 'Efectivo', '2026-06-21 20:33:13'),
(32, 6, '2026-06-01', 'Cuota mensual', 1, 3000.00, 0.00, 0.00, 3000.00, 'Efectivo', '2026-06-21 20:33:13'),
(33, 4, '2026-06-21', 'matrícula', 1, 180000.00, 0.00, 0.00, 180000.00, 'Efectivo', '2026-06-21 23:09:21');

-- --------------------------------------------------------

--
-- Table structure for table `precios`
--

CREATE TABLE `precios` (
  `id_precio` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `concepto` varchar(50) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `descuento_beca` decimal(5,2) DEFAULT 0.00,
  `aplica_beca` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `precios`
--

INSERT INTO `precios` (`id_precio`, `id_curso`, `concepto`, `precio`, `descuento_beca`, `aplica_beca`) VALUES
(1, 1, 'matrícula', 150000.00, 0.00, 0),
(2, 2, 'matrícula', 150000.00, 0.00, 0),
(3, 3, 'matrícula', 150000.00, 0.00, 0),
(4, 4, 'matrícula', 150000.00, 0.00, 0),
(5, 5, 'matrícula', 150000.00, 0.00, 0),
(6, 6, 'matrícula', 150000.00, 0.00, 0),
(8, 1, 'cuota', 220000.00, 45.45, 1),
(9, 2, 'cuota', 200000.00, 45.45, 1),
(10, 3, 'cuota', 200000.00, 45.45, 1),
(11, 4, 'cuota', 200000.00, 45.45, 1),
(12, 5, 'cuota', 200000.00, 45.45, 1),
(13, 6, 'cuota', 200000.00, 45.45, 1),
(15, 1, 'vestuarios', 150000.00, 0.00, 0),
(16, 2, 'vestuarios', 150000.00, 0.00, 0),
(17, 3, 'vestuarios', 150000.00, 0.00, 0),
(18, 4, 'vestuarios', 150000.00, 0.00, 0),
(19, 5, 'vestuarios', 150000.00, 0.00, 0),
(20, 6, 'vestuarios', 150000.00, 0.00, 0),
(22, 1, 'entradas', 80000.00, 0.00, 0),
(23, 2, 'entradas', 80000.00, 0.00, 0),
(24, 3, 'entradas', 80000.00, 0.00, 0),
(25, 4, 'entradas', 80000.00, 0.00, 0),
(26, 5, 'entradas', 80000.00, 0.00, 0),
(27, 6, 'entradas', 80000.00, 0.00, 0),
(29, 7, 'matrícula', 180000.00, 0.00, 0),
(30, 8, 'matrícula', 180000.00, 0.00, 0),
(31, 9, 'matrícula', 180000.00, 0.00, 0),
(32, 10, 'matrícula', 180000.00, 0.00, 0),
(33, 11, 'matrícula', 180000.00, 0.00, 0),
(34, 12, 'matrícula', 180000.00, 0.00, 0),
(35, 13, 'matrícula', 180000.00, 0.00, 0),
(36, 14, 'matrícula', 180000.00, 0.00, 0),
(44, 7, 'cuota', 220000.00, 45.45, 1),
(45, 8, 'cuota', 220000.00, 45.45, 1),
(46, 9, 'cuota', 220000.00, 45.45, 1),
(47, 10, 'cuota', 220000.00, 45.45, 1),
(48, 11, 'cuota', 220000.00, 45.45, 1),
(49, 12, 'cuota', 220000.00, 45.45, 1),
(50, 13, 'cuota', 220000.00, 45.45, 1),
(51, 14, 'cuota', 220000.00, 45.45, 1),
(59, 7, 'vestuarios', 150000.00, 0.00, 0),
(60, 8, 'vestuarios', 150000.00, 0.00, 0),
(61, 9, 'vestuarios', 150000.00, 0.00, 0),
(62, 10, 'vestuarios', 150000.00, 0.00, 0),
(63, 11, 'vestuarios', 150000.00, 0.00, 0),
(64, 12, 'vestuarios', 150000.00, 0.00, 0),
(65, 13, 'vestuarios', 150000.00, 0.00, 0),
(66, 14, 'vestuarios', 150000.00, 0.00, 0),
(74, 7, 'entradas', 80000.00, 0.00, 0),
(75, 8, 'entradas', 80000.00, 0.00, 0),
(76, 9, 'entradas', 80000.00, 0.00, 0),
(77, 10, 'entradas', 80000.00, 0.00, 0),
(78, 11, 'entradas', 80000.00, 0.00, 0),
(79, 12, 'entradas', 80000.00, 0.00, 0),
(80, 13, 'entradas', 80000.00, 0.00, 0),
(81, 14, 'entradas', 80000.00, 0.00, 0),
(89, 7, 'folleto', 25000.00, 0.00, 0),
(90, 8, 'folleto', 25000.00, 0.00, 0),
(91, 9, 'folleto', 25000.00, 0.00, 0),
(92, 10, 'folleto', 25000.00, 0.00, 0),
(93, 11, 'folleto', 25000.00, 0.00, 0),
(94, 12, 'folleto', 25000.00, 0.00, 0),
(95, 13, 'folleto', 25000.00, 0.00, 0),
(96, 14, 'folleto', 25000.00, 0.00, 0),
(104, 15, 'matrícula', 180000.00, 0.00, 0),
(105, 16, 'matrícula', 180000.00, 0.00, 0),
(106, 17, 'matrícula', 180000.00, 0.00, 0),
(107, 18, 'matrícula', 180000.00, 0.00, 0),
(108, 19, 'matrícula', 180000.00, 0.00, 0),
(109, 20, 'matrícula', 180000.00, 0.00, 0),
(110, 21, 'matrícula', 180000.00, 0.00, 0),
(111, 15, 'cuota', 250000.00, 45.45, 1),
(112, 16, 'cuota', 250000.00, 45.45, 1),
(113, 17, 'cuota', 250000.00, 45.45, 1),
(114, 18, 'cuota', 250000.00, 45.45, 1),
(115, 19, 'cuota', 250000.00, 45.45, 1),
(116, 20, 'cuota', 250000.00, 45.45, 1),
(117, 21, 'cuota', 250000.00, 45.45, 1),
(118, 15, 'vestuarios', 150000.00, 0.00, 0),
(119, 16, 'vestuarios', 150000.00, 0.00, 0),
(120, 17, 'vestuarios', 150000.00, 0.00, 0),
(121, 18, 'vestuarios', 150000.00, 0.00, 0),
(122, 19, 'vestuarios', 150000.00, 0.00, 0),
(123, 20, 'vestuarios', 150000.00, 0.00, 0),
(124, 21, 'vestuarios', 150000.00, 0.00, 0),
(125, 15, 'entradas', 80000.00, 0.00, 0),
(126, 16, 'entradas', 80000.00, 0.00, 0),
(127, 17, 'entradas', 80000.00, 0.00, 0),
(128, 18, 'entradas', 80000.00, 0.00, 0),
(129, 19, 'entradas', 80000.00, 0.00, 0),
(130, 20, 'entradas', 80000.00, 0.00, 0),
(131, 21, 'entradas', 80000.00, 0.00, 0),
(132, 1, 'folleto', 0.00, 0.00, 0),
(133, 2, 'folleto', 0.00, 0.00, 0),
(134, 3, 'folleto', 0.00, 0.00, 0),
(135, 4, 'folleto', 0.00, 0.00, 0),
(136, 5, 'folleto', 0.00, 0.00, 0),
(137, 6, 'folleto', 0.00, 0.00, 0),
(139, 15, 'folleto', 0.00, 45.45, 0),
(140, 16, 'folleto', 0.00, 45.45, 0),
(141, 17, 'folleto', 0.00, 45.45, 0),
(142, 18, 'folleto', 0.00, 45.45, 0),
(143, 19, 'folleto', 0.00, 45.45, 0),
(144, 20, 'folleto', 0.00, 45.45, 0),
(145, 21, 'folleto', 0.00, 45.45, 0);

-- --------------------------------------------------------

--
-- Table structure for table `profesores`
--

CREATE TABLE `profesores` (
  `id_profesor` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `salario_base` decimal(10,2) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profesores`
--

INSERT INTO `profesores` (`id_profesor`, `id_usuario`, `especialidad`, `salario_base`, `fecha_contratacion`, `activo`) VALUES
(1, 2, 'Danza Clásica', 500000.00, '2024-03-01', 1);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('admin','profesor','padre') NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `usuario`, `email`, `cedula`, `password_hash`, `rol`, `activo`, `fecha_creacion`) VALUES
(1, 'admin', 'admin@evospace.com', '1234567', '$2y$10$LBSyD2UFwBLJA/G1i4CRh.pVZJ/q/n2zhkSGNilT5OxM6IK3ccyBC', 'admin', 1, '2026-06-21 20:00:21'),
(2, 'profesor', 'profe@evospace.com', '2345678', '$2y$10$19GT94aaJLh/UxAhesrC9OwP5oY57xYuzes4FJeHSsUtyhyv7Rp3q', 'profesor', 1, '2026-06-21 20:00:21'),
(3, 'padre', 'padre@evospace.com', '3456789', '$2y$10$XobVID8qYtFtqHFsbL3XKuBuoiji5c3s1po8FWLYXXyi9oLSwjL1u', 'padre', 1, '2026-06-21 20:00:21'),
(9, 'jose12', 'jose123@gmail.com', '7007909', '$2y$10$KvmseauuvknNUZKn4vuZXuNOS2TdpSczAZAk/stvOMt/mfY.WfUOW', 'padre', 1, '2026-06-22 23:57:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alumnos`
--
ALTER TABLE `alumnos`
  ADD PRIMARY KEY (`id_alumno`),
  ADD UNIQUE KEY `ci` (`ci`),
  ADD KEY `id_curso` (`id_curso`),
  ADD KEY `id_padre` (`id_padre`);

--
-- Indexes for table `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD UNIQUE KEY `id_alumno` (`id_alumno`,`fecha`),
  ADD KEY `id_curso` (`id_curso`);

--
-- Indexes for table `cantina`
--
ALTER TABLE `cantina`
  ADD PRIMARY KEY (`id_compra`),
  ADD KEY `id_alumno` (`id_alumno`);

--
-- Indexes for table `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`clave`);

--
-- Indexes for table `cuotas_config`
--
ALTER TABLE `cuotas_config`
  ADD PRIMARY KEY (`id_curso`);

--
-- Indexes for table `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id_curso`);

--
-- Indexes for table `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id_evento`);

--
-- Indexes for table `evento_curso`
--
ALTER TABLE `evento_curso`
  ADD PRIMARY KEY (`id_evento`,`id_curso`),
  ADD KEY `id_curso` (`id_curso`);

--
-- Indexes for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_evento` (`id_evento`),
  ADD KEY `id_curso` (`id_curso`);

--
-- Indexes for table `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_alumno` (`id_alumno`);

--
-- Indexes for table `precios`
--
ALTER TABLE `precios`
  ADD PRIMARY KEY (`id_precio`),
  ADD UNIQUE KEY `id_curso` (`id_curso`,`concepto`);

--
-- Indexes for table `profesores`
--
ALTER TABLE `profesores`
  ADD PRIMARY KEY (`id_profesor`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alumnos`
--
ALTER TABLE `alumnos`
  MODIFY `id_alumno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cantina`
--
ALTER TABLE `cantina`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id_curso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `precios`
--
ALTER TABLE `precios`
  MODIFY `id_precio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `profesores`
--
ALTER TABLE `profesores`
  MODIFY `id_profesor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alumnos`
--
ALTER TABLE `alumnos`
  ADD CONSTRAINT `alumnos_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`),
  ADD CONSTRAINT `alumnos_ibfk_2` FOREIGN KEY (`id_padre`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL;

--
-- Constraints for table `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`) ON DELETE CASCADE,
  ADD CONSTRAINT `asistencia_ibfk_2` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`);

--
-- Constraints for table `cantina`
--
ALTER TABLE `cantina`
  ADD CONSTRAINT `cantina_ibfk_1` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`) ON DELETE CASCADE;

--
-- Constraints for table `cuotas_config`
--
ALTER TABLE `cuotas_config`
  ADD CONSTRAINT `cuotas_config_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE;

--
-- Constraints for table `evento_curso`
--
ALTER TABLE `evento_curso`
  ADD CONSTRAINT `evento_curso_ibfk_1` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE,
  ADD CONSTRAINT `evento_curso_ibfk_2` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE;

--
-- Constraints for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificaciones_ibfk_2` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE;

--
-- Constraints for table `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`) ON DELETE CASCADE;

--
-- Constraints for table `precios`
--
ALTER TABLE `precios`
  ADD CONSTRAINT `precios_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE;

--
-- Constraints for table `profesores`
--
ALTER TABLE `profesores`
  ADD CONSTRAINT `profesores_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
