<?php
// pages/dashboard/dept_admin.php
require '../../config/db.php';
require '../../includes/functions.php';

requireRole('Department Admin');

$dept_id = $_SESSION['dept_id'];
if(!$dept_id) {
    die("No department assigned to this admin.");
}

// Fetch stats for this department
$stats = [
    'total' => $pdo->prepare("SELECT count(*) FROM complaints WHERE dept_id = ?"),
    'pending' => $pdo->prepare("SELECT count(*) FROM complaints WHERE dept_id = ? AND status='Pending'"),
    'resolved' => $pdo->prepare("SELECT count(*) FROM complaints WHERE dept_id = ? AND status='Resolved'")
];

$stats['total']->execute([$dept_id]);
$stats['pending']->execute([$dept_id]);
$stats['resolved']->execute([$dept_id]);

$total_complaints = $stats['total']->fetchColumn();
$pending_complaints = $stats['pending']->fetchColumn();
$resolved_complaints = $stats['resolved']->fetchColumn();

// Fetch recent complaints
$stmt = $pdo->prepare("SELECT * FROM complaints WHERE dept_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$dept_id]);
$complaints = $stmt->fetchAll();

include '../../includes/header.php';
?>

</div> 
<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h2>Department Admin Dashboard</h2>
        <div class="card-grid">
            <div class="card" style="border-left: 5px solid #3498db;">
                <h3>Total Complaints</h3>
                <p style="font-size: 2rem; font-weight: bold;"><?php echo $total_complaints; ?></p>
            </div>
            <div class="card" style="border-left: 5px solid #e74c3c;">
                <h3>Pending</h3>
                <p style="font-size: 2rem; font-weight: bold;"><?php echo $pending_complaints; ?></p>
            </div>
            <div class="card" style="border-left: 5px solid #2ecc71;">
                <h3>Resolved</h3>
                <p style="font-size: 2rem; font-weight: bold;"><?php echo $resolved_complaints; ?></p>
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Recent Complaints</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($complaints) > 0): ?>
                        <?php foreach($complaints as $row): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                            <td><?php echo formatDate($row['created_at']); ?></td>
                            <td>
                                <a href="../complaints/view.php?id=<?php echo $row['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem;">View</a>
                                <!-- Add Assign Button Logic later in View -->
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No complaints found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="container" style="display:none;">
<?php include '../../includes/footer.php'; ?>
