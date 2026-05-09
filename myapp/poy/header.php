<?php
require_once 'functions.php';
if (!isLoggedIn()) {
    redirect('login.php');
}
$user = getUserById($_SESSION['user_id']);
$unread_notifications = getUnreadNotifications($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>MedCore Dashboard</title>
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --light-bg: #f4f7fe;
            --card-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        body {
            background-color: var(--light-bg);
            font-family: 'Inter', sans-serif;
        }
        #wrapper {
            display: flex;
            width: 100%;
        }
        #sidebar-wrapper {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: #fff;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            z-index: 1000;
            transition: all 0.3s;
        }
        #page-content-wrapper {
            width: 100%;
            padding: 30px;
        }
        .sidebar-heading {
            padding: 20px;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }
        .list-group-item {
            border: none;
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 10px;
            color: var(--secondary-color);
            font-weight: 500;
            transition: all 0.3s;
        }
        .list-group-item:hover {
            background-color: rgba(13, 110, 253, 0.05);
            color: var(--primary-color);
        }
        .list-group-item.active {
            background-color: var(--primary-color) !important;
            color: #fff !important;
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        .navbar-custom {
            background: transparent;
            padding: 20px 0;
        }
        .card-custom {
            border: none;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s;
        }
        .card-custom:hover {
            transform: translateY(-5px);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            padding: 4px 6px;
            border-radius: 50%;
            font-size: 0.6rem;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
        }

        /* Dark Mode Styles */
        body.dark-mode {
            background-color: #1a1c23;
            color: #e2e8f0;
        }
        body.dark-mode #sidebar-wrapper {
            background: #24262d;
            border-right: 1px solid #2d2f39;
        }
        body.dark-mode .card-custom, body.dark-mode .card {
            background-color: #24262d;
            color: #e2e8f0;
            border: 1px solid #2d2f39;
        }
        body.dark-mode .list-group-item {
            background-color: transparent;
            color: #94a3b8;
        }
        body.dark-mode .list-group-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #fff;
        }
        body.dark-mode .text-muted {
            color: #94a3b8 !important;
        }
        body.dark-mode .bg-light {
            background-color: #2d2f39 !important;
        }
        body.dark-mode .table {
            color: #e2e8f0;
        }
        body.dark-mode .navbar-custom h4 {
            color: #fff;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <i class="fas fa-hospital-user me-2"></i> MedCore
            </div>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-th-large me-2"></i> Dashboard
                </a>
                
                <?php if ($_SESSION['role'] == 'patient'): ?>
                    <a href="doctors.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'doctors.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-md me-2"></i> Find Doctors
                    </a>
                    <a href="appointments.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt me-2"></i> My Appointments
                    </a>
                <?php endif; ?>

                <?php if ($_SESSION['role'] == 'doctor'): ?>
                    <a href="doctor_schedule.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'doctor_schedule.php' ? 'active' : ''; ?>">
                        <i class="fas fa-clock me-2"></i> My Schedule
                    </a>
                    <a href="doctor_appointments.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'doctor_appointments.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-check me-2"></i> Appointments
                    </a>
                <?php endif; ?>

                <?php if ($_SESSION['role'] == 'secretary' || $_SESSION['role'] == 'admin'): ?>
                    <a href="manage_appointments.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'manage_appointments.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tasks me-2"></i> Manage Bookings
                    </a>
                    <a href="manage_doctors.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'manage_doctors.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-nurse me-2"></i> Doctors List
                    </a>
                <?php endif; ?>

                <a href="history.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history me-2"></i> Activity Log
                </a>
                <a href="notifications.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bell me-2"></i> Notifications
                    <?php if (count($unread_notifications) > 0): ?>
                        <span class="badge bg-danger rounded-pill float-end"><?php echo count($unread_notifications); ?></span>
                    <?php endif; ?>
                </a>
                <a href="logout.php" class="list-group-item list-group-item-action mt-auto text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-custom mb-4">
                <div class="container-fluid">
                    <h4 class="fw-bold m-0"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h4>
                    <div class="ms-auto d-flex align-items-center">
                        <div class="form-check form-switch me-3">
                            <input class="form-check-input" type="checkbox" id="darkModeToggle">
                            <label class="form-check-label small text-muted" for="darkModeToggle"><i class="fas fa-moon"></i></label>
                        </div>
                        <div class="dropdown me-3">
                            <a href="#" class="text-muted position-relative" id="notifDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-bell fa-lg"></i>
                                <?php if (count($unread_notifications) > 0): ?>
                                    <span class="badge bg-danger notification-badge"><?php echo count($unread_notifications); ?></span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="width: 300px;">
                                <li class="p-2 border-bottom"><h6 class="mb-0">Notifications</h6></li>
                                <?php if (empty($unread_notifications)): ?>
                                    <li class="p-3 text-center text-muted small">No new notifications</li>
                                <?php else: ?>
                                    <?php foreach ($unread_notifications as $notif): ?>
                                        <li><a class="dropdown-item small rounded p-2" href="notifications.php"><?php echo $notif['message']; ?></a></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <li class="text-center mt-2"><a href="notifications.php" class="small text-primary text-decoration-none">View All</a></li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="userDropdown" data-bs-toggle="dropdown">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&background=random" class="user-avatar me-2" alt="Avatar">
                                <div class="d-none d-md-block">
                                    <div class="fw-bold small"><?php echo $_SESSION['full_name']; ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;"><?php echo ucfirst($_SESSION['role']); ?></div>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
