<?php
$page_title = "My Schedule";
require_once 'header.php';
checkRole('doctor');

// Get Doctor ID
$stmt = $pdo->prepare("SELECT id, availability_status FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch();
$doctor_id = $doctor['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        $status = sanitize($_POST['availability_status']);
        $stmt = $pdo->prepare("UPDATE doctors SET availability_status = ? WHERE id = ?");
        $stmt->execute([$status, $doctor_id]);
        $doctor['availability_status'] = $status;
        logActivity($_SESSION['user_id'], 'Update Availability', "Status changed to $status");
    } elseif (isset($_POST['add_schedule'])) {
        $day = $_POST['day_of_week'];
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];
        $stmt = $pdo->prepare("INSERT INTO schedules (doctor_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$doctor_id, $day, $start, $end]);
    }
}

// Fetch Schedule
$stmt = $pdo->prepare("SELECT * FROM schedules WHERE doctor_id = ? ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
$stmt->execute([$doctor_id]);
$schedules = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card card-custom border-0 p-4 mb-4">
                <h5 class="fw-bold mb-4">Availability Status</h5>
                <form action="doctor_schedule.php" method="POST">
                    <input type="hidden" name="update_status" value="1">
                    <div class="mb-3">
                        <select name="availability_status" class="form-select rounded-pill">
                            <option value="available" <?php echo $doctor['availability_status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="busy" <?php echo $doctor['availability_status'] == 'busy' ? 'selected' : ''; ?>>Busy</option>
                            <option value="on_leave" <?php echo $doctor['availability_status'] == 'on_leave' ? 'selected' : ''; ?>>On Leave</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Update Status</button>
                </form>
            </div>

            <div class="card card-custom border-0 p-4">
                <h5 class="fw-bold mb-4">Add Weekly Shift</h5>
                <form action="doctor_schedule.php" method="POST">
                    <input type="hidden" name="add_schedule" value="1">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Day</label>
                        <select name="day_of_week" class="form-select rounded-pill" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small text-muted">Start Time</label>
                            <input type="time" name="start_time" class="form-control rounded-pill" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">End Time</label>
                            <input type="time" name="end_time" class="form-control rounded-pill" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100 rounded-pill">Add Shift</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-custom border-0 p-4">
                <h5 class="fw-bold mb-4">My Weekly Schedule</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">Day</th>
                                <th class="border-0">Working Hours</th>
                                <th class="border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($schedules)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">No shifts scheduled yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($schedules as $sch): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $sch['day_of_week']; ?></td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary px-3 rounded-pill">
                                                <?php echo date('h:i A', strtotime($sch['start_time'])); ?> - <?php echo date('h:i A', strtotime($sch['end_time'])); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="api/delete_schedule.php?id=<?php echo $sch['id']; ?>" class="btn btn-sm btn-light rounded-circle text-danger delete-btn"><i class="fas fa-trash"></i></a>
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

<?php require_once 'footer.php'; ?>

<script>
$(document).ready(function() {
    $(".delete-btn").click(function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        Swal.fire({
            title: 'Delete shift?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});
</script>
