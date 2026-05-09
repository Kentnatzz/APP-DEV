<?php
$page_title = "Patient Dashboard";
require_once 'header.php';

// Get statistics
$stmt = $pdo->prepare("SELECT 
    (SELECT COUNT(*) FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE p.user_id = ? AND a.status = 'pending') as pending_count,
    (SELECT COUNT(*) FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE p.user_id = ? AND a.status = 'approved') as approved_count,
    (SELECT COUNT(*) FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE p.user_id = ? AND a.status = 'completed') as completed_count");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$stats = $stmt->fetch();

// Get upcoming appointments
$stmt = $pdo->prepare("SELECT a.*, d.specialization, u.full_name as doctor_name 
                      FROM appointments a 
                      JOIN doctors d ON a.doctor_id = d.id 
                      JOIN users u ON d.user_id = u.id 
                      JOIN patients p ON a.patient_id = p.id 
                      WHERE p.user_id = ? AND a.appointment_date >= CURDATE() AND a.status != 'cancelled'
                      ORDER BY a.appointment_date ASC, a.appointment_time ASC LIMIT 3");
$stmt->execute([$_SESSION['user_id']]);
$upcoming = $stmt->fetchAll();

// Get recommended doctors
$stmt = $pdo->prepare("SELECT d.*, u.full_name FROM doctors d JOIN users u ON d.user_id = u.id ORDER BY d.experience DESC LIMIT 4");
$stmt->execute();
$recommended = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-custom border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fas fa-clock text-warning fa-2x"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['pending_count']; ?></h4>
                        <p class="text-muted small mb-0">Pending Bookings</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-calendar-check text-primary fa-2x"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['approved_count']; ?></h4>
                        <p class="text-muted small mb-0">Approved Visits</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-check-circle text-success fa-2x"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['completed_count']; ?></h4>
                        <p class="text-muted small mb-0">Completed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card card-custom border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Upcoming Appointments</h5>
                        <a href="appointments.php" class="text-primary small text-decoration-none">View All</a>
                    </div>
                    <?php if (empty($upcoming)): ?>
                        <div class="text-center py-5">
                            <img src="https://illustrations.popsy.co/blue/calendar.svg" alt="No data" style="width: 150px;" class="mb-3">
                            <p class="text-muted">No upcoming appointments.</p>
                            <a href="doctors.php" class="btn btn-primary btn-sm rounded-pill">Book Now</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming as $app): ?>
                            <div class="d-flex align-items-center p-3 border rounded-4 mb-3">
                                <div class="bg-primary bg-opacity-10 text-primary text-center p-2 rounded-4 me-3" style="width: 60px;">
                                    <div class="fw-bold fs-4"><?php echo date('d', strtotime($app['appointment_date'])); ?></div>
                                    <div class="small text-uppercase"><?php echo date('M', strtotime($app['appointment_date'])); ?></div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1"><?php echo $app['doctor_name']; ?></h6>
                                    <p class="text-muted small mb-0"><?php echo $app['specialization']; ?> • <?php echo date('h:i A', strtotime($app['appointment_time'])); ?></p>
                                </div>
                                <div>
                                    <span class="badge <?php echo getStatusBadge($app['status']); ?> rounded-pill px-3"><?php echo ucfirst($app['status']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card card-custom border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Quick Health Tips</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-4">
                                <i class="fas fa-tint text-info mb-2"></i>
                                <h6 class="fw-bold mb-1">Stay Hydrated</h6>
                                <p class="text-muted small mb-0">Drink at least 8 glasses of water daily for better kidney health.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-4">
                                <i class="fas fa-walking text-success mb-2"></i>
                                <h6 class="fw-bold mb-1">Morning Walk</h6>
                                <p class="text-muted small mb-0">A 30-minute walk every morning can boost your cardiovascular health.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-custom border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Recommended Doctors</h5>
                    <?php foreach ($recommended as $doc): ?>
                        <div class="d-flex align-items-center mb-4">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($doc['full_name']); ?>&background=random" class="rounded-circle me-3" width="45" height="45">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0 small"><?php echo $doc['full_name']; ?></h6>
                                <p class="text-muted small mb-0"><?php echo $doc['specialization']; ?></p>
                            </div>
                            <a href="doctors.php" class="btn btn-sm btn-light rounded-circle"><i class="fas fa-chevron-right small"></i></a>
                        </div>
                    <?php endforeach; ?>
                    <a href="doctors.php" class="btn btn-outline-primary w-100 rounded-pill btn-sm">View All Doctors</a>
                </div>
            </div>

            <div class="card card-custom border-0 bg-dark text-white p-4">
                <h5 class="fw-bold mb-3">Emergency?</h5>
                <p class="small opacity-75 mb-4">Call our emergency response team available 24/7 for immediate medical assistance.</p>
                <a href="tel:911" class="btn btn-danger w-100 rounded-pill fw-bold"><i class="fas fa-phone-alt me-2"></i> 911</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
