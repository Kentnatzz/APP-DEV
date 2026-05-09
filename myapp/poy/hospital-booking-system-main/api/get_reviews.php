<?php
header('Content-Type: application/json');
session_start();
require_once '../config.php';

$doctor_id = intval($_GET['doctor_id'] ?? 0);

if ($doctor_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid doctor ID']);
    exit;
}

$query = "SELECT r.id, r.rating, r.comment, r.would_recommend, r.created_at, 
                 u.name, u.email 
          FROM reviews r 
          LEFT JOIN users u ON r.patient_id = u.id 
          WHERE r.doctor_id = ? 
          ORDER BY r.created_at DESC 
          LIMIT 50";

$stmt = $link->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = [
        'id' => $row['id'],
        'rating' => $row['rating'],
        'comment' => $row['comment'],
        'would_recommend' => $row['would_recommend'],
        'created_at' => $row['created_at'],
        'reviewer_name' => $row['name'],
        'reviewer_email' => $row['email']
    ];
}

echo json_encode([
    'success' => true,
    'reviews' => $reviews,
    'count' => count($reviews),
    'average_rating' => count($reviews) > 0 ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0
]);

$stmt->close();
$link->close();
?>
