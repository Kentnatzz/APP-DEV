<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCore Hospital - Doctor Dashboard</title>
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
        .sidebar .nav-menu i { margin-right: 12px; width: 20px; }
        .main-content { margin-left: 260px; padding: 30px; }
        .header { background: white; padding: 20px 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; border-left: 4px solid; }
        .stat-card.primary { border-left-color: #667eea; }
        .stat-value { font-size: 28px; font-weight: 700; color: #333; }
        .stat-label { font-size: 14px; color: #999; margin-top: 8px; }
        .stat-icon { font-size: 32px; margin-bottom: 10px; }
        .appointment-card { background: white; padding: 20px; border-radius: 15px; margin-bottom: 15px; border-left: 4px solid #667eea; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; min-height: auto; padding: 15px; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once 'config.php';
    require_once 'functions.php';

    if (!isLoggedIn() || !hasRole('doctor')) {
        header('Location: login.php');
        exit;
    }

    $userId = getCurrentUserId();
    $doctor = getDoctorByUserId($userId, $link);
    $todayAppointments = getAppointments($link, ['doctor_id' => $doctor['id'], 'date' => date('Y-m-d')]);
    $stats = getDashboardStats($link);
    ?>

    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-hospital me-2"></i>MedCore
        </div>
        <ul class="nav-menu">
            <li><a href="doctor_dashboard.php" class="active"><i class="fas fa-home"></i>Dashboard</a></li>
            <li><a href="appointments.php"><i class="fas fa-calendar"></i>Appointments</a></li>
            <li><a href="schedules.php"><i class="fas fa-clock"></i>Schedule</a></li>
            <li><a href="reviews.php"><i class="fas fa-star"></i>Reviews</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i>Profile</a></li>
            <li><a href="api/logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: #333; margin: 0;">Dr. <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
                <p style="color: #999; margin-top: 5px;">Doctor Dashboard</p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 14px; color: #999;">Today</div>
                <div style="font-size: 24px; font-weight: 700; color: #333;"><?php echo date('M d, Y'); ?></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-value"><?php echo count($todayAppointments); ?></div>
                    <div class="stat-label">Today's Appointments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fas fa-star"></i></div>
                    <div class="stat-value"><?php echo $doctor['rating']; ?>/5</div>
                    <div class="stat-label">Doctor Rating</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-value"><?php echo $doctor['total_patients']; ?></div>
                    <div class="stat-label">Total Patients</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fas fa-check"></i></div>
                    <div class="stat-value"><?php echo $doctor['total_appointments']; ?></div>
                    <div class="stat-label">Total Appointments</div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-8">
                <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="margin-bottom: 20px; font-weight: 700;">Today's Appointments</h3>
                    <?php if (empty($todayAppointments)): ?>
                        <div style="text-align: center; padding: 40px; color: #999;">
                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            <p>No appointments today</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($todayAppointments as $apt): ?>
                            <div class="appointment-card">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <h5 style="font-weight: 600; margin: 0;">Patient: <?php echo htmlspecialchars($apt['patient_first_name'] . ' ' . $apt['patient_last_name']); ?></h5>
                                        <p style="color: #999; font-size: 13px; margin: 5px 0;"><i class="fas fa-clock me-2"></i><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></p>
                                        <p style="color: #999; font-size: 13px; margin: 5px 0;"><i class="fas fa-stethoscope me-2"></i>Reason: <?php echo htmlspecialchars($apt['reason_for_visit']); ?></p>
                                    </div>
                                    <span class="badge" style="background: #2ed573;"><?php echo ucfirst($apt['status']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-4">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h4 style="margin-bottom: 15px; font-weight: 600;"><i class="fas fa-info-circle me-2"></i>Doctor Info</h4>
                    <div style="font-size: 14px;">
                        <p><strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialization']); ?></p>
                        <p><strong>Experience:</strong> <?php echo $doctor['experience_years']; ?> years</p>
                        <p><strong>License:</strong> <?php echo htmlspecialchars($doctor['license_number']); ?></p>
                        <p><strong>Room:</strong> <?php echo htmlspecialchars($doctor['room_number']); ?></p>
                        <p><strong>Consultation Fee:</strong> $<?php echo $doctor['consultation_fee']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
