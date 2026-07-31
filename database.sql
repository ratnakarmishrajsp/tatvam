-- TATVAM - MySQL Database Schema
-- Run this schema in your phpMyAdmin / MySQL Terminal if using MySQL mode

CREATE DATABASE IF NOT EXISTS `tatvam` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tatvam`;

-- 1. Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(100) UNIQUE NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `original_price` DECIMAL(10,2) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_email` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(20) NOT NULL,
  `product_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_status` VARCHAR(50) DEFAULT 'pending',
  `razorpay_order_id` VARCHAR(100) DEFAULT NULL,
  `razorpay_payment_id` VARCHAR(100) DEFAULT NULL,
  `download_token` VARCHAR(100) DEFAULT NULL,
  `download_count` INT DEFAULT 0,
  `token_expiry` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default products
INSERT INTO `products` (`title`, `slug`, `price`, `original_price`, `file_path`, `category`) VALUES
('मन की शांति (The Power of Calm)', 'positive-thinking', 99.00, 999.00, 'files/power_of_calm_hindi.pdf', 'mindset'),
('चिंता मुक्ति (Anxiety Relief)', 'stress-worry', 99.00, 999.00, 'files/anxiety_relief_hindi.pdf', 'peace'),
('अनुशासन क्रांति (Ultimate Discipline)', 'habit-freedom', 99.00, 999.00, 'files/ultimate_discipline_hindi.pdf', 'discipline'),
('समृद्धि सूत्र (Wealth Principles)', 'wealth-mindset', 99.00, 999.00, 'files/wealth_principles_hindi.pdf', 'wealth'),
('TATVAM Mega Mindset Bundle (4-in-1)', 'mega-bundle', 199.00, 3996.00, 'files/tattvam_mega_bundle.zip', 'bundle')
ON DUPLICATE KEY UPDATE `price`=VALUES(`price`);
