<?php
$page_title = "Notifications";
require_once 'header.php';

// Mark all as read
$stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);

// Fetch all notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Your Notifications</h5>
                        <button class="btn btn-sm btn-light rounded-pill px-3" id="clearNotif">Clear All</button>
                    </div>

                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-light mb-3"></i>
                            <p class="text-muted">No notifications yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="notification-list">
                            <?php foreach ($notifications as $n): ?>
                                <div class="p-3 border rounded-4 mb-3 <?php echo $n['is_read'] ? 'opacity-75' : 'bg-primary bg-opacity-10 border-primary'; ?>">
                                    <div class="d-flex">
                                        <div class="rounded-circle bg-white shadow-sm p-2 me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-info-circle text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 small"><?php echo $n['message']; ?></p>
                                            <div class="text-muted small" style="font-size: 0.7rem;">
                                                <i class="fas fa-clock me-1"></i> <?php echo date('M d, Y h:i A', strtotime($n['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
