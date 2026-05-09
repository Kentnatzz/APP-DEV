<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCore Hospital - Secretary Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f5f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; color: white; position: fixed; width: 260px; left: 0; top: 0; }
        .sidebar .logo { font-size: 24px; font-weight: 700; margin-bottom: 40px; }
        .sidebar .nav-menu { list-style: none; }
        .sidebar .nav-menu li { margin-bottom: 15px; }
        .sidebar .nav-menu a { color: rgba(255,255,255,0.8); text-decoration: none; display: flex; align-items: center; padding: 12px; border-radius: 8px; transition: all 0.3s ease; }
        .sidebar .nav-menu a:hover, .sidebar .nav-menu a.active { background: rgba(255,255,255,0.2); color: white; }
        .main-content { margin-left: 260px; padding: 30px; }
        .header { background: white; padding: 20px 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; border-left: 4px solid #667eea; }
        .stat-value { font-size: 28px; font-weight: 700; color: #333; }
        .stat-label { font-size: 14px; color: #999; margin-top: 8px; }
        .stat-icon { font-size: 32px; margin-bottom: 10px; }
        .action-btn { margin-right: 5px; margin-bottom: 5px; }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; min-height: auto; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once 'config.php';
    require_once 'functions.php';

    if (!isLoggedIn() || !hasRole('secretary')) {
        header('Location: login.php');
        exit;
    }

    $stats = getDashboardStats($link);
    $pendingAppointments = getAppointments($link, ['status' => 'pending']);
    ?>

    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-hospital me-2"></i>MedCore
        </div>
        <ul class="nav-menu">
            <li><a href="secretary_dashboard.php" class="active"><i class="fas fa-home"></i>Dashboard</a></li>
            <li><a href="appointments.php"><i class="fas fa-calendar"></i>Appointments</a></li>
            <li><a href="patients.php"><i class="fas fa-users"></i>Patients</a></li>
            <li><a href="doctors.php"><i class="fas fa-stethoscope"></i>Doctors</a></li>
            <li><a href="api/logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1 style="font-size: 28px; font-weight: 700; color: #333; margin: 0;">Secretary Dashboard</h1>
            <p style="color: #999; margin-top: 5px;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                    <div class="stat-value"><?php echo $stats['total_appointments']; ?></div>
                    <div class="stat-label">Total Appointments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-hourglass-end"></i></div>
                    <div class="stat-value"><?php echo $stats['pending_appointments']; ?></div>
                    <div class="stat-label">Pending Approval</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-value"><?php echo $stats['total_patients']; ?></div>
                    <div class="stat-label">Total Patients</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-stethoscope"></i></div>
                    <div class="stat-value"><?php echo $stats['total_doctors']; ?></div>
                    <div class="stat-label">Doctors</div>
                </div>
            </div>
        </div>

        <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-top: 30px;">
            <h3 style="margin-bottom: 20px; font-weight: 700;">Pending Appointments</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($pendingAppointments, 0, 10) as $apt): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($apt['patient_first_name'] . ' ' . $apt['patient_last_name']); ?></td>
                                <td><?php echo htmlspecialchars('Dr. ' . $apt['doctor_first_name'] . ' ' . $apt['doctor_last_name']); ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($apt['appointment_date'] . ' ' . $apt['appointment_time'])); ?></td>
                                <td><span class="badge bg-warning">Pending</span></td>
                                <td>
                                    <button class="btn btn-sm btn-success action-btn" onclick="approveAppointment(<?php echo $apt['id']; ?>)">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-sm btn-danger action-btn" onclick="rejectAppointment(<?php echo $apt['id']; ?>)">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function approveAppointment(id) {
            if (confirm('Approve this appointment?')) {
                fetch('api/update_appointment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, status: 'approved' })
                }).then(() => location.reload());
            }
        }
        
        function rejectAppointment(id) {
            if (confirm('Reject this appointment?')) {
                fetch('api/update_appointment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, status: 'cancelled' })
                }).then(() => location.reload());
            }
        }
    </script>
</body>
</html>
