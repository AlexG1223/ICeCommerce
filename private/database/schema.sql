CREATE DATABASE IF NOT EXISTS `impresos_carnelli`;
USE `impresos_carnelli`;

CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `stock` INT DEFAULT 0,
    `image` VARCHAR(255),
    `is_active` BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_name` VARCHAR(150) NOT NULL,
    `customer_phone` VARCHAR(50) NOT NULL,
    `customer_email` VARCHAR(150) NOT NULL,
    `customer_address` TEXT,
    `notes` TEXT,
    `total` DECIMAL(10,2) NOT NULL,
    `payment_method` ENUM('mercadopago', 'manual') NOT NULL,
    `payment_status` ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    `preference_id` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `order_details` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `unit_price` DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
);

-- Insert dummy data
INSERT IGNORE INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Pizzería', 'pizzeria'),
(2, 'Cafetería', 'cafeteria'),
(3, 'Oficina', 'oficina');

INSERT IGNORE INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image`, `is_active`) VALUES
(1, 1, 'Cajas para Pizza (Pack x 100)', 'Cajas de cartón corrugado, formato estándar familiar. Ideales para delivery.', 2500.00, 50, 'https://placehold.co/400x400/9b2226/ffffff?text=Cajas+Pizza', 1),
(2, 1, 'Imanes para Heladera (Pack x 500)', 'Imanes publicitarios de 5x5 cm a todo color.', 1800.00, 100, 'https://placehold.co/400x400/9b2226/ffffff?text=Imanes', 1),
(3, 2, 'Vasos Térmicos (Pack x 200)', 'Vasos de polipapel con tapa, capacidad 250ml.', 3500.00, 30, 'https://placehold.co/400x400/9b2226/ffffff?text=Vasos+Termicos', 1),
(4, 2, 'Posavasos de Cartón (Pack x 1000)', 'Posavasos impresos full color, material absorbente.', 1200.00, 200, 'https://placehold.co/400x400/9b2226/ffffff?text=Posavasos', 1),
(5, 3, 'Tarjetas Personales (Pack x 1000)', 'Tarjetas 9x5 cm en papel ilustración 300g con laminado mate.', 1500.00, 80, 'https://placehold.co/400x400/9b2226/ffffff?text=Tarjetas', 1),
(6, 3, 'Talonarios A4 (Pack x 10)', 'Talonarios de 50 hojas originales y duplicados.', 2200.00, 40, 'https://placehold.co/400x400/9b2226/ffffff?text=Talonarios', 1);
