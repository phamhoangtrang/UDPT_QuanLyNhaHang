CREATE DATABASE IF NOT EXISTS user_service;
USE user_service;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `number` varchar(10) NOT NULL,
  `password` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `password` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin` (`name`, `password`) VALUES
('admin', '6216f8a75fd5bb3d5f22b6f9958cdede3fc086c2');

-- Add sample user
INSERT INTO `users` (`name`, `email`, `number`, `password`) VALUES
('dang', 'user@gmail.com', '0123456789', '356a192b7913b04c54574d18c28d46e6395428ab');
