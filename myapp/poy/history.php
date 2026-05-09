<?php
$page_title = "Activity History";
require_once 'header.php';

$stmt = $pdo->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$logs = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-custom border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Your Recent Activity</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="historyTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Action</th>
                                    <th class="border-0">Details</th>
                                    <th class="border-0">Time</th>
                                    <th class="border-0">IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><span class="badge bg-primary-subtle text-primary rounded-pill px-3"><?php echo $log['action']; ?></span></td>
                                        <td class="small text-muted"><?php echo $log['details'] ?: 'N/A'; ?></td>
                                        <td class="small fw-bold"><?php echo date('M d, Y - h:i A', strtotime($log['created_at'])); ?></td>
                                        <td class="small text-muted"><?php echo $log['ip_address']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#historyTable').DataTable({
        "order": [[2, "desc"]]
    });
});
</script>
