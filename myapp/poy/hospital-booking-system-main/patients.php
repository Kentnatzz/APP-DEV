<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn() || !hasAnyRole(['admin', 'secretary'])) {
    header('Location: login.php');
    exit;
}

// Get all patients with user details
$query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.status 
          FROM patients p 
          JOIN users u ON p.user_id = u.id 
          ORDER BY u.first_name ASC";
$result = mysqli_query($link, $query);
$patients = [];
while ($row = mysqli_fetch_assoc($result)) {
    $patients[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - MedCore Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .main-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .table thead th { border-top: none; background: #f8fafc; text-transform: uppercase; font-size: 12px; font-weight: 700; color: #64748b; }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Patients</h1>
                <p class="text-muted mb-0">Total patients registered in the system</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Dashboard</a>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Patient Name</th>
                                <th>Contact</th>
                                <th>Age / Gender</th>
                                <th>Blood Group</th>
                                <th>Appointments</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $p): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold"><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></div>
                                        <div class="text-muted small"><?php echo htmlspecialchars($p['email']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['phone']); ?></td>
                                    <td><?php echo $p['age']; ?> / <?php echo ucfirst($p['gender']); ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo $p['blood_group']; ?></span></td>
                                    <td><?php echo $p['total_appointments']; ?></td>
                                    <td class="text-end pe-4">
                                        <a href="history.php?phone=<?php echo urlencode($p['phone']); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-history"></i> History
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
