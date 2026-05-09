<?php
require_once '../functions.php';

if (!isLoggedIn() || $_SESSION['role'] != 'doctor') {
    redirect('../login.php');
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Security check: ensure schedule belongs to this doctor
    $stmt = $pdo->prepare("SELECT s.id FROM schedules s JOIN doctors d ON s.doctor_id = d.id WHERE s.id = ? AND d.user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
        $stmt->execute([$id]);
        logActivity($_SESSION['user_id'], 'Delete Schedule', "Deleted shift ID: $id");
    }
}

redirect('../doctor_schedule.php');
?>
