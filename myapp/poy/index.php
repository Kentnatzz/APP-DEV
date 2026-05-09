<?php
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCore Hospital - Modern Healthcare Excellence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #0d6efd;
            --secondary: #6c757d;
            --accent: #00d2ff;
            --dark: #212529;
            --light: #f8f9fa;
        }
        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }
        .navbar {
            padding: 20px 0;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .hero-section {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(0, 210, 255, 0.05) 100%);
            padding: 120px 0 100px;
            position: relative;
        }
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            color: var(--dark);
        }
        .btn-primary-custom {
            background: var(--primary);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            color: white;
            text-decoration: none;
        }
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(13, 110, 253, 0.2);
            color: white;
        }
        .feature-card {
            padding: 40px;
            border-radius: 20px;
            border: none;
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            background: rgba(13, 110, 253, 0.1);
            color: var(--primary);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 25px;
        }
        .doctor-card {
            border-radius: 20px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .doctor-img {
            height: 300px;
            object-fit: cover;
        }
        .footer {
            background: var(--dark);
            color: white;
            padding: 80px 0 30px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fas fa-hospital-user text-primary fa-2x me-2"></i>
                <span class="fw-bold fs-3 text-dark">MedCore</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link mx-2 fw-semibold" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link mx-2 fw-semibold" href="#doctors">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link mx-2 fw-semibold" href="#about">About</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item"><a class="btn btn-primary-custom ms-lg-3" href="dashboard.php">Dashboard</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link mx-2 fw-semibold" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="btn btn-primary-custom ms-lg-3" href="register.php">Book Appointment</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 animate__animated animate__fadeInLeft">
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-3">Healthcare Reimagined</span>
                    <h1 class="hero-title mb-4">Your Health is Our <br><span class="text-primary">Top Priority</span></h1>
                    <p class="lead text-muted mb-5">Experience world-class healthcare with MedCore. Book appointments with top specialists, manage your records, and receive expert care all in one place.</p>
                    <div class="d-flex gap-3">
                        <a href="register.php" class="btn btn-primary-custom">Get Started</a>
                        <a href="#doctors" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold">Find a Doctor</a>
                    </div>
                    <div class="mt-5 d-flex gap-4">
                        <div>
                            <h4 class="fw-bold mb-0">20+</h4>
                            <p class="small text-muted">Specialists</p>
                        </div>
                        <div class="vr"></div>
                        <div>
                            <h4 class="fw-bold mb-0">10k+</h4>
                            <p class="small text-muted">Patients</p>
                        </div>
                        <div class="vr"></div>
                        <div>
                            <h4 class="fw-bold mb-0">15+</h4>
                            <p class="small text-muted">Years Exp.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block animate__animated animate__fadeInRight">
                    <img src="https://img.freepik.com/free-photo/doctor-offering-medical-teleconsultation-patient_23-2149329007.jpg" alt="Doctor" class="img-fluid rounded-4 shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-100 bg-white" style="padding: 100px 0;">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold fs-1">Our Premium Services</h2>
                <p class="text-muted">Designed to provide the best medical experience</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                        <h4 class="fw-bold mb-3">Easy Booking</h4>
                        <p class="text-muted">Schedule appointments with your favorite doctors in just a few clicks.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-user-md"></i></div>
                        <h4 class="fw-bold mb-3">Expert Doctors</h4>
                        <p class="text-muted">Access to highly qualified specialists across various medical fields.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                        <h4 class="fw-bold mb-3">Health Tracking</h4>
                        <p class="text-muted">Monitor your medical history and upcoming appointments effortlessly.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-4">
                        <i class="fas fa-hospital-user text-primary fa-2x me-2"></i>
                        <span class="fw-bold fs-3">MedCore</span>
                    </div>
                    <p class="text-muted pe-lg-5">MedCore Hospital is committed to providing exceptional healthcare services with state-of-the-art technology and expert medical professionals.</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="#" class="text-muted"><i class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-linkedin-in fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-lg-0">
                    <h5 class="fw-bold mb-4">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Doctors</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Services</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-lg-0">
                    <h5 class="fw-bold mb-4">Support</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5 class="fw-bold mb-4">Contact Us</h5>
                    <ul class="list-unstyled">
                        <li class="mb-3 text-muted"><i class="fas fa-map-marker-alt text-primary me-2"></i> 123 Medical Plaza, Health City</li>
                        <li class="mb-3 text-muted"><i class="fas fa-phone-alt text-primary me-2"></i> +1 (234) 567-890</li>
                        <li class="mb-3 text-muted"><i class="fas fa-envelope text-primary me-2"></i> contact@medcore.com</li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center text-muted small mt-4">
                <p>&copy; 2026 MedCore Hospital Booking System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
