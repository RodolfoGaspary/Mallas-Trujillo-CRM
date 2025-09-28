-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2025 at 10:41 AM
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
-- Database: `mallas_trujillo`
--

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `id_clientes` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id_clientes`, `nombre`, `telefono`, `email`) VALUES
(1, 'rodolfol', '987897', 'correo@rodofo');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id_items` int(11) NOT NULL,
  `detalle` varchar(255) NOT NULL,
  `ancho` decimal(10,4) NOT NULL,
  `alto` decimal(10,4) NOT NULL,
  `area` decimal(10,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id_items`, `detalle`, `ancho`, `alto`, `area`) VALUES
(1, 'rodolofo i 1', 1.0000, 2.0000, 2.0000),
(2, 'rodolfo i2', 1.0000, 3.0000, 3.0000);

-- --------------------------------------------------------

--
-- Table structure for table `precios`
--

CREATE TABLE `precios` (
  `id` int(11) NOT NULL,
  `precio` decimal(12,2) NOT NULL,
  `descuento_1` decimal(8,2) NOT NULL DEFAULT 0.00,
  `descuento_2` decimal(8,2) NOT NULL DEFAULT 0.00,
  `descuento_3` decimal(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `precios`
--

INSERT INTO `precios` (`id`, `precio`, `descuento_1`, `descuento_2`, `descuento_3`, `created_at`) VALUES
(1, 50.00, 10.00, 20.00, 30.00, '2025-09-28 08:30:32');

-- --------------------------------------------------------

--
-- Table structure for table `proformas`
--

CREATE TABLE `proformas` (
  `id_proformas` int(11) NOT NULL,
  `direccion_proformas` varchar(255) NOT NULL,
  `id_c_p` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `precio` decimal(12,2) NOT NULL,
  `descuento` decimal(8,2) DEFAULT 0.00,
  `costo_adicional` decimal(12,2) DEFAULT 0.00,
  `descripcion_costo_adicional` varchar(512) DEFAULT NULL,
  `total` decimal(12,2) DEFAULT NULL,
  `detalles` varchar(255) DEFAULT NULL,
  `arnes` int(64) DEFAULT NULL,
  `escaleras` varchar(255) DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `subtotal_desc` decimal(12,2) NOT NULL,
  `area_total` decimal(12,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proformas`
--

INSERT INTO `proformas` (`id_proformas`, `direccion_proformas`, `id_c_p`, `fecha`, `created_at`, `precio`, `descuento`, `costo_adicional`, `descripcion_costo_adicional`, `total`, `detalles`, `arnes`, `escaleras`, `subtotal`, `subtotal_desc`, `area_total`) VALUES
(1, 'direc rodolfo', 1, '2025-09-05', '2025-09-28 08:03:51', 50.00, 10.00, 35.00, 'costo rodolfo', 260.00, 'detalle rodolfo', 2, 'escalkera odolfop', 250.00, 225.00, 5.0000);

-- --------------------------------------------------------

--
-- Table structure for table `proformas_items`
--

CREATE TABLE `proformas_items` (
  `id` int(11) NOT NULL,
  `id_proforma` int(11) NOT NULL,
  `id_item` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proformas_items`
--

INSERT INTO `proformas_items` (`id`, `id_proforma`, `id_item`) VALUES
(1, 1, 1),
(2, 1, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_clientes`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id_items`);

--
-- Indexes for table `precios`
--
ALTER TABLE `precios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `proformas`
--
ALTER TABLE `proformas`
  ADD PRIMARY KEY (`id_proformas`),
  ADD KEY `fk_proformas_clientes` (`id_c_p`),
  ADD KEY `idx_proformas_fecha` (`fecha`);

--
-- Indexes for table `proformas_items`
--
ALTER TABLE `proformas_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proforma` (`id_proforma`),
  ADD KEY `idx_item` (`id_item`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_clientes` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id_items` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `precios`
--
ALTER TABLE `precios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `proformas`
--
ALTER TABLE `proformas`
  MODIFY `id_proformas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `proformas_items`
--
ALTER TABLE `proformas_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `proformas`
--
ALTER TABLE `proformas`
  ADD CONSTRAINT `fk_proformas_clientes` FOREIGN KEY (`id_c_p`) REFERENCES `clientes` (`id_clientes`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
