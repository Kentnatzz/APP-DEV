<?php
require_once 'config.php';

/**
 * Authentication Functions
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function checkRole($allowed_roles) {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    if (!in_array($_SESSION['role'], (array)$allowed_roles)) {
        redirect('dashboard.php?error=unauthorized');
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * User Functions
 */

function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function logActivity($user_id, $action, $details = null) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $details, $ip]);
}

/**
 * Notification Functions
 */

function addNotification($user_id, $message) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    return $stmt->execute([$user_id, $message]);
}

function getUnreadNotifications($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

/**
 * UI Helpers
 */

function getStatusBadge($status) {
    $classes = [
        'pending' => 'bg-warning text-dark',
        'approved' => 'bg-primary text-white',
        'completed' => 'bg-success text-white',
        'cancelled' => 'bg-danger text-white'
    ];
    return isset($classes[$status]) ? $classes[$status] : 'bg-secondary text-white';
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}
?>
