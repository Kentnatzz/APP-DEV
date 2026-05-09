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

$userId = getCurrentUserId();
$limit = intval($_GET['limit'] ?? 10);
$offset = intval($_GET['offset'] ?? 0);
$unreadOnly = isset($_GET['unread']);

$notifications = getUserNotifications($userId, $limit, $link);
$unreadCount = getUnreadNotificationCount($userId, $link);

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => intval($unreadCount),
    'total' => count($notifications)
]);

mysqli_close($link);
?>
