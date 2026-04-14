<?php
// pages/dashboard/field_worker.php
require '../../config/db.php';
require '../../includes/functions.php';

requireRole('Field Worker');

$worker_id = $_SESSION['user_id'];

// Fetch Tasks
$stmt = $pdo->prepare("
    SELECT t.id as task_id, c.id as complaint_id, t.status as task_status, t.assigned_at, c.title, c.priority, c.description, c.location
    FROM task_assignments t
    JOIN complaints c ON t.complaint_id = c.id
    WHERE t.worker_id = ?
    ORDER BY t.assigned_at DESC
");
$stmt->execute([$worker_id]);
$tasks = $stmt->fetchAll();

include '../../includes/header.php';
?>

</div> 
<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h2>My Assigned Tasks</h2>

        <?php if(count($tasks) > 0): ?>
            <div class="card-grid">
                <?php foreach($tasks as $row): ?>
                <div class="card" style="border-top: 5px solid <?php echo ($row['task_status'] == 'Completed') ? '#2ecc71' : '#3498db'; ?>">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><strong>Priority:</strong> <?php echo $row['priority']; ?></p>
                    <p><strong>Loc:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                    <p><strong>Status:</strong> <span class="status-badge status-<?php echo $row['task_status']; ?>"><?php echo $row['task_status']; ?></span></p>
                    <p style="font-size: 0.9rem; color: #7f8c8d;"><?php echo formatDate($row['assigned_at']); ?></p>
                    <a href="../complaints/view.php?id=<?php echo $row['complaint_id']; ?>&type=task" class="btn btn-primary" style="margin-top: 10px;">View Details</a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No tasks assigned yet.</p>
        <?php endif; ?>
    </div>
</div>
<div class="container" style="display:none;">
<?php include '../../includes/footer.php'; ?>
