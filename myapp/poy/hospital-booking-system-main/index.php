<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (isLoggedIn()) {
    // Redirect based on role
    $redirect_pages = [
        'admin' => 'admin_dashboard.php',
        'secretary' => 'secretary_dashboard.php',
        'doctor' => 'doctor_dashboard.php',
        'patient' => 'patient_dashboard.php'
    ];
    $role = $_SESSION['user_role'] ?? 'patient';
    $redirect = $redirect_pages[$role] ?? 'patient_dashboard.php';
    header('Location: ' . $redirect);
    exit;
}

// Redirect to login if not logged in
header('Location: login.php');
exit;
?>
