<?php
session_start();
require_once '../config.php';
require_once '../functions.php';

if (!isLoggedIn()) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !isset($input['status'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Missing required fields']));
}

$result = updateAppointmentStatus($input['id'], $input['status'], $link);

header('Content-Type: application/json');
echo json_encode($result);
?>
