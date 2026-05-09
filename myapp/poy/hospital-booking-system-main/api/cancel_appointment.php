<?php
session_start();
require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !isset($input['reason'])) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

$appointmentId = intval($input['id']);
$reason = trim($input['reason']);
$userId = getCurrentUserId();

$result = cancelAppointment($appointmentId, $userId, $reason, $link);

echo json_encode($result);
?>
