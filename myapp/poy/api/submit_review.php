<?php
header('Content-Type: application/json');
require_once '../functions.php';

if (!isLoggedIn() || $_SESSION['role'] != 'patient') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_id = sanitize($_POST['appointment_id']);
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Invalid rating.']);
        exit();
    }

    try {
        // Check if appointment is completed and belongs to patient
        $stmt = $pdo->prepare("SELECT a.id, d.user_id as doctor_user_id FROM appointments a JOIN patients p ON a.patient_id = p.id JOIN doctors d ON a.doctor_id = d.id WHERE a.id = ? AND p.user_id = ? AND a.status = 'completed'");
        $stmt->execute([$appointment_id, $_SESSION['user_id']]);
        $app = $stmt->fetch();

        if (!$app) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found or not eligible for review.']);
            exit();
        }

        // Check if already reviewed
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE appointment_id = ?");
        $stmt->execute([$appointment_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You have already reviewed this appointment.']);
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO reviews (appointment_id, rating, comment) VALUES (?, ?, ?)");
        $stmt->execute([$appointment_id, $rating, $comment]);

        // Notify Doctor
        addNotification($app['doctor_user_id'], "{$_SESSION['full_name']} left you a $rating-star review.");

        logActivity($_SESSION['user_id'], 'Submit Review', "Submitted review for appointment ID: $appointment_id");

        echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
