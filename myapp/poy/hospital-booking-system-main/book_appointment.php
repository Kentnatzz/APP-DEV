<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCore - Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .container-custom { max-width: 900px; margin: 40px auto; }
        .booking-card { background: white; border-radius: 15px; padding: 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .form-section { margin: 30px 0; }
        .form-section h3 { color: #333; font-weight: 700; margin-bottom: 20px; }
        .form-control { border: 2px solid #e0e0e0; border-radius: 8px; padding: 12px; }
        .form-control:focus { border-color: #667eea; box-shadow: none; }
        .doctor-selector { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
        .doctor-option { padding: 15px; border: 2px solid #e0e0e0; border-radius: 10px; cursor: pointer; transition: all 0.3s; text-align: center; }
        .doctor-option:hover { border-color: #667eea; background: #f5f7fa; }
        .doctor-option.selected { border-color: #667eea; background: #eef2ff; }
        .time-slot { padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.3s; }
        .time-slot:hover { border-color: #667eea; }
        .time-slot.selected { background: #667eea; color: white; border-color: #667eea; }
        .time-slot.disabled { background: #f5f5f5; color: #ccc; cursor: not-allowed; }
        .btn-book-final { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; padding: 12px 30px; border-radius: 8px; font-weight: 600; width: 100%; }
        .summary-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 15px; }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once 'config.php';
    require_once 'functions.php';

    if (!isLoggedIn() || !hasRole('patient')) {
        header('Location: login.php');
        exit;
    }

    $doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;
    $error = '';
    $success = '';

    $doctors = getDoctors($link);
    $selectedDoctor = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $doctorId = intval($_POST['doctor_id']);
        $date = $_POST['date'];
        $time = $_POST['time'];
        $reason = mysqli_real_escape_string($link, $_POST['reason']);

        $selectedDoctor = getDoctorById($doctorId, $link);
        $patient = getPatientByUserId(getCurrentUserId(), $link);

        if (!$selectedDoctor) {
            $error = 'Please select a valid doctor';
        } elseif (empty($date) || empty($time)) {
            $error = 'Please select date and time';
        } else {
            $result = bookAppointment($doctorId, $patient['id'], $date, $time, $reason, $link);
            if ($result['success']) {
                $success = 'Appointment booked successfully! Waiting for approval.';
                $_GET['doctor_id'] = null;
            } else {
                $error = $result['message'];
            }
        }
    }

    if ($doctorId) {
        $selectedDoctor = getDoctorById($doctorId, $link);
    }
    ?>

    <div class="container-custom">
        <div class="booking-card">
            <h1 style="margin-bottom: 30px; text-align: center; font-weight: 700;">
                <i class="fas fa-calendar-plus me-2"></i>Book Appointment
            </h1>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-section">
                    <h3><i class="fas fa-user-md me-2"></i>Select Doctor</h3>
                    <div class="doctor-selector">
                        <?php foreach ($doctors as $doctor): ?>
                            <label class="doctor-option<?php echo $selectedDoctor && $selectedDoctor['id'] == $doctor['id'] ? ' selected' : ''; ?>">
                                <input type="radio" name="doctor_id" value="<?php echo $doctor['id']; ?>" <?php echo $selectedDoctor && $selectedDoctor['id'] == $doctor['id'] ? 'checked' : ''; ?> style="display: none;">
                                <div>
                                    <strong>Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?></strong>
                                    <p style="font-size: 13px; margin: 5px 0; color: #666;">
                                        <?php echo htmlspecialchars($doctor['specialization']); ?>
                                    </p>
                                    <p style="font-size: 12px; color: #999;">Rating: <?php echo $doctor['rating']; ?>/5</p>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($selectedDoctor): ?>
                    <div class="form-section">
                        <h3><i class="fas fa-calendar me-2"></i>Select Date</h3>
                        <input type="date" name="date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-clock me-2"></i>Select Time</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px;">
                            <?php
                            $times = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'];
                            foreach ($times as $t):
                            ?>
                                <label class="time-slot" style="cursor: pointer;">
                                    <input type="radio" name="time" value="<?php echo $t; ?>" style="display: none;">
                                    <?php echo $t; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-notes-medical me-2"></i>Reason for Visit</h3>
                        <textarea name="reason" class="form-control" rows="4" placeholder="Describe your symptoms or reason for visit..."></textarea>
                    </div>

                    <div class="summary-box">
                        <h5 style="margin-bottom: 15px;">Appointment Summary</h5>
                        <p><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($selectedDoctor['first_name'] . ' ' . $selectedDoctor['last_name']); ?></p>
                        <p><strong>Specialization:</strong> <?php echo htmlspecialchars($selectedDoctor['specialization']); ?></p>
                        <p><strong>Consultation Fee:</strong> $<?php echo $selectedDoctor['consultation_fee']; ?></p>
                        <p style="margin-bottom: 0;"><strong>Status:</strong> Pending Approval</p>
                    </div>

                    <button type="submit" class="btn-book-final mt-4">
                        <i class="fas fa-check me-2"></i>Confirm Appointment
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
