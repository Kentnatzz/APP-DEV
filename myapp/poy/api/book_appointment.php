<?php
header('Content-Type: application/json');
require_once '../functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to book an appointment.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_id = sanitize($_POST['doctor_id']);
    $appointment_date = sanitize($_POST['appointment_date']);
    $appointment_time = sanitize($_POST['appointment_time']);
    $reason = sanitize($_POST['reason']);
    
    // Get patient ID
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $patient = $stmt->fetch();

    if (!$patient) {
        echo json_encode(['success' => false, 'message' => 'Patient record not found.']);
        exit();
    }

    $patient_id = $patient['id'];

    // Check if slot is already booked
    $stmt = $pdo->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'");
    $stmt->execute([$doctor_id, $appointment_date, $appointment_time]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'This time slot is already booked. Please choose another one.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, reason) VALUES (?, ?, ?, ?, 'pending', ?)");
        $stmt->execute([$patient_id, $doctor_id, $appointment_date, $appointment_time, $reason]);

        // Notify Doctor
        $doc_stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE id = ?");
        $doc_stmt->execute([$doctor_id]);
        $doctor_user = $doc_stmt->fetch();
        addNotification($doctor_user['user_id'], "New appointment request from {$_SESSION['full_name']} for $appointment_date at $appointment_time.");

        logActivity($_SESSION['user_id'], 'Book Appointment', "Booked appointment with Doctor ID: $doctor_id");

        echo json_encode(['success' => true, 'message' => 'Appointment booked successfully! Waiting for approval.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error booking appointment: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
