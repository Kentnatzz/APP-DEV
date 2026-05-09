<?php
$page_title = "Manage Appointments";
require_once 'header.php';
checkRole(['secretary', 'admin']);

$stmt = $pdo->prepare("SELECT a.*, u_p.full_name as patient_name, u_d.full_name as doctor_name, d.specialization 
                      FROM appointments a 
                      JOIN patients p ON a.patient_id = p.id 
                      JOIN users u_p ON p.user_id = u_p.id 
                      JOIN doctors d ON a.doctor_id = d.id 
                      JOIN users u_d ON d.user_id = u_d.id 
                      ORDER BY a.created_at DESC");
$stmt->execute();
$all_appointments = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="card card-custom border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">System-wide Appointments</h5>
                <div>
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
                    <button class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-plus me-1"></i> Manual Booking</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="manageAppsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">ID</th>
                            <th class="border-0">Patient</th>
                            <th class="border-0">Doctor</th>
                            <th class="border-0">Date & Time</th>
                            <th class="border-0">Status</th>
                            <th class="border-0 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_appointments as $app): ?>
                            <tr>
                                <td class="small fw-bold text-muted">#<?php echo $app['id']; ?></td>
                                <td>
                                    <div class="fw-bold small"><?php echo $app['patient_name']; ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold small"><?php echo $app['doctor_name']; ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;"><?php echo $app['specialization']; ?></div>
                                </td>
                                <td>
                                    <div class="small fw-bold"><?php echo date('M d, Y', strtotime($app['appointment_date'])); ?></div>
                                    <div class="text-muted small"><?php echo date('h:i A', strtotime($app['appointment_time'])); ?></div>
                                </td>
                                <td>
                                    <span class="badge <?php echo getStatusBadge($app['status']); ?> rounded-pill px-3">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li><a class="dropdown-item small" href="#" onclick="updateStatus(<?php echo $app['id']; ?>, 'approved')"><i class="fas fa-check text-success me-2"></i> Approve</a></li>
                                            <li><a class="dropdown-item small" href="#" onclick="updateStatus(<?php echo $app['id']; ?>, 'completed')"><i class="fas fa-check-double text-primary me-2"></i> Mark Completed</a></li>
                                            <li><a class="dropdown-item small" href="#" onclick="updateStatus(<?php echo $app['id']; ?>, 'cancelled')"><i class="fas fa-times text-danger me-2"></i> Cancel/Reject</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#manageAppsTable').DataTable({
        "order": [[0, "desc"]]
    });
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
</script>
