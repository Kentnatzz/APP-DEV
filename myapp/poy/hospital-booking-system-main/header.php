<?php
/**
 * Navigation Header Component
 * Include this in every authenticated page
 */

if (!function_exists('renderHeader')) {
    function renderHeader($currentPage = '') {
        $user = [
            'name' => $_SESSION['user_name'] ?? 'User',
            'role' => ucfirst($_SESSION['user_role'] ?? 'user'),
            'email' => $_SESSION['user_email'] ?? '',
            'photo' => $_SESSION['profile_photo'] ?? 'https://via.placeholder.com/40'
        ];

        $notificationCount = 0;
        if (isset($GLOBALS['link'])) {
            global $link;
            $notificationCount = getUnreadNotificationCount($_SESSION['user_id'], $link);
        }
        
        ?>
        <nav class="navbar navbar-expand-lg navbar-light" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <div class="container-fluid px-4">
                <a class="navbar-brand" href="index.php" style="color: white; font-weight: 700;">
                    <i class="fas fa-hospital me-2"></i>MedCore Hospital
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php" style="color: white;">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                        </li>

                        <?php if ($_SESSION['user_role'] === 'patient'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="doctors.php" style="color: white;">
                                <i class="fas fa-stethoscope me-1"></i>Find Doctor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="appointments.php" style="color: white;">
                                <i class="fas fa-calendar me-1"></i>Appointments
                            </a>
                        </li>
                        <?php endif; ?>

                        <li class="nav-item position-relative">
                            <a class="nav-link" href="#notifications" style="color: white; position: relative;">
                                <i class="fas fa-bell me-1"></i>
                                <?php if ($notificationCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background: #ff4757;">
                                        <?php echo $notificationCount; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" style="color: white;" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <img src="<?php echo $user['photo']; ?>" alt="Profile" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                <span class="ms-2"><?php echo htmlspecialchars($user['name']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="history.php"><i class="fas fa-history me-2"></i>Activity History</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="api/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <?php
    }
}

?>
