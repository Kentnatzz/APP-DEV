<?php
$page_title = "My Appointments";
require_once 'header.php';

$stmt = $pdo->prepare("SELECT a.*, d.specialization, u.full_name as doctor_name 
                      FROM appointments a 
                      JOIN doctors d ON a.doctor_id = d.id 
                      JOIN users u ON d.user_id = u.id 
                      JOIN patients p ON a.patient_id = p.id 
                      WHERE p.user_id = ? 
                      ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card card-custom border-0 bg-primary text-white p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-1">Appointment History</h3>
                        <p class="mb-0 opacity-75">Track your upcoming and past medical visits.</p>
                    </div>
                    <a href="doctors.php" class="btn btn-light rounded-pill px-4 fw-bold">Book New</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom border-0">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="appointmentsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Doctor</th>
                                    <th class="border-0">Date & Time</th>
                                    <th class="border-0">Reason</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($appointments)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">No appointments found. <a href="doctors.php">Book one now!</a></td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($appointments as $app): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($app['doctor_name']); ?>&background=random" class="rounded-circle me-2" width="40" height="40">
                                                    <div>
                                                        <div class="fw-bold"><?php echo $app['doctor_name']; ?></div>
                                                        <div class="small text-muted"><?php echo $app['specialization']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?php echo date('M d, Y', strtotime($app['appointment_date'])); ?></div>
                                                <div class="small text-muted"><?php echo date('h:i A', strtotime($app['appointment_time'])); ?></div>
                                            </td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?php echo $app['reason']; ?>">
                                                    <?php echo $app['reason'] ?: 'N/A'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo getStatusBadge($app['status']); ?> rounded-pill px-3">
                                                    <?php echo ucfirst($app['status']); ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <?php if ($app['status'] == 'pending' || $app['status'] == 'approved'): ?>
                                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3 cancel-btn" data-id="<?php echo $app['id']; ?>">Cancel</button>
                                                <?php elseif ($app['status'] == 'completed'): ?>
                                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 review-btn" data-id="<?php echo $app['id']; ?>" data-doctor="<?php echo $app['doctor_name']; ?>">Review</button>
                                                <?php endif; ?>
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
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Leave a Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reviewForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="appointment_id" id="reviewAppId">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Doctor</label>
                        <input type="text" class="form-control bg-light border-0" id="reviewDoctorName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Rating</label>
                        <div class="star-rating d-flex gap-2 fs-3 text-warning">
                            <i class="far fa-star star" data-rating="1"></i>
                            <i class="far fa-star star" data-rating="2"></i>
                            <i class="far fa-star star" data-rating="3"></i>
                            <i class="far fa-star star" data-rating="4"></i>
                            <i class="far fa-star star" data-rating="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" value="5" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small text-muted">Your Comment</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="How was your experience?" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#appointmentsTable').DataTable({
        "order": [[1, "desc"]],
        "pageLength": 10
    });

    // Review Modal Logic
    $(".review-btn").click(function() {
        var id = $(this).data("id");
        var doctor = $(this).data("doctor");
        $("#reviewAppId").val(id);
        $("#reviewDoctorName").val(doctor);
        $("#reviewModal").modal("show");
    });

    // Star Rating Logic
    $(".star").click(function() {
        var rating = $(this).data("rating");
        $("#ratingValue").val(rating);
        $(".star").each(function() {
            if ($(this).data("rating") <= rating) {
                $(this).removeClass("far").addClass("fas");
            } else {
                $(this).removeClass("fas").addClass("far");
            }
        });
    });

    // Submit Review
    $("#reviewForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: 'api/submit_review.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            }
        });
    });

    // Cancel Appointment
    $(".cancel-btn").click(function() {
        var id = $(this).data("id");
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to cancel this appointment?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it!',
            input: 'text',
            inputPlaceholder: 'Reason for cancellation...'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/cancel_appointment.php',
                    type: 'POST',
                    data: { appointment_id: id, reason: result.value },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Cancelled!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
});
</script>
