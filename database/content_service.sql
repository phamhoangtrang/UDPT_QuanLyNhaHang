CREATE DATABASE IF NOT EXISTS content_service;
USE content_service;

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `message` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Recreate footer table with proper structure
DROP TABLE IF EXISTS `footer`;
CREATE TABLE `footer` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `email1` varchar(255) NOT NULL,
   `email2` varchar(255) NOT NULL,
   `opening_hours` varchar(100) NOT NULL,
   `address` varchar(255) NOT NULL,
   `phone1` varchar(15) NOT NULL,
   `phone2` varchar(15) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial footer data
INSERT INTO `footer` (`id`, `email1`, `email2`, `opening_hours`, `address`, `phone1`, `phone2`) 
VALUES (1, 'homie@gmail.com', 'restaurant@gmail.com', '07:00am to 09:00pm', '123 Main Street, City, Country', '0123456789', '0987654321');

-- Recreate reviews table
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add sample reviews
INSERT INTO `reviews` (`user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 5, 'Excellent food!', '2025-03-25 06:41:54'),
(1, 4, 'Great service', '2025-03-25 06:41:57'), 
(1, 5, 'Amazing sushi', '2025-03-25 06:42:04'),
(1, 4, 'Will come back again', '2025-03-25 06:43:56'),
(1, 5, 'Best Japanese restaurant', '2025-03-25 06:44:25');
