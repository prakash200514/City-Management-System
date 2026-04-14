<?php
// pages/dashboard/manage_complaints.php
require '../../config/db.php';
require '../../includes/functions.php';

requireRole('Department Admin');

$dept_id = $_SESSION['dept_id'];

// Filter Logic
$status = $_GET['status'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$query = "SELECT c.*, w.full_name as worker_name 
          FROM complaints c 
          LEFT JOIN task_assignments ta ON c.id = ta.complaint_id AND ta.status != 'Completed'
          LEFT JOIN users w ON ta.worker_id = w.id
          WHERE c.dept_id = ?";
$params = [$dept_id];

if ($status) {
    $query .= " AND c.status = ?";
    $params[] = $status;
}
if ($from_date) {
    $query .= " AND date(c.created_at) >= ?";
    $params[] = $from_date;
}
if ($to_date) {
    $query .= " AND date(c.created_at) <= ?";
    $params[] = $to_date;
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

include '../../includes/header.php';
?>

</div> 
<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h2>Manage Complaints</h2>
        
        <div class="card" style="margin-bottom: 2rem;">
            <form method="GET" action="" class="d-flex" style="gap: 20px; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Filter by Status:</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?php if($status=='Pending') echo 'selected'; ?>>Pending</option>
                        <option value="In Progress" <?php if($status=='In Progress') echo 'selected'; ?>>In Progress</option>
                        <option value="Resolved" <?php if($status=='Resolved') echo 'selected'; ?>>Resolved</option>
                        <option value="Closed" <?php if($status=='Closed') echo 'selected'; ?>>Closed</option>
                    </select>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label class="form-label">From Date:</label>
                    <input type="date" name="from_date" class="form-control" value="<?php echo $_GET['from_date'] ?? ''; ?>">
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label class="form-label">To Date:</label>
                    <input type="date" name="to_date" class="form-control" value="<?php echo $_GET['to_date'] ?? ''; ?>">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="height: 48px;">Filter</button>
                    <a href="manage_complaints.php" class="btn" style="height: 48px; line-height: 48px; background: transparent; color: var(--text-muted);">Reset</a>
                </div>
            </form>
        </div>

        <div class="card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Assigned To</th>
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
                            <td>
                                <?php if($row['worker_name']): ?>
                                    <span style="color: var(--primary); font-weight: 500;"><i class="fas fa-user-hard-hat"></i> <?php echo htmlspecialchars($row['worker_name']); ?></span>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-style: italic;">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                            <td><?php echo formatDate($row['created_at']); ?></td>
                            <td>
                                <a href="../complaints/view.php?id=<?php echo $row['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Manage</a>
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
