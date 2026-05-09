<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCore - Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .main-container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .review-card { background: white; border-radius: 15px; padding: 25px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .stars { color: #ffc107; font-size: 14px; }
        .header { margin-bottom: 30px; }
        .header h1 { font-weight: 700; color: #333; }
        .btn-submit { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; }
        .btn-submit:hover { color: white; }
        .modal-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
        .modal-header .btn-close { filter: brightness(0) invert(1); }
        .star-rating { display: flex; gap: 5px; font-size: 30px; }
        .star-rating i { cursor: pointer; color: #ddd; transition: all 0.2s; }
        .star-rating i:hover, .star-rating i.active { color: #ffc107; }
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

    $role = $_SESSION['user_role'];
    $userId = getCurrentUserId();
    $reviews = [];
    $completedAppointments = [];

    if ($role === 'patient') {
        $patient = getPatientByUserId($userId, $link);
        // Get all reviews by this patient
        $query = "SELECT r.*, u.first_name as doctor_first_name, u.last_name as doctor_last_name 
                  FROM reviews r 
                  JOIN doctors d ON r.doctor_id = d.id 
                  JOIN users u ON d.user_id = u.id 
                  WHERE r.patient_id = " . $patient['id'] . " 
                  ORDER BY r.created_at DESC";
        $result = mysqli_query($link, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $reviews[] = $row;
        }

        // Get completed appointments without reviews
        $completedAppointments = getAppointments($link, ['patient_id' => $patient['id'], 'status' => 'completed']);
    } elseif ($role === 'doctor') {
        $doctor = getDoctorByUserId($userId, $link);
        $reviews = getDoctorReviews($doctor['id'], $link, 50);
    } else {
        // Admin/Secretary view all reviews
        $query = "SELECT r.*, u1.first_name as patient_first_name, u1.last_name as patient_last_name, 
                         u2.first_name as doctor_first_name, u2.last_name as doctor_last_name 
                  FROM reviews r 
                  JOIN patients p ON r.patient_id = p.id 
                  JOIN users u1 ON p.user_id = u1.id 
                  JOIN doctors d ON r.doctor_id = d.id 
                  JOIN users u2 ON d.user_id = u2.id 
                  ORDER BY r.created_at DESC";
        $result = mysqli_query($link, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $reviews[] = $row;
        }
    }
    ?>

    <div class="main-container">
        <div class="header d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-star me-2"></i>Reviews</h1>
                <p class="text-muted">Patient feedback and ratings</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-2"></i>Dashboard</a>
        </div>

        <?php if ($role === 'patient'): ?>
            <button class="btn btn-submit mb-4" data-bs-toggle="modal" data-bs-target="#reviewModal">
                <i class="fas fa-plus me-2"></i>Write Review
            </button>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
            <div style="text-align: center; padding: 60px 20px;">
                <i class="fas fa-inbox" style="font-size: 64px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                <p style="color: #999;">No reviews yet</p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $r): ?>
                <div class="review-card">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                        <div>
                            <h5 style="margin: 0; font-weight: 600;">
                                <?php if ($role === 'patient'): ?>
                                    To: Dr. <?php echo htmlspecialchars($r['doctor_first_name'] . ' ' . $r['doctor_last_name']); ?>
                                <?php elseif ($role === 'doctor'): ?>
                                    From: <?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($r['patient_first_name'] . ' ' . $r['patient_last_name']); ?> to Dr. <?php echo htmlspecialchars($r['doctor_first_name'] . ' ' . $r['doctor_last_name']); ?>
                                <?php endif; ?>
                            </h5>
                            <small class="text-muted"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></small>
                        </div>
                        <div class="stars">
                            <?php for ($i = 0; $i < $r['rating']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                            <?php for ($i = $r['rating']; $i < 5; $i++): ?>
                                <i class="far fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p style="margin: 0; color: #666;"><?php echo htmlspecialchars($r['comment']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 15px;">
                <div class="modal-header" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title">Write a Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: brightness(0) invert(1);"></button>
                </div>
                <div class="modal-body">
                    <form id="reviewForm">
                        <div class="mb-3">
                            <label class="form-label">Select Appointment</label>
                            <select class="form-select" id="appointmentSelect" required>
                                <option value="">Choose a completed appointment...</option>
                                <?php foreach ($completedAppointments as $apt): ?>
                                    <option value="<?php echo $apt['id']; ?>" data-doctor-id="<?php echo $apt['doctor_id']; ?>">
                                        Dr. <?php echo htmlspecialchars($apt['doctor_first_name'] . ' ' . $apt['doctor_last_name']); ?> - <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="star-rating" id="starRating">
                                <i class="far fa-star" data-value="1"></i>
                                <i class="far fa-star" data-value="2"></i>
                                <i class="far fa-star" data-value="3"></i>
                                <i class="far fa-star" data-value="4"></i>
                                <i class="far fa-star" data-value="5"></i>
                            </div>
                            <input type="hidden" id="ratingValue" name="rating" value="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Review</label>
                            <textarea class="form-control" id="reviewText" name="comment" rows="4" placeholder="Share your experience..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-submit w-100">Submit Review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('#starRating i').forEach(star => {
            star.addEventListener('click', function() {
                const value = this.dataset.value;
                document.getElementById('ratingValue').value = value;
                document.querySelectorAll('#starRating i').forEach(s => {
                    const sVal = s.dataset.value;
                    if (sVal <= value) {
                        s.classList.remove('far');
                        s.classList.add('fas', 'active');
                    } else {
                        s.classList.remove('fas', 'active');
                        s.classList.add('far');
                    }
                });
            });
        });

        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const rating = document.getElementById('ratingValue').value;
            if (rating == 0) {
                alert('Please select a rating');
                return;
            }

            const appointmentSelect = document.getElementById('appointmentSelect');
            const appointmentId = appointmentSelect.value;
            const doctorId = appointmentSelect.options[appointmentSelect.selectedIndex].dataset.doctorId;
            const comment = document.getElementById('reviewText').value;

            fetch('api/submit_review.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    appointment_id: appointmentId,
                    doctor_id: doctorId,
                    rating: rating,
                    comment: comment
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Review submitted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting your review.');
            });
        });
    </script>
</body>
</html>
