<?php
header('Content-Type: application/json');
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$limit = intval($_GET['limit'] ?? 50);
$offset = intval($_GET['offset'] ?? 0);
$action_type = $_GET['action_type'] ?? null;

$where = "WHERE user_id = ?";
$params = [$user_id];
$types = 'i';

if ($action_type) {
    $where .= " AND action_type = ?";
    $params[] = $action_type;
    $types .= 's';
}

$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$query = "SELECT id, action, action_type, description, created_at 
          FROM activity_logs 
          $where 
          ORDER BY created_at DESC 
          LIMIT ? OFFSET ?";

$stmt = $link->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = [
        'id' => $row['id'],
        'action' => $row['action'],
        'action_type' => $row['action_type'],
        'description' => $row['description'],
        'created_at' => $row['created_at']
    ];
}

echo json_encode([
    'success' => true,
    'logs' => $logs,
    'total' => count($logs)
]);

$stmt->close();
$link->close();
?>
