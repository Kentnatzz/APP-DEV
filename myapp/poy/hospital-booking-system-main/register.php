<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCore Hospital - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow: hidden;
            padding: 20px 0;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            margin: 0 auto 20px;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .register-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }

        .register-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            color: white;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .role-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .role-card {
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .role-card:hover {
            border-color: #667eea;
        }

        .role-card.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }

        .role-card i {
            font-size: 24px;
            margin-bottom: 8px;
            display: block;
        }

        .role-card .role-name {
            font-weight: 600;
            font-size: 14px;
        }

        .password-strength {
            height: 5px;
            background: #e0e0e0;
            border-radius: 5px;
            margin-top: 5px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }

        .terms-check {
            font-size: 13px;
            margin: 15px 0;
            color: #666;
        }

        .terms-check a {
            color: #667eea;
            text-decoration: none;
        }

        .terms-check a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="logo">
                <i class="fas fa-hospital"></i>
            </div>
            <h1>Create Account</h1>
            <p>Join MedCore Hospital System</p>
        </div>

        <?php
        session_start();
        require_once 'config.php';
        require_once 'functions.php';

        $error = '';
        $success = '';

        // Handle registration
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_action'])) {
            $action = $_POST['register_action'];

            if ($action === 'register') {
                $firstName = trim($_POST['first_name'] ?? '');
                $lastName = trim($_POST['last_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $password = trim($_POST['password'] ?? '');
                $confirmPassword = trim($_POST['confirm_password'] ?? '');
                $role = trim($_POST['role'] ?? 'patient');

                // Validation
                if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
                    $error = 'Please fill in all required fields';
                } elseif ($password !== $confirmPassword) {
                    $error = 'Passwords do not match';
                } elseif (strlen($password) < 8) {
                    $error = 'Password must be at least 8 characters';
                } else {
                    $result = registerUser($firstName, $lastName, $email, $phone, $password, $role, $link);
                    if ($result['success']) {
                        $_SESSION['register_success'] = 'Registration successful! Please log in.';
                        header('Location: login.php');
                        exit;
                    } else {
                        $error = $result['message'];
                    }
                }
            }
        }
        ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="register_action" value="register">
            <input type="hidden" name="role" id="selectedRole" value="patient">

            <div class="form-group">
                <label>Account Type</label>
                <div class="role-cards">
                    <div class="role-card selected" onclick="selectRole('patient', this)">
                        <i class="fas fa-user"></i>
                        <div class="role-name">Patient</div>
                    </div>
                    <div class="role-card" onclick="selectRole('doctor', this)">
                        <i class="fas fa-stethoscope"></i>
                        <div class="role-name">Doctor</div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" onkeyup="checkPasswordStrength()" required>
                <div class="password-strength">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>
                <small class="text-muted">At least 8 characters</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="terms-check">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" required>
                    I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="btn btn-register">
                <i class="fas fa-user-plus me-2"></i>Create Account
            </button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Sign in here</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectRole(role, element) {
            // Remove selected class from all cards
            document.querySelectorAll('.role-card').forEach(card => {
                card.classList.remove('selected');
            });
            // Add selected class to clicked card
            element.classList.add('selected');
            // Update hidden role field
            document.getElementById('selectedRole').value = role;
        }

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('strengthBar');
            let strength = 0;

            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 25;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 12;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 13;

            strengthBar.style.width = Math.min(strength, 100) + '%';

            if (strength < 40) {
                strengthBar.style.background = '#ff4757';
            } else if (strength < 70) {
                strengthBar.style.background = '#ffa502';
            } else {
                strengthBar.style.background = '#2ed573';
            }
        }
    </script>
</body>
</html>
