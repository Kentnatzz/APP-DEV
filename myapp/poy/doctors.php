<?php
$page_title = "Find Doctors";
require_once 'header.php';

// Fetch all doctors with their user info
$stmt = $pdo->prepare("SELECT d.*, u.full_name, u.profile_pic FROM doctors d JOIN users u ON d.user_id = u.id");
$stmt->execute();
$doctors = $stmt->fetchAll();

// Get unique specializations for filter
$specs = array_unique(array_column($doctors, 'specialization'));
?>

<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold">Our Specialists</h3>
            <p class="text-muted">Find and book appointments with top medical experts.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="input-group" style="max-width: 400px; margin-left: auto;">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="doctorSearch" class="form-control border-start-0" placeholder="Search by name or specialization...">
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex gap-2 overflow-auto pb-2">
                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 active filter-btn" data-filter="all">All</button>
                <?php foreach ($specs as $spec): ?>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 filter-btn" data-filter="<?php echo $spec; ?>"><?php echo $spec; ?></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row g-4" id="doctorsGrid">
        <?php foreach ($doctors as $doc): ?>
            <div class="col-md-6 col-xl-4 doctor-card-wrapper" data-specialization="<?php echo $doc['specialization']; ?>" data-name="<?php echo strtolower($doc['full_name']); ?>">
                <div class="card card-custom h-100 border-0">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($doc['full_name']); ?>&background=random" class="rounded-circle me-3" style="width: 70px; height: 70px; object-fit: cover;" alt="Doctor">
                            <div>
                                <h5 class="fw-bold mb-1"><?php echo $doc['full_name']; ?></h5>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3"><?php echo $doc['specialization']; ?></span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="small text-muted mb-1"><i class="fas fa-briefcase me-1"></i> Experience</div>
                                    <div class="fw-bold"><?php echo $doc['experience']; ?> Years</div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted mb-1"><i class="fas fa-money-bill-wave me-1"></i> Fee</div>
                                    <div class="fw-bold"><?php echo formatCurrency($doc['consultation_fee']); ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted mb-1"><i class="fas fa-door-open me-1"></i> Room</div>
                                    <div class="fw-bold"><?php echo $doc['room_number']; ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted mb-1"><i class="fas fa-info-circle me-1"></i> Status</div>
                                    <span class="badge <?php echo $doc['availability_status'] == 'available' ? 'bg-success' : 'bg-danger'; ?> rounded-pill px-2">
                                        <?php echo ucfirst($doc['availability_status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mb-4 line-clamp-2"><?php echo $doc['bio']; ?></p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary rounded-pill book-btn" data-id="<?php echo $doc['id']; ?>" data-name="<?php echo $doc['full_name']; ?>" <?php echo $doc['availability_status'] != 'available' ? 'disabled' : ''; ?>>
                                <i class="fas fa-calendar-plus me-2"></i> Book Appointment
                            </button>
                            <a href="doctor_profile.php?id=<?php echo $doc['id']; ?>" class="btn btn-light rounded-pill border">View Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="bookingModalLabel">Book Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bookingForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="doctor_id" id="modalDoctorId">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Selected Doctor</label>
                        <input type="text" class="form-control bg-light border-0" id="modalDoctorName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Select Date</label>
                        <input type="date" name="appointment_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Select Time Slot</label>
                        <select name="appointment_time" class="form-select" required>
                            <option value="">Choose a time...</option>
                            <option value="08:00">08:00 AM</option>
                            <option value="09:00">09:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="14:00">02:00 PM</option>
                            <option value="15:00">03:00 PM</option>
                            <option value="16:00">04:00 PM</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small text-muted">Reason for Visit (Optional)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Briefly describe your symptoms..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
$(document).ready(function() {
    // Search Functionality
    $("#doctorSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".doctor-card-wrapper").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Filter Functionality
    $(".filter-btn").click(function() {
        $(".filter-btn").removeClass("active");
        $(this).addClass("active");
        var filter = $(this).data("filter");
        
        if (filter === "all") {
            $(".doctor-card-wrapper").show();
        } else {
            $(".doctor-card-wrapper").hide();
            $(".doctor-card-wrapper[data-specialization='" + filter + "']").show();
        }
    });

    // Handle Booking Modal
    $(".book-btn").click(function() {
        var id = $(this).data("id");
        var name = $(this).data("name");
        $("#modalDoctorId").val(id);
        $("#modalDoctorName").val(name);
        $("#bookingModal").modal("show");
    });

    // Handle Booking Submission via AJAX
    $("#bookingForm").submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: 'api/book_appointment.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'appointments.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message
                    });
                }
            }
        });
    });
});
</script>
