<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = getCurrentUserId();
$role = $_SESSION['user_role'];

// For patients, show their appointment history
// For doctors, show their completed appointments
$appointments = [];
if ($role === 'patient') {
    $patient = getPatientByUserId($userId, $link);
    $appointments = getAppointments($link, ['patient_id' => $patient['id']]);
} elseif ($role === 'doctor') {
    $doctor = getDoctorByUserId($userId, $link);
    $appointments = getAppointments($link, ['doctor_id' => $doctor['id'], 'status' => 'completed']);
} else {
    $appointments = getAppointments($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity History - MedCore Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .container-custom { max-width: 900px; margin: 50px auto; }
        .history-card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .appointment-item { border-bottom: 1px solid #eef2f7; padding: 20px 0; display: flex; justify-content: space-between; align-items: center; }
        .appointment-item:last-child { border-bottom: none; }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-completed { background: #d1fae5; color: #059669; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-approved { background: #e0e7ff; color: #4338ca; }
        .status-pending { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="container-custom">
        <a href="index.php" class="btn btn-link text-decoration-none mb-4 text-muted">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>

        <div class="history-card">
            <h2 class="mb-4 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Appointment History</h2>

            <?php if (empty($appointments)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No history found.</p>
                </div>
            <?php else: ?>
                <div class="appointment-list">
                    <?php foreach ($appointments as $apt): ?>
                        <div class="appointment-item">
                            <div>
                                <h5 class="mb-1 fw-bold">
                                    <?php if ($role === 'patient'): ?>
                                        Dr. <?php echo htmlspecialchars($apt['doctor_first_name'] . ' ' . $apt['doctor_last_name']); ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($apt['patient_first_name'] . ' ' . $apt['patient_last_name']); ?>
                                    <?php endif; ?>
                                </h5>
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-calendar-alt me-1"></i> <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?> 
                                    at <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                                </p>
                                <p class="text-muted small">
                                    <i class="fas fa-stethoscope me-1"></i> <?php echo htmlspecialchars($apt['specialization']); ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="status-badge status-<?php echo $apt['status']; ?>">
                                    <?php echo ucfirst($apt['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
