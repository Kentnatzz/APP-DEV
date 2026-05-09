<?php
$page_title = "Doctor Dashboard";
require_once 'header.php';

// Get Doctor ID
$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch();
$doctor_id = $doctor['id'];

// Get statistics
$stmt = $pdo->prepare("SELECT 
    (SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = 'pending') as pending_count,
    (SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = CURDATE() AND status = 'approved') as today_count,
    (SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = 'completed') as total_patients");
$stmt->execute([$doctor_id, $doctor_id, $doctor_id]);
$stats = $stmt->fetch();

// Get today's appointments
$stmt = $pdo->prepare("SELECT a.*, u.full_name as patient_name 
                      FROM appointments a 
                      JOIN patients p ON a.patient_id = p.id 
                      JOIN users u ON p.user_id = u.id 
                      WHERE a.doctor_id = ? AND a.appointment_date = CURDATE() AND a.status = 'approved'
                      ORDER BY a.appointment_time ASC");
$stmt->execute([$doctor_id]);
$today_appointments = $stmt->fetchAll();

// Data for Chart.js (Appointments in last 7 days)
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
    $stmt->execute([$doctor_id, $date]);
    $chart_data[date('D', strtotime($date))] = $stmt->fetchColumn();
}
?>

<div class="container-fluid">
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-custom border-0 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-user-clock text-primary fa-2x"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['pending_count']; ?></h4>
                        <p class="text-muted small mb-0">Pending Approvals</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom border-0 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-calendar-day text-success fa-2x"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['today_count']; ?></h4>
                        <p class="text-muted small mb-0">Today's Appointments</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom border-0 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-users text-info fa-2x"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['total_patients']; ?></h4>
                        <p class="text-muted small mb-0">Total Consultations</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card card-custom border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Weekly Statistics</h5>
                    <canvas id="appointmentsChart" height="250"></canvas>
                </div>
            </div>

            <div class="card card-custom border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Today's Schedule</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Patient</th>
                                    <th class="border-0">Time</th>
                                    <th class="border-0">Reason</th>
                                    <th class="border-0 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($today_appointments)): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No appointments for today.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($today_appointments as $app): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($app['patient_name']); ?>&background=random" class="rounded-circle me-2" width="35" height="35">
                                                    <div class="fw-bold small"><?php echo $app['patient_name']; ?></div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-light text-dark"><?php echo date('h:i A', strtotime($app['appointment_time'])); ?></span></td>
                                            <td class="small"><?php echo $app['reason'] ?: 'N/A'; ?></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-success rounded-pill px-3 complete-btn" data-id="<?php echo $app['id']; ?>">Complete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-custom border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Pending Approvals</h5>
                    <?php
                    $stmt = $pdo->prepare("SELECT a.*, u.full_name as patient_name FROM appointments a JOIN patients p ON a.patient_id = p.id JOIN users u ON p.user_id = u.id WHERE a.doctor_id = ? AND a.status = 'pending' LIMIT 5");
                    $stmt->execute([$doctor_id]);
                    $pending = $stmt->fetchAll();
                    if (empty($pending)): ?>
                        <p class="text-center text-muted py-3">No pending requests.</p>
                    <?php else: ?>
                        <?php foreach ($pending as $p): ?>
                            <div class="p-3 border rounded-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0"><?php echo $p['patient_name']; ?></h6>
                                    <small class="text-muted"><?php echo date('M d', strtotime($p['appointment_date'])); ?></small>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-primary flex-grow-1 rounded-pill approve-btn" data-id="<?php echo $p['id']; ?>">Approve</button>
                                    <button class="btn btn-sm btn-light flex-grow-1 rounded-pill border reject-btn" data-id="<?php echo $p['id']; ?>">Reject</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
$(document).ready(function() {
    // Chart.js implementation
    const ctx = document.getElementById('appointmentsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_keys($chart_data)); ?>,
            datasets: [{
                label: 'Appointments',
                data: <?php echo json_encode(array_values($chart_data)); ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Approve Appointment
    $(".approve-btn").click(function() {
        var id = $(this).data("id");
        updateStatus(id, 'approved');
    });

    // Reject Appointment
    $(".reject-btn").click(function() {
        var id = $(this).data("id");
        updateStatus(id, 'cancelled');
    });

    // Complete Appointment
    $(".complete-btn").click(function() {
        var id = $(this).data("id");
        updateStatus(id, 'completed');
    });

    function updateStatus(id, status) {
        $.ajax({
            url: 'api/update_appointment_status.php',
            type: 'POST',
            data: { appointment_id: id, status: status },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Updated!', response.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            }
        });
    }
});
</script>
