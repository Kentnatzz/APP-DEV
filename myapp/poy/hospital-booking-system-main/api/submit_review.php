<?php
session_start();
require_once '../config.php';
require_once '../functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('patient')) {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['doctor_id']) || !isset($input['rating']) || !isset($input['comment']) || !isset($input['appointment_id'])) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

$doctorId = intval($input['doctor_id']);
$appointmentId = intval($input['appointment_id']);
$rating = intval($input['rating']);
$comment = trim($input['comment']);

$patient = getPatientByUserId(getCurrentUserId(), $link);

if (!$patient) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Patient record not found']));
}

$result = addReview($doctorId, $patient['id'], $appointmentId, $rating, $comment, $link);

echo json_encode($result);
?>
