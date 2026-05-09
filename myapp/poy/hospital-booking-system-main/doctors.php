<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCore Hospital - Find Doctors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; padding: 50px 20px; }
        .header { text-align: center; color: white; margin-bottom: 50px; }
        .header h1 { font-size: 36px; font-weight: 700; margin-bottom: 10px; }
        .doctor-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 30px; transition: all 0.3s ease; height: 100%; display: flex; flex-direction: column; }
        .doctor-card:hover { transform: translateY(-10px); box-shadow: 0 10px 35px rgba(0,0,0,0.2); }
        .doctor-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .doctor-avatar { width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 48px; margin: 0 auto 15px; object-fit: cover; }
        .doctor-name { font-size: 22px; font-weight: 700; margin-bottom: 5px; }
        .doctor-spec { font-size: 14px; opacity: 0.9; }
        .doctor-body { padding: 30px; flex-grow: 1; display: flex; flex-direction: column; }
        .doctor-info { margin: 15px 0; display: flex; align-items: center; }
        .doctor-info i { color: #667eea; margin-right: 15px; width: 20px; }
        .doctor-info span { color: #666; font-size: 14px; }
        .rating { color: #ffc107; }
        .btn-book { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; border-radius: 8px; padding: 10px 20px; width: 100%; margin-top: auto; transition: all 0.3s ease; }
        .btn-book:hover { transform: scale(1.02); color: white; opacity: 0.9; }
        .container-custom { max-width: 1200px; margin: 0 auto; }
        .search-box { background: white; padding: 20px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .search-box input, .search-box select { border-radius: 8px; border: 2px solid #e0e0e0; padding: 10px 15px; }
        .nav-link-custom { color: white; text-decoration: none; font-weight: 600; margin-bottom: 20px; display: inline-block; }
        .nav-link-custom:hover { color: #e0e0e0; }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once 'config.php';
    require_once 'functions.php';

    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    $specialization = $_GET['specialization'] ?? '';
    $doctors = getDoctors($link, $specialization ?: null);
    $specializations = getDoctorSpecializations($link);
    ?>

    <div class="container-custom">
        <a href="index.php" class="nav-link-custom"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
        
        <div class="header">
            <h1><i class="fas fa-stethoscope me-2"></i>Find Our Doctors</h1>
            <p>Choose from our qualified medical professionals</p>
        </div>

        <div class="search-box">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Search doctor by name..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="specialFilter" onchange="filterDoctors()">
                        <option value="">All Specializations</option>
                        <?php foreach ($specializations as $spec): ?>
                            <option value="<?php echo htmlspecialchars($spec); ?>" <?php echo $specialization === $spec ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($spec); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row" id="doctorsContainer">
            <?php if (empty($doctors)): ?>
                <div class="col-12" style="text-align: center; color: white;">
                    <i class="fas fa-inbox" style="font-size: 48px; margin: 20px 0;"></i>
                    <p>No doctors found</p>
                </div>
            <?php else: ?>
                <?php foreach ($doctors as $doctor): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="doctor-card">
                            <div class="doctor-header">
                                <?php if (!empty($doctor['profile_photo'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($doctor['profile_photo']); ?>" alt="Dr. <?php echo htmlspecialchars($doctor['first_name']); ?>" class="doctor-avatar">
                                <?php else: ?>
                                    <div class="doctor-avatar">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="doctor-name">Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?></div>
                                <div class="doctor-spec"><?php echo htmlspecialchars($doctor['specialization']); ?></div>
                            </div>
                            <div class="doctor-body">
                                <div class="doctor-info">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span><?php echo $doctor['experience_years']; ?> years experience</span>
                                </div>
                                <div class="doctor-info">
                                    <i class="fas fa-star rating"></i>
                                    <span><?php echo number_format($doctor['rating'], 1); ?>/5 • <strong><?php echo $doctor['total_appointments']; ?> appointments</strong></span>
                                </div>
                                <div class="doctor-info">
                                    <i class="fas fa-dollar-sign"></i>
                                    <span>Consultation: $<?php echo number_format($doctor['consultation_fee'], 2); ?></span>
                                </div>
                                <div class="doctor-info">
                                    <i class="fas fa-door-open"></i>
                                    <span>Room <?php echo htmlspecialchars($doctor['room_number'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="d-flex gap-2 mt-auto">
                                    <a href="doctor_profile.php?doctor_id=<?php echo $doctor['id']; ?>" class="btn btn-outline-primary flex-grow-1">
                                        <i class="fas fa-user me-2"></i>Profile
                                    </a>
                                    <a href="book_appointment.php?doctor_id=<?php echo $doctor['id']; ?>" class="btn btn-book flex-grow-1 mt-0">
                                        <i class="fas fa-calendar-plus me-2"></i>Book
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.doctor-card').forEach(card => {
                const name = card.querySelector('.doctor-name').textContent.toLowerCase();
                const spec = card.querySelector('.doctor-spec').textContent.toLowerCase();
                if (name.includes(searchTerm) || spec.includes(searchTerm)) {
                    card.parentElement.style.display = 'block';
                } else {
                    card.parentElement.style.display = 'none';
                }
            });
        });

        function filterDoctors() {
            const spec = document.getElementById('specialFilter').value;
            window.location.href = spec ? '?specialization=' + encodeURIComponent(spec) : 'doctors.php';
        }
    </script>
</body>
</html>
