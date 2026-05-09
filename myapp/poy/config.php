<?php
// MedCore Hospital Booking System Configuration

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'medcore_db');

// App Constants
define('APP_NAME', 'MedCore Hospital');
define('APP_URL', 'http://localhost/Poy'); // Adjust based on your local setup

// Start Session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database Connection using PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Set Timezone
date_default_timezone_set('UTC');
?>
