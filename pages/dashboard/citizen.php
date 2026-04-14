<?php
// pages/dashboard/citizen.php
require '../../config/db.php';
require '../../includes/functions.php';

requireRole('Citizen');

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM complaints WHERE citizen_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$complaints = $stmt->fetchAll();

include '../../includes/header.php';
?>

</div> 
<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h2>My Dashboard</h2>
        <a href="../complaints/lodge.php" class="btn btn-primary" style="margin-bottom: 20px;">Lodge New Complaint</a>

        <div class="card">
            <h3>My Complaint History</h3>
            <?php if(count($complaints) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($complaints as $row): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td>
                                <?php 
                                    // Fetch dept name
                                    $dept_stmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
                                    $dept_stmt->execute([$row['dept_id']]);
                                    echo $dept_stmt->fetchColumn();
                                ?>
                            </td>
                            <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                            <td><?php echo formatDate($row['created_at']); ?></td>
                            <td><a href="../complaints/view.php?id=<?php echo $row['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 0.8rem;">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No complaints found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="container" style="display:none;">
<?php include '../../includes/footer.php'; ?>
