<?php
header('Content-Type: application/json');
require_once '../functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_id = sanitize($_POST['appointment_id']);
    $reason = sanitize($_POST['reason']);

    // Get appointment details
    $stmt = $pdo->prepare("SELECT a.*, d.user_id as doctor_user_id, u.full_name as patient_name 
                          FROM appointments a 
                          JOIN doctors d ON a.doctor_id = d.id 
                          JOIN patients p ON a.patient_id = p.id 
                          JOIN users u ON p.user_id = u.id 
                          WHERE a.id = ?");
    $stmt->execute([$appointment_id]);
    $app = $stmt->fetch();

    if (!$app) {
        echo json_encode(['success' => false, 'message' => 'Appointment not found.']);
        exit();
    }

    // Security check: Only patient or doctor/secretary can cancel
    if ($_SESSION['role'] == 'patient') {
        $patient_stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ?");
        $patient_stmt->execute([$_SESSION['user_id']]);
        $p_id = $patient_stmt->fetch()['id'];
        if ($app['patient_id'] != $p_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
            exit();
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled', cancellation_reason = ? WHERE id = ?");
        $stmt->execute([$reason, $appointment_id]);

        // Notify Doctor
        addNotification($app['doctor_user_id'], "Appointment with {$app['patient_name']} on {$app['appointment_date']} has been cancelled. Reason: $reason");

        logActivity($_SESSION['user_id'], 'Cancel Appointment', "Cancelled appointment ID: $appointment_id");

        echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
