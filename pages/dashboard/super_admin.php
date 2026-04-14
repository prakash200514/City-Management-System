<?php
// pages/dashboard/super_admin.php
require '../../config/db.php';
require '../../includes/functions.php';

requireRole('Super Admin');

// Fetch Stats
$stats = [
    'users' => $pdo->query("SELECT count(*) FROM users")->fetchColumn(),
    'complaints' => $pdo->query("SELECT count(*) FROM complaints")->fetchColumn(),
    'pending' => $pdo->query("SELECT count(*) FROM complaints WHERE status='Pending'")->fetchColumn(),
    'resolved' => $pdo->query("SELECT count(*) FROM complaints WHERE status='Resolved'")->fetchColumn()
];

include '../../includes/header.php';
?>

<!-- Break out of standard container for dashboard layout -->
</div> 
<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h2>Super Admin Dashboard</h2>
        <div class="card-grid">
            <div class="card">
                <h3><i class="fas fa-users"></i> Total Users</h3>
                <p class="value"><?php echo $stats['users']; ?></p>
            </div>
            <div class="card">
                <h3><i class="fas fa-file-alt"></i> Total Complaints</h3>
                <p class="value"><?php echo $stats['complaints']; ?></p>
            </div>
            <div class="card">
                <h3><i class="fas fa-clock text-warning"></i> Pending</h3>
                <p class="value text-warning"><?php echo $stats['pending']; ?></p>
            </div>
            <div class="card">
                <h3><i class="fas fa-check-circle text-success"></i> Resolved</h3>
                <p class="value text-success"><?php echo $stats['resolved']; ?></p>
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Recent Activity</h3>
            <p>System logs and recent complaints will appear here.</p>
            <!-- Placeholder for recent table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT id, title, status, created_at FROM complaints ORDER BY created_at DESC LIMIT 5");
                    while($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                        <td><?php echo formatDate($row['created_at']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Re-open container for footer to close -->
<div class="container" style="display:none;">
<?php include '../../includes/footer.php'; ?>
