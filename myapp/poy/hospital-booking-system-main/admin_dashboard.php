<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCore Hospital - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
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
        .chart-container { position: relative; height: 300px; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; min-height: auto; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once 'config.php';
    require_once 'functions.php';

    if (!isLoggedIn() || !hasRole('admin')) {
        header('Location: login.php');
        exit;
    }

    $stats = getDashboardStats($link);
    ?>

    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-hospital me-2"></i>MedCore
        </div>
        <ul class="nav-menu">
            <li><a href="admin_dashboard.php" class="active"><i class="fas fa-home"></i>Dashboard</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i>Users</a></li>
            <li><a href="doctors.php"><i class="fas fa-stethoscope"></i>Doctors</a></li>
            <li><a href="appointments.php"><i class="fas fa-calendar"></i>Appointments</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i>Reports</a></li>
            <li><a href="api/logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1 style="font-size: 28px; font-weight: 700; color: #333; margin: 0;">Admin Dashboard</h1>
            <p style="color: #999; margin-top: 5px;">System Overview</p>
        </div>

        <div class="row">
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
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                    <div class="stat-value"><?php echo $stats['total_appointments']; ?></div>
                    <div class="stat-label">Appointments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-star"></i></div>
                    <div class="stat-value"><?php echo $stats['total_reviews']; ?></div>
                    <div class="stat-label">Reviews</div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 style="margin-bottom: 15px;">Appointment Status Distribution</h5>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 style="margin-bottom: 15px;">System Metrics</h5>
                    <canvas id="metricsChart"></canvas>
                </div>
            </div>
        </div>

        <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-top: 20px;">
            <h3 style="margin-bottom: 20px; font-weight: 700;">Quick Actions</h3>
            <a href="users.php" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;"><i class="fas fa-user-plus me-2"></i>Add User</a>
            <a href="doctors.php" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;"><i class="fas fa-user-md me-2"></i>Add Doctor</a>
            <a href="appointments.php" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;"><i class="fas fa-calendar-plus me-2"></i>View All</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Pending', 'Cancelled', 'Approved'],
                datasets: [{
                    data: [<?php echo $stats['completed_appointments']; ?>, <?php echo $stats['pending_appointments']; ?>, <?php echo $stats['cancelled_appointments']; ?>, <?php echo $stats['total_appointments'] - $stats['completed_appointments'] - $stats['pending_appointments'] - $stats['cancelled_appointments']; ?>],
                    backgroundColor: ['#2ed573', '#ffa502', '#ff4757', '#667eea']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Metrics Chart
        const metricsCtx = document.getElementById('metricsChart').getContext('2d');
        new Chart(metricsCtx, {
            type: 'bar',
            data: {
                labels: ['Patients', 'Doctors', 'Appointments', 'Reviews'],
                datasets: [{
                    label: 'Count',
                    data: [<?php echo $stats['total_patients']; ?>, <?php echo $stats['total_doctors']; ?>, <?php echo $stats['total_appointments']; ?>, <?php echo $stats['total_reviews']; ?>],
                    backgroundColor: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
</body>
</html>
