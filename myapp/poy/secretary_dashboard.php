<?php
$page_title = "Secretary Dashboard";
require_once 'header.php';

// Get statistics
$stats = [
    'new_requests' => $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn(),
    'today_visits' => $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status = 'approved'")->fetchColumn(),
    'total_doctors' => $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn(),
    'total_patients' => $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn()
];

// Get recent pending appointments
$stmt = $pdo->prepare("SELECT a.*, u_p.full_name as patient_name, u_d.full_name as doctor_name, d.specialization 
                      FROM appointments a 
                      JOIN patients p ON a.patient_id = p.id 
                      JOIN users u_p ON p.user_id = u_p.id 
                      JOIN doctors d ON a.doctor_id = d.id 
                      JOIN users u_d ON d.user_id = u_d.id 
                      WHERE a.status = 'pending' 
                      ORDER BY a.created_at DESC LIMIT 10");
$stmt->execute();
$pending_apps = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-custom border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fas fa-file-medical text-warning fa-xl"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['new_requests']; ?></h4>
                        <p class="text-muted small mb-0">New Requests</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-hospital-user text-primary fa-xl"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['today_visits']; ?></h4>
                        <p class="text-muted small mb-0">Today's Visits</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-user-md text-success fa-xl"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['total_doctors']; ?></h4>
                        <p class="text-muted small mb-0">Active Doctors</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom border-0 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-users text-info fa-xl"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $stats['total_patients']; ?></h4>
                        <p class="text-muted small mb-0">Total Patients</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card card-custom border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Pending Appointment Approvals</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="pendingTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Patient</th>
                                    <th class="border-0">Doctor</th>
                                    <th class="border-0">Date & Time</th>
                                    <th class="border-0">Reason</th>
                                    <th class="border-0 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_apps as $app): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo $app['patient_name']; ?></div>
                                            <div class="small text-muted">ID: #P-<?php echo $app['patient_id']; ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo $app['doctor_name']; ?></div>
                                            <div class="small text-muted"><?php echo $app['specialization']; ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo date('M d, Y', strtotime($app['appointment_date'])); ?></div>
                                            <div class="small text-muted"><?php echo date('h:i A', strtotime($app['appointment_time'])); ?></div>
                                        </td>
                                        <td class="small"><?php echo $app['reason'] ?: 'N/A'; ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-primary rounded-pill px-3 approve-btn" data-id="<?php echo $app['id']; ?>">Approve</button>
                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 reject-btn" data-id="<?php echo $app['id']; ?>">Reject</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#pendingTable').DataTable();

    // Reuse the same logic from doctor dashboard
    $(".approve-btn").click(function() {
        updateStatus($(this).data("id"), 'approved');
    });

    $(".reject-btn").click(function() {
        updateStatus($(this).data("id"), 'cancelled');
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
