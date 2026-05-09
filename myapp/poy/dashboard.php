<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$role = $_SESSION['role'];

switch ($role) {
    case 'admin':
        include 'admin_dashboard.php';
        break;
    case 'secretary':
        include 'secretary_dashboard.php';
        break;
    case 'doctor':
        include 'doctor_dashboard.php';
        break;
    case 'patient':
        include 'patient_dashboard.php';
        break;
    default:
        redirect('logout.php');
}
?>
