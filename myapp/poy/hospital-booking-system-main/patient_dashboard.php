<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCore Hospital - Patient Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css">
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
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; border-left: 4px solid; transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .stat-card.primary { border-left-color: #667eea; }
        .stat-card.success { border-left-color: #2ed573; }
        .stat-card.warning { border-left-color: #ffa502; }
        .stat-card.danger { border-left-color: #ff4757; }
        .stat-value { font-size: 28px; font-weight: 700; color: #333; }
        .stat-label { font-size: 14px; color: #999; margin-top: 8px; }
        .stat-icon { font-size: 32px; margin-bottom: 10px; }
        .appointment-card { background: white; padding: 20px; border-radius: 15px; margin-bottom: 15px; border-left: 4px solid #667eea; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .btn-primary-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; }
        .btn-primary-gradient:hover { color: white; }
        .modal-content { border-radius: 15px; border: none; }
        .modal-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 15px 15px 0 0; }
        .modal-header .btn-close { filter: brightness(0) invert(1); }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; min-height: auto; padding: 15px; } .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once 'config.php';
    require_once 'functions.php';

    // Check if user is logged in and is a patient
    if (!isLoggedIn() || !hasRole('patient')) {
        header('Location: login.php');
        exit;
    }

    $userId = getCurrentUserId();
    $patient = getPatientByUserId($userId, $link);
    $stats = getDashboardStats($link);
    $appointments = getAppointments($link, ['patient_id' => $patient['id']]);
    $upcomingAppointments = array_filter($appointments, function($a) {
        return $a['appointment_date'] >= date('Y-m-d') && in_array($a['status'], ['pending', 'approved']);
    });
    ?>

    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-hospital me-2"></i>MedCore
        </div>
        <ul class="nav-menu">
            <li><a href="patient_dashboard.php" class="active"><i class="fas fa-home"></i>Dashboard</a></li>
            <li><a href="doctors.php"><i class="fas fa-stethoscope"></i>Find Doctor</a></li>
            <li><a href="book_appointment.php"><i class="fas fa-calendar-plus"></i>Book Appointment</a></li>
            <li><a href="appointments.php"><i class="fas fa-calendar-check"></i>My Appointments</a></li>
            <li><a href="reviews.php"><i class="fas fa-star"></i>Reviews</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i>Profile</a></li>
            <li><a href="history.php"><i class="fas fa-history"></i>Activity History</a></li>
            <li><a href="api/logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: #333; margin: 0;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
                <p style="color: #999; margin-top: 5px;">Patient Dashboard</p>
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
                    <div class="stat-value"><?php echo count($upcomingAppointments); ?></div>
                    <div class="stat-label">Upcoming Appointments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value"><?php echo count(array_filter($appointments, function($a) { return $a['status'] === 'completed'; })); ?></div>
                    <div class="stat-label">Completed Visits</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning">
                    <div class="stat-icon"><i class="fas fa-hourglass-end"></i></div>
                    <div class="stat-value"><?php echo count(array_filter($appointments, function($a) { return $a['status'] === 'pending'; })); ?></div>
                    <div class="stat-label">Pending Appointments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card danger">
                    <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-value"><?php echo count(array_filter($appointments, function($a) { return $a['status'] === 'cancelled'; })); ?></div>
                    <div class="stat-label">Cancelled</div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-8">
                <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="margin-bottom: 20px; font-weight: 700;">Upcoming Appointments</h3>
                    <?php if (empty($upcomingAppointments)): ?>
                        <div style="text-align: center; padding: 40px; color: #999;">
                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            <p>No upcoming appointments</p>
                            <a href="book_appointment.php" class="btn btn-primary-gradient mt-3">Book Appointment</a>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($upcomingAppointments, 0, 5) as $apt): ?>
                            <div class="appointment-card">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <h5 style="font-weight: 600; margin: 0;">Dr. <?php echo htmlspecialchars($apt['doctor_first_name'] . ' ' . $apt['doctor_last_name']); ?></h5>
                                        <p style="color: #999; font-size: 13px; margin: 5px 0;">Specialization: <?php echo htmlspecialchars($apt['specialization']); ?></p>
                                        <p style="color: #999; font-size: 13px; margin: 5px 0;"><i class="fas fa-calendar me-2"></i><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?> at <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></p>
                                    </div>
                                    <span class="badge" style="background: <?php echo $apt['status'] === 'approved' ? '#2ed573' : '#ffa502'; ?>;"><?php echo ucfirst($apt['status']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <a href="appointments.php" class="btn btn-outline-primary mt-3">View All Appointments</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-4">
                <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px;">
                    <h3 style="margin-bottom: 20px; font-weight: 700;">Quick Actions</h3>
                    <a href="doctors.php" class="btn btn-primary-gradient w-100 mb-2">
                        <i class="fas fa-search me-2"></i>Find Doctor
                    </a>
                    <a href="book_appointment.php" class="btn btn-primary-gradient w-100 mb-2">
                        <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                    </a>
                    <a href="reviews.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-star me-2"></i>Write Review
                    </a>
                </div>

                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h4 style="margin-bottom: 15px; font-weight: 600;"><i class="fas fa-lightbulb me-2"></i>Health Tip</h4>
                    <p style="font-size: 14px; margin: 0;">Remember to maintain regular check-ups with your doctor for better health outcomes.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
