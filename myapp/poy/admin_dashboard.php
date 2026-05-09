<?php
$page_title = "Admin Dashboard";
require_once 'header.php';

// Get statistics
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_appointments' => $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn(),
    'completed_appointments' => $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'completed'")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT SUM(d.consultation_fee) FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.status = 'completed'")->fetchColumn() ?: 0
];

// Data for Specialty Chart
$stmt = $pdo->query("SELECT d.specialization, COUNT(*) as count FROM appointments a JOIN doctors d ON a.doctor_id = d.id GROUP BY d.specialization");
$specialty_data = $stmt->fetchAll();

// Recent Activity Logs
$stmt = $pdo->query("SELECT l.*, u.full_name, u.role FROM activity_logs l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 10");
$logs = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-custom border-0 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-users text-primary fa-xl"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['total_users']; ?></h4>
                        <p class="text-muted small mb-0">Total Users</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom border-0 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-calendar-check text-success fa-xl"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['total_appointments']; ?></h4>
                        <p class="text-muted small mb-0">Total Bookings</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom border-0 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-check-double text-info fa-xl"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['completed_appointments']; ?></h4>
                        <p class="text-muted small mb-0">Completed</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom border-0 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                        <i class="fas fa-dollar-sign text-danger fa-xl"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo formatCurrency($stats['total_revenue']); ?></h4>
                        <p class="text-muted small mb-0">Total Revenue</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card card-custom border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">System Activity Log</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">User</th>
                                    <th class="border-0">Action</th>
                                    <th class="border-0">Time</th>
                                    <th class="border-0">IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold small"><?php echo $log['full_name']; ?></div>
                                            <div class="text-muted small" style="font-size: 0.7rem;"><?php echo ucfirst($log['role']); ?></div>
                                        </td>
                                        <td><span class="small"><?php echo $log['action']; ?></span></td>
                                        <td class="small"><?php echo date('M d, h:i A', strtotime($log['created_at'])); ?></td>
                                        <td class="small text-muted"><?php echo $log['ip_address']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-custom border-0 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Bookings by Specialty</h5>
                    <canvas id="specialtyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
$(document).ready(function() {
    const ctx = document.getElementById('specialtyChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($specialty_data, 'specialization')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($specialty_data, 'count')); ?>,
                backgroundColor: ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6610f2'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '70%'
        }
    });
});
</script>
