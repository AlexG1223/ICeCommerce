-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2026 at 09:01 PM
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
-- Database: `impresos_carnelli`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Pizzería', 'pizzeria'),
(2, 'Cafetería', 'cafeteria'),
(3, 'Oficina', 'oficina');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_method` enum('mercadopago','manual') NOT NULL,
  `payment_status` enum('pending','paid','failed','approved','rejected','cancelled','refunded','in_process','completed') DEFAULT 'pending',
  `preference_id` varchar(255) DEFAULT NULL,
  `shipping_agency` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `customer_phone`, `customer_email`, `customer_address`, `notes`, `total`, `payment_method`, `payment_status`, `preference_id`, `created_at`) VALUES
(1, 'Alex Carnelli', '098', 'agcarnelli2023@gmail.com', 'Guayabos ', 'aa aa', 4700.00, 'mercadopago', 'pending', NULL, '2026-04-02 21:44:02'),
(2, 'Alex Carnelli', '092473724', 'agcarnelli2023@gmail.com', 'Guayabos 137 ', 'Nota adicional ', 450000.00, 'manual', 'pending', NULL, '2026-04-07 19:31:56'),
(3, 'Alex Carnelli', '092473724', 'agcarnelli2023@gmail.com', 'Guayabos 137 ', '', 300000.00, 'manual', 'pending', NULL, '2026-04-07 19:34:01');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`) VALUES
(1, 1, 3, 1, 3500.00),
(2, 1, 4, 1, 1200.00),
(3, 2, 2, 250, 1800.00),
(4, 3, 4, 250, 1200.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `min_quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `is_active`, `min_quantity`) VALUES
(1, 1, 'Cajas para Pizza (Pack x 100)', 'Cajas de cartón corrugado, formato estándar familiar. Ideales para delivery.', 2500.00, 50, 1, 250),
(2, 1, 'Imanes para Heladera (Pack x 500)', 'Imanes publicitarios de 5x5 cm a todo color.', 1800.00, 100, 1, 250),
(3, 2, 'Vasos Térmicos (Pack x 200)', 'Vasos de polipapel con tapa, capacidad 250ml.', 3500.00, 30, 1, 250),
(4, 2, 'Posavasos de Cartón (Pack x 1000)', 'Posavasos impresos full color, material absorbente.', 1200.00, 200, 1, 250),
(5, 3, 'Tarjetas Personales (Pack x 1000)', 'Tarjetas 9x5 cm en papel ilustración 300g con laminado mate.', 1500.00, 80, 1, 250),
(6, 3, 'Talonarios A4 (Pack x 10)', 'Talonarios de 50 hojas originales y duplicados.', 2200.00, 40, 1, 250),
(7, 3, 'Tony', 'gato travieso', 999.00, 123, 1, 1),
(8, 2, 'TOTO', 'Breve', 200.00, 23, 1, 12);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `url`, `is_primary`, `created_at`) VALUES
(1, 1, 'https://placehold.co/400x400/9b2226/ffffff?text=Cajas+Pizza', 1, '2026-04-09 14:50:50'),
(2, 2, 'https://placehold.co/400x400/9b2226/ffffff?text=Imanes', 1, '2026-04-09 14:50:50'),
(3, 3, 'https://placehold.co/400x400/9b2226/ffffff?text=Vasos+Termicos', 1, '2026-04-09 14:50:50'),
(4, 4, 'https://placehold.co/400x400/9b2226/ffffff?text=Posavasos', 1, '2026-04-09 14:50:50'),
(5, 5, 'https://placehold.co/400x400/9b2226/ffffff?text=Tarjetas', 1, '2026-04-09 14:50:50'),
(6, 6, 'https://placehold.co/400x400/9b2226/ffffff?text=Talonarios', 1, '2026-04-09 14:50:50'),
(7, 7, 'img/products/1775758352_portadaTuCita.jpg', 1, '2026-04-09 18:12:32'),
(8, 8, 'img/products/1775760331_portadaUsuarios.jpg', 1, '2026-04-09 18:45:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
