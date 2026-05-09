<?php
require_once 'functions.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];

    if (empty($full_name) || empty($username) || empty($email) || empty($password) || empty($dob)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Username or email already exists.";
        } else {
            try {
                $pdo->beginTransaction();

                // Create User
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'patient')");
                $stmt->execute([$username, $hashed_password, $email, $full_name]);
                $user_id = $pdo->lastInsertId();

                // Create Patient profile
                $stmt = $pdo->prepare("INSERT INTO patients (user_id, dob, gender) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $dob, $gender]);

                $pdo->commit();
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                logActivity($user_id, 'Registration', 'New patient registered');
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MedCore Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            padding: 40px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 10px;
        }
        .btn-register {
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="text-center mb-4">
            <i class="fas fa-hospital-user fa-3x text-primary mb-2"></i>
            <h2 class="fw-bold">Create Account</h2>
            <p class="text-muted">Join MedCore Hospital today.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label small text-muted">Full Name</label>
                    <input type="text" name="full_name" class="form-control" placeholder="John Doe" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small text-muted">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small text-muted">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small text-muted">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small text-muted">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small text-muted">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label small text-muted">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-register text-white mb-3">Register</button>
            <p class="text-center text-muted small">Already have an account? <a href="login.php" class="text-primary text-decoration-none fw-bold">Login</a></p>
        </form>
    </div>
</body>
</html>
