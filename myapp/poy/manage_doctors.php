<?php
$page_title = "Manage Doctors";
require_once 'header.php';
checkRole(['admin', 'secretary']);

$stmt = $pdo->query("SELECT d.*, u.full_name, u.email, u.profile_pic FROM doctors d JOIN users u ON d.user_id = u.id");
$doctors = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="card card-custom border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Medical Staff Directory</h5>
                <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addDoctorModal"><i class="fas fa-plus me-1"></i> Add New Doctor</button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="manageDoctorsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Doctor</th>
                            <th class="border-0">Specialization</th>
                            <th class="border-0">Room</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Fee</th>
                            <th class="border-0 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doctors as $doc): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($doc['full_name']); ?>&background=random" class="rounded-circle me-2" width="35" height="35">
                                        <div>
                                            <div class="fw-bold small"><?php echo $doc['full_name']; ?></div>
                                            <div class="text-muted" style="font-size: 0.7rem;"><?php echo $doc['email']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="small"><?php echo $doc['specialization']; ?></span></td>
                                <td><span class="small fw-bold"><?php echo $doc['room_number']; ?></span></td>
                                <td>
                                    <span class="badge <?php echo $doc['availability_status'] == 'available' ? 'bg-success' : 'bg-danger'; ?> rounded-pill px-2" style="font-size: 0.7rem;">
                                        <?php echo ucfirst($doc['availability_status']); ?>
                                    </span>
                                </td>
                                <td><span class="small fw-bold"><?php echo formatCurrency($doc['consultation_fee']); ?></span></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-light rounded-circle me-1" title="Edit"><i class="fas fa-edit text-primary"></i></button>
                                    <button class="btn btn-sm btn-light rounded-circle" title="View Schedule"><i class="fas fa-calendar-alt text-info"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Doctor Modal (Placeholder) -->
<div class="modal fade" id="addDoctorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Add New Medical Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small">Please use the registration system or admin panel to create new user accounts first.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#manageDoctorsTable').DataTable();
});
</script>
