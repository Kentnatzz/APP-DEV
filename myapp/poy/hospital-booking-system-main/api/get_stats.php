<?php
header('Content-Type: application/json');
session_start();
require_once '../config.php';
require_once '../functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$role = getCurrentUserRole();
$userId = getCurrentUserId();
$roleId = getCurrentRoleId();

$stats = [];

if ($role === 'admin' || $role === 'secretary') {
    // Admin/Secretary: all appointments
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
              FROM appointments";
    
    $result = mysqli_query($link, $query);
    if ($result) {
        $stats = mysqli_fetch_assoc($result);
    }
} elseif ($role === 'doctor') {
    // Doctor: his appointments
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
              FROM appointments 
              WHERE doctor_id = $roleId";
    
    $result = mysqli_query($link, $query);
    if ($result) {
        $stats = mysqli_fetch_assoc($result);
    }
} elseif ($role === 'patient') {
    // Patient: his appointments
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
              FROM appointments 
              WHERE patient_id = $roleId";
    
    $result = mysqli_query($link, $query);
    if ($result) {
        $stats = mysqli_fetch_assoc($result);
    }
}

// Format stats to ensure they are numeric and not null
foreach (['total', 'completed', 'pending', 'approved', 'cancelled'] as $key) {
    $stats[$key] = intval($stats[$key] ?? 0);
}

echo json_encode([
    'success' => true,
    'stats' => $stats,
    'role' => $role
]);

mysqli_close($link);
?>
