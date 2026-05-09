<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = getCurrentUserId();
$user = getUserById($userId, $link);
$role = $user['role'];

// Get role-specific info
$roleInfo = null;
if ($role === 'patient') {
    $roleInfo = getPatientByUserId($userId, $link);
} elseif ($role === 'doctor') {
    $roleInfo = getDoctorByUserId($userId, $link);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($firstName) || empty($lastName)) {
        $error = 'First and last name are required';
    } else {
        $firstName = mysqli_real_escape_string($link, $firstName);
        $lastName = mysqli_real_escape_string($link, $lastName);
        $phone = mysqli_real_escape_string($link, $phone);
        
        $query = "UPDATE users SET first_name = '$firstName', last_name = '$lastName', phone = '$phone' WHERE id = $userId";
        if (mysqli_query($link, $query)) {
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            $success = 'Profile updated successfully';
            $user = getUserById($userId, $link);
        } else {
            $error = 'Failed to update profile: ' . mysqli_error($link);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MedCore Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .container-custom { max-width: 800px; margin: 50px auto; }
        .profile-card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .profile-header { text-align: center; margin-bottom: 40px; }
        .avatar-container { width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 48px; margin: 0 auto 20px; position: relative; }
        .form-label { font-weight: 600; color: #333; }
        .btn-update { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; padding: 12px 30px; border-radius: 10px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container-custom">
        <a href="index.php" class="btn btn-link text-decoration-none mb-4 text-muted">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>

        <div class="profile-card">
            <div class="profile-header">
                <div class="avatar-container">
                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                <span class="badge bg-primary"><?php echo ucfirst($role); ?></span>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="update_profile" value="1">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <small class="text-muted">Email cannot be changed.</small>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <?php if ($role === 'patient' && $roleInfo): ?>
                        <div class="col-md-4">
                            <label class="form-label">Age</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($roleInfo['age'] ?? 'N/A'); ?>" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Blood Group</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($roleInfo['blood_group'] ?? 'N/A'); ?>" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <input type="text" class="form-control" value="<?php echo ucfirst($roleInfo['gender'] ?? 'N/A'); ?>" disabled>
                        </div>
                    <?php endif; ?>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-update w-100">Update Profile</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
