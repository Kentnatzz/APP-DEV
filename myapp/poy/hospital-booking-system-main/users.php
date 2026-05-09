<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn() || !hasAnyRole(['admin', 'secretary'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Handle delete user
if (isset($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    if ($userId !== getCurrentUserId()) {
        mysqli_query($link, "DELETE FROM users WHERE id = $userId");
        $success = 'User deleted successfully';
    } else {
        $error = 'You cannot delete yourself';
    }
}

// Get all users
$query = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($link, $query);
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - MedCore Hospital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .main-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .table thead th { border-top: none; background: #f8fafc; text-transform: uppercase; font-size: 12px; font-weight: 700; color: #64748b; }
        .badge-role { font-size: 11px; font-weight: 700; padding: 5px 10px; border-radius: 6px; }
        .role-admin { background: #fee2e2; color: #991b1b; }
        .role-doctor { background: #e0e7ff; color: #4338ca; }
        .role-secretary { background: #fef3c7; color: #92400e; }
        .role-patient { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">User Management</h1>
                <p class="text-muted mb-0">View and manage system users</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Dashboard</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                <i class="fas fa-user text-secondary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($u['phone']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <span class="badge-role role-<?php echo $u['role']; ?>">
                                            <?php echo strtoupper($u['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $u['status'] === 'active' ? 'success' : 'danger'; ?> rounded-pill" style="font-size: 10px;">
                                            <?php echo ucfirst($u['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                    <td class="text-end pe-4">
                                        <?php if ($u['id'] !== getCurrentUserId()): ?>
                                            <a href="?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
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
</body>
</html>
