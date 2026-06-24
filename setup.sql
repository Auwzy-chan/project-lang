-- Auto Hub Database Setup Script
-- Run this in phpMyAdmin to create the users table

-- Create the users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `Full_Name` VARCHAR(100) NOT NULL,
  `Email_Address` VARCHAR(100) NOT NULL UNIQUE,
  `Phone_Number` VARCHAR(20) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`Email_Address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample user for testing (optional)
-- INSERT INTO `users` (Full_Name, Email_Address, Phone_Number, Password) 
-- VALUES ('Test User', 'test@example.com', '+63 9XX XXX XXXX', 'password123');
