<?php
// pages/dashboard/field_workers.php
require '../../config/db.php';
require '../../includes/functions.php';

requireRole('Department Admin');
$dept_id = $_SESSION['dept_id'];

// Fetch Workers
$stmt = $pdo->prepare("
    SELECT u.id, u.full_name, u.email, u.contact_number,
    (SELECT count(*) FROM task_assignments t WHERE t.worker_id = u.id AND t.status != 'Completed') as active_tasks
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE r.name = 'Field Worker' AND u.dept_id = ?
");
$stmt->execute([$dept_id]);
$workers = $stmt->fetchAll();

include '../../includes/header.php';
?>

</div> 
<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h2>Field Workers</h2>
        
        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Active Tasks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($workers) > 0): ?>
                        <?php foreach($workers as $w): ?>
                        <tr>
                            <td><?php echo $w['id']; ?></td>
                            <td><?php echo htmlspecialchars($w['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($w['email']); ?></td>
                            <td><?php echo htmlspecialchars($w['contact_number']); ?></td>
                            <td>
                                <?php if($w['active_tasks'] > 0): ?>
                                    <span class="status-badge status-In"><?php echo $w['active_tasks']; ?> Active</span>
                                <?php else: ?>
                                    <span class="status-badge status-Resolved" style="background: #d1fae5; color: #065f46;">Available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No field workers found in this department.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="container" style="display:none;">
<?php include '../../includes/footer.php'; ?>
