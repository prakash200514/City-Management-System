<?php
// pages/dashboard/audit_logs.php
require '../../config/db.php';
require '../../includes/functions.php';

requireRole('Super Admin');

// Fetch Logs
$stmt = $pdo->query("
    SELECT s.*, u.full_name as changer_name, c.title as complaint_title 
    FROM status_logs s
    JOIN users u ON s.changed_by = u.id
    JOIN complaints c ON s.complaint_id = c.id
    ORDER BY s.timestamp DESC
");
$logs = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h2>System Audit Logs</h2>
        
        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Complaint</th>
                        <th>Action</th>
                        <th>Changed By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($logs) > 0): ?>
                        <?php foreach($logs as $log): ?>
                        <tr>
                            <td><?php echo formatDate($log['timestamp']); ?></td>
                            <td>
                                #<?php echo $log['complaint_id']; ?> 
                                <span style="font-size:0.85em; color:var(--text-muted); display:block;"><?php echo htmlspecialchars(substr($log['complaint_title'], 0, 30)); ?>...</span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $log['old_status']; ?>"><?php echo $log['old_status']; ?></span>
                                &rarr;
                                <span class="status-badge status-<?php echo $log['new_status']; ?>"><?php echo $log['new_status']; ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($log['changer_name']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No logs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="container" style="display:none;">
<?php include '../../includes/footer.php'; ?>
