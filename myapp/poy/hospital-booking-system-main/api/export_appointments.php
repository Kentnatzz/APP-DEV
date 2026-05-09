<?php
session_start();
require_once '../config.php';
require_once '../functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    exit;
}

$format = $_GET['format'] ?? 'csv';
$role = getUserRole();
$user_id = $_SESSION['user_id'];

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="appointments_' . date('Y-m-d') . '.csv"');
} else {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="appointments_' . date('Y-m-d') . '.json"');
}

$data = [];

if ($role === 'patient') {
    $patient_id = getPatientByUserId($user_id, $link);
    $query = "SELECT a.id, a.appointment_date, a.time_slot, a.status, 
                     d.name as doctor_name, d.specialization 
              FROM appointments a 
              JOIN doctors d ON a.doctor_id = d.id 
              WHERE a.patient_id = ? 
              ORDER BY a.appointment_date DESC";
    
    $stmt = $link->prepare($query);
    $stmt->bind_param('i', $patient_id);
    
} elseif ($role === 'doctor') {
    $doctor_id = getPatientByUserId($user_id, $link);
    $query = "SELECT a.id, a.appointment_date, a.time_slot, a.status, 
                     p.first_name, p.last_name 
              FROM appointments a 
              JOIN patients p ON a.patient_id = p.id 
              WHERE a.doctor_id = ? 
              ORDER BY a.appointment_date DESC";
    
    $stmt = $link->prepare($query);
    $stmt->bind_param('i', $doctor_id);
    
} else {
    http_response_code(403);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
$stmt->close();

if ($format === 'csv') {
    // Output CSV
    if (count($data) > 0) {
        $keys = array_keys($data[0]);
        echo implode(',', $keys) . "\n";
        foreach ($data as $row) {
            echo implode(',', array_map(function($v) { return '"' . str_replace('"', '""', $v) . '"'; }, $row)) . "\n";
        }
    }
} else {
    echo json_encode(['success' => true, 'data' => $data], JSON_PRETTY_PRINT);
}

$link->close();
?>
