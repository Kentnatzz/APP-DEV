<?php
$page_title = "Manage Appointments";
require_once 'header.php';
checkRole('doctor');

// Get Doctor ID
$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch();
$doctor_id = $doctor['id'];

// Fetch all appointments for this doctor
$stmt = $pdo->prepare("SELECT a.*, u.full_name as patient_name 
                      FROM appointments a 
                      JOIN patients p ON a.patient_id = p.id 
                      JOIN users u ON p.user_id = u.id 
                      WHERE a.doctor_id = ? 
                      ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$stmt->execute([$doctor_id]);
$appointments = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="card card-custom border-0">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-4">Patient Appointments</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="doctorAppsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Patient</th>
                            <th class="border-0">Date & Time</th>
                            <th class="border-0">Reason</th>
                            <th class="border-0">Status</th>
                            <th class="border-0 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $app): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($app['patient_name']); ?>&background=random" class="rounded-circle me-2" width="35" height="35">
                                        <div class="fw-bold small"><?php echo $app['patient_name']; ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-bold"><?php echo date('M d, Y', strtotime($app['appointment_date'])); ?></div>
                                    <div class="text-muted small"><?php echo date('h:i A', strtotime($app['appointment_time'])); ?></div>
                                </td>
                                <td class="small"><?php echo $app['reason'] ?: 'N/A'; ?></td>
                                <td>
                                    <span class="badge <?php echo getStatusBadge($app['status']); ?> rounded-pill px-3">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?php if ($app['status'] == 'pending'): ?>
                                        <button class="btn btn-sm btn-primary rounded-pill px-3 approve-btn" data-id="<?php echo $app['id']; ?>">Approve</button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3 reject-btn" data-id="<?php echo $app['id']; ?>">Reject</button>
                                    <?php elseif ($app['status'] == 'approved'): ?>
                                        <button class="btn btn-sm btn-success rounded-pill px-3 complete-btn" data-id="<?php echo $app['id']; ?>">Complete</button>
                                    <?php endif; ?>
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
    $('#doctorAppsTable').DataTable({
        "order": [[1, "desc"]]
    });

    $(".approve-btn").click(function() { updateStatus($(this).data("id"), 'approved'); });
    $(".reject-btn").click(function() { updateStatus($(this).data("id"), 'cancelled'); });
    $(".complete-btn").click(function() { updateStatus($(this).data("id"), 'completed'); });

    function updateStatus(id, status) {
        $.ajax({
            url: 'api/update_appointment_status.php',
            type: 'POST',
            data: { appointment_id: id, status: status },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success').then(() => { location.reload(); });
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            }
        });
    }
});
</script>
