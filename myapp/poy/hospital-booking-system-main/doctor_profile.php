<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$doctorId = $_GET['doctor_id'] ?? null;
$doctor = null;

if ($doctorId) {
    $doctor = getDoctorById($doctorId, $link);
}

if (!$doctor) {
    header('HTTP/1.0 404 Not Found');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Doctor Not Found</title></head><body><h1>Doctor not found</h1><p>The requested profile was not found.</p><p><a href="doctors.php">Back to search</a></p></body></html>';
    exit;
}

$reviews = getDoctorReviews($doctor['id'], $link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?> - MedCore Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        body { background: #f8fafc; color: #0f172a; padding: 40px 20px; }
        .profile-container { max-width: 1000px; margin: 0 auto; }
        .profile-card { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .profile-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; }
        .doctor-avatar { width: 150px; height: 150px; border-radius: 30px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 64px; margin-right: 30px; object-fit: cover; }
        .profile-info h1 { font-size: 32px; font-weight: 700; margin-bottom: 10px; }
        .profile-info p { opacity: 0.9; font-size: 18px; }
        .profile-content { padding: 40px; }
        .section-title { font-size: 20px; font-weight: 700; margin-bottom: 20px; color: #333; display: flex; align-items: center; }
        .section-title i { color: #667eea; margin-right: 15px; }
        .stat-card { background: #f1f5f9; padding: 20px; border-radius: 15px; text-align: center; }
        .stat-value { display: block; font-size: 24px; font-weight: 700; color: #667eea; }
        .stat-label { font-size: 14px; color: #64748b; }
        .review-card { padding: 20px; border-bottom: 1px solid #e2e8f0; }
        .review-card:last-child { border-bottom: none; }
        .review-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .rating-stars { color: #ffc107; }
        .btn-book { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; border-radius: 12px; padding: 15px 30px; font-weight: 700; width: 100%; font-size: 18px; box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3); transition: all 0.3s ease; }
        .btn-book:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4); color: white; }
    </style>
</head>
<body>
    <div class="profile-container">
        <a href="doctors.php" class="btn btn-link text-decoration-none mb-4 text-muted">
            <i class="fas fa-arrow-left me-2"></i>Back to Doctors
        </a>

        <div class="profile-card">
            <div class="profile-header d-flex align-items-center flex-wrap">
                <?php if (!empty($doctor['profile_photo'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($doctor['profile_photo']); ?>" alt="Dr. <?php echo htmlspecialchars($doctor['first_name']); ?>" class="doctor-avatar">
                <?php else: ?>
                    <div class="doctor-avatar">
                        <i class="fas fa-user-md"></i>
                    </div>
                <?php endif; ?>
                <div class="profile-info">
                    <h1>Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?></h1>
                    <p><i class="fas fa-stethoscope me-2"></i><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                    <div class="rating-stars mt-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?php echo $i <= $doctor['rating'] ? '' : '-o'; ?>"></i>
                        <?php endfor; ?>
                        <span class="ms-2 text-white-50">(<?php echo number_format($doctor['rating'], 1); ?>/5)</span>
                    </div>
                </div>
            </div>

            <div class="profile-content">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-5">
                            <h2 class="section-title"><i class="fas fa-info-circle"></i>About Doctor</h2>
                            <p class="text-muted" style="line-height: 1.8;">
                                <?php echo nl2br(htmlspecialchars($doctor['bio'] ?: "Dr. " . $doctor['last_name'] . " is a highly qualified " . $doctor['specialization'] . " with " . $doctor['experience_years'] . " years of experience in the field. Committed to providing exceptional patient care and utilizing the latest medical advancements.")); ?>
                            </p>
                        </div>

                        <div class="mb-5">
                            <h2 class="section-title"><i class="fas fa-star"></i>Patient Reviews</h2>
                            <?php if (empty($reviews)): ?>
                                <p class="text-muted">No reviews yet for this doctor.</p>
                            <?php else: ?>
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-card">
                                        <div class="review-header">
                                            <strong><?php echo htmlspecialchars($review['first_name']); ?></strong>
                                            <span class="text-muted small"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                        </div>
                                        <div class="rating-stars mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="mb-0 text-muted"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="stat-card">
                                    <span class="stat-value"><?php echo $doctor['experience_years']; ?></span>
                                    <span class="stat-label">Years Exp.</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <span class="stat-value"><?php echo $doctor['total_appointments']; ?></span>
                                    <span class="stat-label">Patients</span>
                                </div>
                            </div>
                        </div>

                        <div class="section-card bg-light p-4 rounded-4 mb-4">
                            <h2 class="section-title mb-3" style="font-size: 18px;"><i class="fas fa-clock"></i>Availability</h2>
                            <ul class="list-unstyled text-muted small">
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Mon - Fri: 08:00 AM - 05:00 PM</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Saturday: 09:00 AM - 01:00 PM</li>
                                <li><i class="fas fa-times-circle text-danger me-2"></i>Sunday: Closed</li>
                            </ul>
                        </div>

                        <div class="section-card bg-light p-4 rounded-4 mb-4">
                            <h2 class="section-title mb-3" style="font-size: 18px;"><i class="fas fa-dollar-sign"></i>Fees</h2>
                            <p class="h4 text-primary fw-bold mb-1">$<?php echo number_format($doctor['consultation_fee'], 2); ?></p>
                            <p class="text-muted small">Per consultation</p>
                        </div>

                        <a href="book_appointment.php?doctor_id=<?php echo $doctor['id']; ?>" class="btn btn-book">
                            <i class="fas fa-calendar-check me-2"></i>Book Appointment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
