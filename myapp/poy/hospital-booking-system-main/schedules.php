<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn() || !hasRole('doctor')) {
    header('Location: login.php');
    exit;
}

$userId = getCurrentUserId();
$doctor = getDoctorByUserId($userId, $link);
$error = '';
$success = '';

// Handle schedule update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_schedule'])) {
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    foreach ($days as $day) {
        $isAvailable = isset($_POST["available_$day"]) ? 1 : 0;
        $mStart = $_POST["m_start_$day"] ?: '08:00:00';
        $mEnd = $_POST["m_end_$day"] ?: '12:00:00';
        $aStart = $_POST["a_start_$day"] ?: '13:00:00';
        $aEnd = $_POST["a_end_$day"] ?: '17:00:00';
        
        $query = "INSERT INTO schedules (doctor_id, day_of_week, morning_start, morning_end, afternoon_start, afternoon_end, is_available)
                  VALUES (" . $doctor['id'] . ", '$day', '$mStart', '$mEnd', '$aStart', '$aEnd', $isAvailable)
                  ON DUPLICATE KEY UPDATE 
                  morning_start = '$mStart', morning_end = '$mEnd', 
                  afternoon_start = '$aStart', afternoon_end = '$aEnd', 
                  is_available = $isAvailable";
        mysqli_query($link, $query);
    }
    $success = 'Schedule updated successfully';
}

$schedule = getDoctorSchedule($doctor['id'], $link);
$scheduleMap = [];
foreach ($schedule as $s) {
    $scheduleMap[$s['day_of_week']] = $s;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedule - MedCore Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .main-container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .day-row { border-bottom: 1px solid #eef2f7; padding: 20px; }
        .day-row:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">My Working Schedule</h1>
                <p class="text-muted mb-0">Set your availability for appointments</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Dashboard</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <input type="hidden" name="update_schedule" value="1">
                <div class="card-body p-0">
                    <?php 
                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    foreach ($days as $day): 
                        $s = $scheduleMap[$day] ?? null;
                    ?>
                        <div class="day-row">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="available_<?php echo $day; ?>" <?php echo (!$s || $s['is_available']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold"><?php echo $day; ?></label>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Morning</span>
                                        <input type="time" name="m_start_<?php echo $day; ?>" class="form-control" value="<?php echo $s['morning_start'] ?? '08:00'; ?>">
                                        <span class="input-group-text">to</span>
                                        <input type="time" name="m_end_<?php echo $day; ?>" class="form-control" value="<?php echo $s['morning_end'] ?? '12:00'; ?>">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Afternoon</span>
                                        <input type="time" name="a_start_<?php echo $day; ?>" class="form-control" value="<?php echo $s['afternoon_start'] ?? '13:00'; ?>">
                                        <span class="input-group-text">to</span>
                                        <input type="time" name="a_end_<?php echo $day; ?>" class="form-control" value="<?php echo $s['afternoon_end'] ?? '17:00'; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer bg-white p-4">
                    <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <i class="fas fa-save me-2"></i>Save Schedule Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
