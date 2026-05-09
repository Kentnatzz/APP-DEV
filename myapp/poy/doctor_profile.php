<?php
require_once 'functions.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT d.*, u.full_name, u.profile_pic, u.email 
                      FROM doctors d 
                      JOIN users u ON d.user_id = u.id 
                      WHERE d.id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    redirect('doctors.php');
}

$page_title = "Dr. " . $doctor['full_name'];
require_once 'header.php';

// Fetch Reviews
$stmt = $pdo->prepare("SELECT r.*, u.full_name as patient_name 
                      FROM reviews r 
                      JOIN appointments a ON r.appointment_id = a.id 
                      JOIN patients p ON a.patient_id = p.id 
                      JOIN users u ON p.user_id = u.id 
                      WHERE a.doctor_id = ? 
                      ORDER BY r.created_at DESC");
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

// Fetch Schedule
$stmt = $pdo->prepare("SELECT * FROM schedules WHERE doctor_id = ?");
$stmt->execute([$id]);
$schedules = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card card-custom border-0 text-center p-4 mb-4">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($doctor['full_name']); ?>&size=150&background=random" class="rounded-circle mx-auto mb-3 shadow-sm" style="width: 150px; height: 150px;">
                <h4 class="fw-bold mb-1"><?php echo $doctor['full_name']; ?></h4>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 mb-3"><?php echo $doctor['specialization']; ?></span>
                <p class="text-muted small"><?php echo $doctor['bio']; ?></p>
                <hr>
                <div class="row g-2 text-start">
                    <div class="col-6">
                        <small class="text-muted d-block">Experience</small>
                        <span class="fw-bold"><?php echo $doctor['experience']; ?> Years</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Consultation Fee</small>
                        <span class="fw-bold text-primary"><?php echo formatCurrency($doctor['consultation_fee']); ?></span>
                    </div>
                    <div class="col-6 mt-3">
                        <small class="text-muted d-block">Room Number</small>
                        <span class="fw-bold"><?php echo $doctor['room_number']; ?></span>
                    </div>
                    <div class="col-6 mt-3">
                        <small class="text-muted d-block">Contact</small>
                        <span class="fw-bold small"><?php echo $doctor['contact_number']; ?></span>
                    </div>
                </div>
            </div>

            <div class="card card-custom border-0 p-4">
                <h5 class="fw-bold mb-3">Weekly Schedule</h5>
                <?php if (empty($schedules)): ?>
                    <p class="text-muted small">No specific schedule set.</p>
                <?php else: ?>
                    <?php foreach ($schedules as $sch): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded-3">
                            <span class="small fw-bold"><?php echo $sch['day_of_week']; ?></span>
                            <span class="small text-muted"><?php echo date('h:i A', strtotime($sch['start_time'])); ?> - <?php echo date('h:i A', strtotime($sch['end_time'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-custom border-0 p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Patient Reviews</h5>
                    <div class="d-flex align-items-center text-warning">
                        <?php 
                        $avg = count($reviews) > 0 ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;
                        for($i=1; $i<=5; $i++) echo $i <= $avg ? '<i class="fas fa-star me-1"></i>' : '<i class="far fa-star me-1"></i>';
                        ?>
                        <span class="text-muted small ms-2">(<?php echo number_format($avg, 1); ?>)</span>
                    </div>
                </div>

                <?php if (empty($reviews)): ?>
                    <div class="text-center py-5">
                        <p class="text-muted">No reviews yet for this doctor.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                        <div class="mb-4 pb-4 border-bottom last-child-border-0">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="fw-bold mb-0"><?php echo $rev['patient_name']; ?></h6>
                                <div class="text-warning small">
                                    <?php for($i=1; $i<=5; $i++) echo $i <= $rev['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                                </div>
                            </div>
                            <p class="text-muted small mb-1"><?php echo $rev['comment']; ?></p>
                            <small class="text-muted" style="font-size: 0.7rem;"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($_SESSION['role'] == 'patient'): ?>
            <div class="card card-custom border-0 bg-primary text-white p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="fw-bold">Ready to consult?</h5>
                        <p class="mb-0 opacity-75">Book your appointment now and get expert medical advice.</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="doctors.php" class="btn btn-light rounded-pill px-4 fw-bold text-primary">Book Appointment</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
