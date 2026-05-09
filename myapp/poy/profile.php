<?php
$page_title = "My Profile";
require_once 'header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    
    // Update basic info
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$full_name, $email, $_SESSION['user_id']])) {
        $_SESSION['full_name'] = $full_name;
        $success = "Profile updated successfully!";
        logActivity($_SESSION['user_id'], 'Update Profile', 'User updated profile information');
    } else {
        $error = "Failed to update profile.";
    }

    // Role-specific updates
    if ($_SESSION['role'] == 'patient') {
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        
        $stmt = $pdo->prepare("UPDATE patients SET dob = ?, gender = ?, phone = ?, address = ? WHERE user_id = ?");
        $stmt->execute([$dob, $gender, $phone, $address, $_SESSION['user_id']]);
    }
}

// Fetch fresh data
$user = getUserById($_SESSION['user_id']);
$patient_data = null;
if ($_SESSION['role'] == 'patient') {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $patient_data = $stmt->fetch();
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom border-0 p-4">
                <div class="text-center mb-4">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&size=100&background=random" class="rounded-circle mb-3 shadow-sm" width="100">
                    <h4 class="fw-bold mb-0"><?php echo $user['full_name']; ?></h4>
                    <p class="text-muted small"><?php echo ucfirst($user['role']); ?></p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form action="profile.php" method="POST">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">Basic Information</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                        </div>
                    </div>

                    <?php if ($_SESSION['role'] == 'patient' && $patient_data): ?>
                        <h6 class="fw-bold border-bottom pb-2 mb-3">Medical Profile</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="<?php echo $patient_data['dob']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="male" <?php echo $patient_data['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo $patient_data['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="other" <?php echo $patient_data['gender'] == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo $patient_data['phone']; ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small text-muted">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?php echo $patient_data['address']; ?></textarea>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-5">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
