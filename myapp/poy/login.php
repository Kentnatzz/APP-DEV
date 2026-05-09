<?php
require_once 'functions.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            logActivity($user['id'], 'Login', 'User logged in successfully');
            redirect('dashboard.php');
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MedCore Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        body {
            background: var(--bg-gradient);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            transition: transform 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
        }
        .hospital-logo {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ddd;
        }
        .btn-login {
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            background: var(--primary-color);
            border: none;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #0056b3;
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
    </style>
</head>
<body>
    <div class="login-card text-center">
        <div class="hospital-logo">
            <i class="fas fa-hospital-user"></i>
        </div>
        <h2 class="mb-4 fw-bold">MedCore</h2>
        <p class="text-muted mb-4">Welcome back! Please login to your account.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3 text-start">
                <label class="form-label text-muted small">Username or Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" name="username" class="form-control border-start-0" placeholder="Enter username" required>
                </div>
            </div>
            <div class="mb-4 text-start">
                <label class="form-label text-muted small">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="Enter password" required>
                </div>
            </div>
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label small text-muted" for="remember">Remember me</label>
                </div>
                <a href="#" class="small text-primary text-decoration-none">Forgot Password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-login mb-3 text-white">Login</button>
            <p class="text-muted small">Don't have an account? <a href="register.php" class="text-primary text-decoration-none fw-bold">Register here</a></p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
