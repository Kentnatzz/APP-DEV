<?php
// ============================================
// MEDCORE HOSPITAL MANAGEMENT SYSTEM
// Database Configuration
// ============================================

// Database Configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'medcore_hospital');

// Site Configuration
define('SITE_NAME', 'MedCore Hospital');
define('SITE_URL', 'http://localhost/myapp/hospital-booking-system-main/');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('HASH_ALGORITHM', 'sha256');

// Initialize Database Connection
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
if ($link === false) {
    die('ERROR: Could not connect to MySQL server. ' . mysqli_connect_error());
}

// Create database if not exists
if (!mysqli_select_db($link, DB_NAME)) {
    $createDbSql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (!mysqli_query($link, $createDbSql)) {
        die('ERROR: Could not create database. ' . mysqli_error($link));
    }
    if (!mysqli_select_db($link, DB_NAME)) {
        die('ERROR: Could not select database. ' . mysqli_error($link));
    }
    
    // Initialize database schema on first run
    initializeDatabase($link);
}

mysqli_set_charset($link, 'utf8mb4');

/**
 * Initialize database schema from database.sql
 */
function initializeDatabase($link) {
    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        return;
    }
    
    $sql = file_get_contents($sqlFile);
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            if (!mysqli_query($link, $query)) {
                error_log('Database initialization error: ' . mysqli_error($link));
            }
        }
    }
}

// Create uploads directory if not exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

?>
