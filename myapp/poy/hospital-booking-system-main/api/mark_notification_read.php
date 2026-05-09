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

$input = json_decode(file_get_contents('php://input'), true);
$notificationId = intval($input['notification_id'] ?? $_POST['notification_id'] ?? 0);

if ($notificationId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
    exit;
}

$userId = getCurrentUserId();

// Verify notification belongs to user
$verifyQuery = "SELECT id FROM notifications WHERE id = $notificationId AND user_id = $userId";
$verifyResult = mysqli_query($link, $verifyQuery);

if (mysqli_num_rows($verifyResult) === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (markNotificationAsRead($notificationId, $link)) {
    echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update notification']);
}

mysqli_close($link);
?>
