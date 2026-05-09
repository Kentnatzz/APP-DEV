<?php
header('Content-Type: application/json');
require_once '../functions.php';

if (!isLoggedIn() || !in_array($_SESSION['role'], ['doctor', 'secretary', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_id = sanitize($_POST['appointment_id']);
    $status = sanitize($_POST['status']);

    try {
        // Get appointment and patient info
        $stmt = $pdo->prepare("SELECT a.*, p.user_id as patient_user_id, u.full_name as doctor_name 
                              FROM appointments a 
                              JOIN doctors d ON a.doctor_id = d.id 
                              JOIN users u ON d.user_id = u.id 
                              JOIN patients p ON a.patient_id = p.id 
                              WHERE a.id = ?");
        $stmt->execute([$appointment_id]);
        $app = $stmt->fetch();

        if (!$app) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found.']);
            exit();
        }

        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->execute([$status, $appointment_id]);

        // Notify Patient
        $message = "Your appointment with {$app['doctor_name']} on {$app['appointment_date']} has been " . ucfirst($status) . ".";
        addNotification($app['patient_user_id'], $message);

        logActivity($_SESSION['user_id'], 'Update Appointment Status', "Updated appointment ID $appointment_id to $status");

        echo json_encode(['success' => true, 'message' => 'Appointment status updated successfully.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
