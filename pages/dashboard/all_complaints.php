<?php
// pages/dashboard/all_complaints.php
require '../../config/db.php';
require '../../includes/functions.php';

requireRole('Super Admin');

// Filter Logic
$status = $_GET['status'] ?? '';
$dept_id = $_GET['dept_id'] ?? '';

$query = "SELECT c.*, d.name as dept_name FROM complaints c JOIN departments d ON c.dept_id = d.id WHERE 1=1";
$params = [];

if ($status) {
    $query .= " AND c.status = ?";
    $params[] = $status;
}
if ($dept_id) {
    $query .= " AND c.dept_id = ?";
    $params[] = $dept_id;
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

// Fetch Departments using shared connection
$depts = $pdo->query("SELECT * FROM departments")->fetchAll();

include '../../includes/header.php';
?>

</div> 
<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h2>All Complaints (Super Admin)</h2>
        
        <div class="card" style="margin-bottom: 20px;">
            <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: end;">
                <div>
                    <label class="form-label" style="display: block; margin-bottom: 5px; font-weight: 500;">Filter by Status:</label>
                    <select name="status" class="form-control" onchange="this.form.submit()" style="width: 100%;">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?php if($status=='Pending') echo 'selected'; ?>>Pending</option>
                        <option value="In Progress" <?php if($status=='In Progress') echo 'selected'; ?>>In Progress</option>
                        <option value="Resolved" <?php if($status=='Resolved') echo 'selected'; ?>>Resolved</option>
                        <option value="Closed" <?php if($status=='Closed') echo 'selected'; ?>>Closed</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="display: block; margin-bottom: 5px; font-weight: 500;">Filter by Dept:</label>
                    <select name="dept_id" class="form-control" onchange="this.form.submit()" style="width: 100%;">
                        <option value="">All Departments</option>
                        <?php foreach($depts as $d): ?>
                            <option value="<?php echo $d['id']; ?>" <?php if($dept_id==$d['id']) echo 'selected'; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Add a clear button for better UX -->
                <?php if($status || $dept_id): ?>
                <div style="padding-bottom: 2px;">
                    <a href="all_complaints.php" class="btn" style="background: var(--text-muted); color: white; padding: 0.6rem 1rem;">Clear Filters</a>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
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
                    <?php if(count($complaints) > 0): ?>
                        <?php foreach($complaints as $row): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['dept_name']); ?></td>
                            <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                            <td><?php echo formatDate($row['created_at']); ?></td>
                            <td>
                                <a href="../complaints/view.php?id=<?php echo $row['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No complaints found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="container" style="display:none;">
<?php include '../../includes/footer.php'; ?>
