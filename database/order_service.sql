CREATE DATABASE IF NOT EXISTS order_service;
USE order_service;

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `user_id` int(100) NOT NULL,
  `name` varchar(20) NOT NULL,
  `number` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `method` varchar(50) NOT NULL,
  `total_products` varchar(1000) NOT NULL,
  `total_price` int(100) NOT NULL,
  `placed_on` date NOT NULL DEFAULT current_timestamp(),
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
  `dining_option` enum('dine_in','delivery') NOT NULL DEFAULT 'delivery',
  `address` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_number` int(11) NOT NULL,
  `capacity` int(11) NOT NULL,
  `status` enum('available','reserved') DEFAULT 'available',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tables` (`table_number`, `capacity`, `status`) VALUES
(1, 4, 'available'),
(2, 4, 'available'),
(3, 6, 'available'),
(4, 2, 'available'),
(5, 8, 'available');

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `table_id` int(11) NOT NULL,
   `name` varchar(100) NOT NULL,
   `phone` varchar(15) NOT NULL,
   `reservation_time` datetime NOT NULL,
   `user_id` int(11) NOT NULL,
   `order_id` int(100) DEFAULT 0,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
