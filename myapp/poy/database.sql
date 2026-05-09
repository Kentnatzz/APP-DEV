-- MedCore Hospital Booking System Database Schema
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `medcore_db`;
USE `medcore_db`;

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','secretary','doctor','patient') NOT NULL,
  `profile_pic` varchar(255) DEFAULT 'default_user.png',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Doctors Table
CREATE TABLE IF NOT EXISTS `doctors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `experience` int(11) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `consultation_fee` decimal(10,2) NOT NULL,
  `bio` text,
  `availability_status` enum('available','on_leave','busy') DEFAULT 'available',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Patients Table
CREATE TABLE IF NOT EXISTS `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `address` text,
  `phone` varchar(20),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Secretaries Table
CREATE TABLE IF NOT EXISTS `secretaries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `department` varchar(100),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Schedules Table
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Appointments Table
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','approved','completed','cancelled') DEFAULT 'pending',
  `reason` text,
  `cancellation_reason` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews Table
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (rating BETWEEN 1 AND 5),
  `comment` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications Table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity Logs Table
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text,
  `ip_address` varchar(45),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Fake Data
-- Clear existing data first to prevent duplicate errors on re-run
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `activity_logs`;
TRUNCATE TABLE `notifications`;
TRUNCATE TABLE `reviews`;
TRUNCATE TABLE `appointments`;
TRUNCATE TABLE `schedules`;
TRUNCATE TABLE `secretaries`;
TRUNCATE TABLE `patients`;
TRUNCATE TABLE `doctors`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- Users (Admin, Secretaries, Doctors, Patients)
INSERT INTO `users` (`username`, `password`, `email`, `full_name`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@medcore.com', 'System Administrator', 'admin'),
('sec_emily', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'emily@medcore.com', 'Emily Watson', 'secretary'),
('sec_john', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john@medcore.com', 'John Smith', 'secretary'),
('dr_house', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'house@medcore.com', 'Dr. Gregory House', 'doctor'),
('dr_wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'wilson@medcore.com', 'Dr. James Wilson', 'doctor'),
('dr_grey', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'grey@medcore.com', 'Dr. Meredith Grey', 'doctor'),
('dr_shepherd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'shepherd@medcore.com', 'Dr. Derek Shepherd', 'doctor'),
('dr_cuddy', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cuddy@medcore.com', 'Dr. Lisa Cuddy', 'doctor'),
('patient_sarah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sarah@example.com', 'Sarah Connor', 'patient'),
('patient_mike', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mike@example.com', 'Mike Ross', 'patient');

-- Doctors Details
INSERT INTO `doctors` (`user_id`, `specialization`, `experience`, `contact_number`, `room_number`, `consultation_fee`, `bio`) VALUES
(4, 'Neurologist', 20, '123-456-7890', '405', 150.00, 'Specialist in diagnostic medicine and neurology.'),
(5, 'Oncologist', 15, '123-456-7891', '302', 120.00, 'Expert in oncology and palliative care.'),
(6, 'General Surgeon', 12, '123-456-7892', '201', 100.00, 'Focused on general surgery and emergency medicine.'),
(7, 'Neurosurgeon', 18, '123-456-7893', '401', 250.00, 'Renowned neurosurgeon specializing in brain trauma.'),
(8, 'Endocrinologist', 16, '123-456-7894', '105', 110.00, 'Expert in endocrinology and hospital administration.');

-- Patients Details
INSERT INTO `patients` (`user_id`, `dob`, `gender`, `address`, `phone`) VALUES
(9, '1985-05-12', 'female', '123 Skynet Blvd', '555-0101'),
(10, '1990-11-20', 'male', '456 Pearson Specter St', '555-0102');

-- Schedules
INSERT INTO `schedules` (`doctor_id`, `day_of_week`, `start_time`, `end_time`) VALUES
(1, 'Monday', '08:00:00', '12:00:00'),
(1, 'Wednesday', '08:00:00', '12:00:00'),
(2, 'Tuesday', '09:00:00', '17:00:00'),
(3, 'Thursday', '10:00:00', '18:00:00'),
(4, 'Friday', '08:00:00', '16:00:00');

-- Appointments
INSERT INTO `appointments` (`patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `reason`) VALUES
(1, 1, '2026-05-15', '09:00:00', 'approved', 'Chronic leg pain'),
(2, 2, '2026-05-16', '14:00:00', 'pending', 'Routine checkup'),
(1, 3, '2026-05-17', '11:00:00', 'completed', 'Post-surgery follow-up');

-- Reviews
INSERT INTO `reviews` (`appointment_id`, `rating`, `comment`) VALUES
(3, 5, 'Dr. Grey was very professional and caring.');

-- Notifications
INSERT INTO `notifications` (`user_id`, `message`) VALUES
(4, 'You have a new appointment request from Sarah Connor.'),
(9, 'Your appointment with Dr. House has been approved.');

COMMIT;
