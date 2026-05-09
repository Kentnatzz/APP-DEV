<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCore - Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .main-container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .appointment-card { background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); border-left: 4px solid #667eea; }
        .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-completed { background: #d1ecf1; color: #0c5460; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        .btn-action { padding: 6px 12px; font-size: 12px; border-radius: 6px; }
        .header { margin-bottom: 30px; }
        .header h1 { font-weight: 700; color: #333; }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once 'config.php';
    require_once 'functions.php';

    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    $appointments = [];
    if (hasRole('patient')) {
        $patient = getPatientByUserId(getCurrentUserId(), $link);
        $appointments = getAppointments($link, ['patient_id' => $patient['id']]);
    } elseif (hasRole('doctor')) {
        $doctor = getDoctorByUserId(getCurrentUserId(), $link);
        $appointments = getAppointments($link, ['doctor_id' => $doctor['id']]);
    } else {
        $appointments = getAppointments($link);
    }
    ?>

    <div class="main-container">
        <div class="header">
            <h1><i class="fas fa-calendar-check me-2"></i>Appointments</h1>
            <p class="text-muted">Manage your medical appointments</p>
        </div>

        <?php if (empty($appointments)): ?>
            <div style="text-align: center; padding: 60px 20px;">
                <i class="fas fa-inbox" style="font-size: 64px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                <p style="color: #999; margin-bottom: 20px;">No appointments found</p>
                <?php if (hasRole('patient')): ?>
                    <a href="doctors.php" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <i class="fas fa-search me-2"></i>Find Doctor
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($appointments as $apt): ?>
                <div class="appointment-card">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <h5 style="margin: 0 0 10px 0; font-weight: 600;">
                                <?php if (hasRole('patient')): ?>
                                    Dr. <?php echo htmlspecialchars($apt['doctor_first_name'] . ' ' . $apt['doctor_last_name']); ?>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($apt['patient_first_name'] . ' ' . $apt['patient_last_name']); ?>
                                <?php endif; ?>
                            </h5>
                            <p style="margin: 8px 0; color: #666; font-size: 14px;">
                                <i class="fas fa-calendar me-2"></i><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?> at <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                            </p>
                            <p style="margin: 8px 0; color: #666; font-size: 14px;">
                                <i class="fas fa-stethoscope me-2"></i><?php echo htmlspecialchars($apt['specialization']); ?>
                            </p>
                            <?php if ($apt['reason_for_visit']): ?>
                                <p style="margin: 8px 0; color: #666; font-size: 14px;">
                                    <i class="fas fa-notes-medical me-2"></i><?php echo htmlspecialchars($apt['reason_for_visit']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div style="text-align: right;">
                            <span class="status-badge badge-<?php echo str_replace('_', '', $apt['status']); ?>">
                                <?php echo ucfirst($apt['status']); ?>
                            </span>
                            <?php if ($apt['status'] === 'pending' || $apt['status'] === 'approved'): ?>
                                <div style="margin-top: 10px;">
                                    <button class="btn btn-sm btn-outline-danger btn-action" onclick="cancelAppointment(<?php echo $apt['id']; ?>)">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function cancelAppointment(id) {
            if (confirm('Are you sure you want to cancel this appointment?')) {
                const reason = prompt('Enter cancellation reason:');
                if (reason) {
                    fetch('api/cancel_appointment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id, reason: reason })
                    }).then(() => location.reload());
                }
            }
        }
    </script>
</body>
</html>
