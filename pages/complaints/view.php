<?php
// pages/complaints/view.php
require '../../config/db.php';
require '../../includes/functions.php';
requireLogin();

$complaint_id = $_GET['id'] ?? null;
if (!$complaint_id) {
    die("Complaint ID not specified.");
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role_name'];
$dept_id = $_SESSION['dept_id'];

// Fetch Complaint Details
$stmt = $pdo->prepare("
    SELECT c.*, u.full_name as citizen_name, d.name as dept_name 
    FROM complaints c
    JOIN users u ON c.citizen_id = u.id
    JOIN departments d ON c.dept_id = d.id
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    die("Complaint not found.");
}

// Access Control
if ($role == 'Citizen' && $complaint['citizen_id'] != $user_id) {
    die("Access Denied.");
}
if ($role == 'Department Admin' && $complaint['dept_id'] != $dept_id) {
    die("Access Denied: This complaint belongs to another department.");
}
// Field Worker: Check if assigned?
if ($role == 'Field Worker') {
    // Check if assigned to this worker
    $stmt = $pdo->prepare("SELECT * FROM task_assignments WHERE complaint_id = ? AND worker_id = ?");
    $stmt->execute([$complaint_id, $user_id]);
    if ($stmt->rowCount() == 0) {
        // die("Access Denied: You are not assigned to this complaint."); 
        // Actually, maybe they can view but not edit? Let's restrict for now.
         die("Access Denied: You are not assigned to this complaint.");
    }
    $assignment = $stmt->fetch();
}

// Handle Form Submissions

// 1. Assign Task (Dept Admin)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_worker']) && $role == 'Department Admin') {
    $worker_id = $_POST['worker_id'];
    // Check if already assigned?
    $check = $pdo->prepare("SELECT id FROM task_assignments WHERE complaint_id = ? AND status != 'Completed'");
    $check->execute([$complaint_id]);
    
    if ($check->rowCount() > 0) {
        $msg = "Complaint is already assigned.";
        // Update assignment? For simplicity, we assume one active assignment.
    } else {
        $stmt = $pdo->prepare("INSERT INTO task_assignments (complaint_id, worker_id, assigned_by) VALUES (?, ?, ?)");
        $stmt->execute([$complaint_id, $worker_id, $user_id]);
        
        // Update Complaint Status
        $pdo->prepare("UPDATE complaints SET status = 'In Progress' WHERE id = ?")->execute([$complaint_id]);
        
        // Audit Log (Pending -> In Progress)
        $pdo->prepare("INSERT INTO status_logs (complaint_id, old_status, new_status, changed_by) VALUES (?, 'Pending', 'In Progress', ?)")
            ->execute([$complaint_id, $user_id]);

        // Notify Field Worker (DB + Email)
        $notif_msg = "New Task Assigned: Complaint #$complaint_id - " . $complaint['title'];
        $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$worker_id, $notif_msg]);
        
        // Email Field Worker
        $worker = getUserById($pdo, $worker_id);
        sendEmail($worker['email'], "New Task Assigned", "<p>You have been assigned a new complaint: <strong>{$complaint['title']}</strong>.</p><p>Please check your dashboard.</p>");

        $msg = "Task assigned successfully.";
        // Refresh
        header("Location: view.php?id=$complaint_id");
        exit();
    }
}

// 2. Update Status (Field Worker)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status']) && $role == 'Field Worker') {
    $new_status = $_POST['status']; // In Progress, Completed
    $notes = $_POST['notes'];
    
    // Update Task Assignment
    $stmt = $pdo->prepare("UPDATE task_assignments SET status = ?, completion_notes = ? WHERE complaint_id = ? AND worker_id = ?");
    $stmt->execute([$new_status, $notes, $complaint_id, $user_id]);
    
    // Get Old Status for Log
    $stmt = $pdo->prepare("SELECT status FROM complaints WHERE id = ?");
    $stmt->execute([$complaint_id]);
    $old_status = $stmt->fetchColumn();

    // Map Task Status to Complaint Status
    $comp_status = ($new_status == 'Completed') ? 'Resolved' : 'In Progress';
    $pdo->prepare("UPDATE complaints SET status = ? WHERE id = ?")->execute([$comp_status, $complaint_id]);

    // Audit Log
    $pdo->prepare("INSERT INTO status_logs (complaint_id, old_status, new_status, changed_by) VALUES (?, ?, ?, ?)")
        ->execute([$complaint_id, $old_status, $comp_status, $user_id]);

    // Notify Citizen (DB + Email)
    $citizen_msg = "Your complaint #$complaint_id status has been updated to: $comp_status. Notes: $notes";
    $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$complaint['citizen_id'], $citizen_msg]);
    
    // Email Citizen
    $citizen = getUserById($pdo, $complaint['citizen_id']);
    sendEmail($citizen['email'], "Complaint Status Update", "<h2>Status Updated</h2><p>Your compliant #$complaint_id is now <strong>$comp_status</strong>.</p><p>Notes: $notes</p>");

    // SMS Citizen
    if (!empty($citizen['contact_number'])) {
        $sms_msg = "Smart City Ops: Your complaint #$complaint_id is now '$comp_status'. Notes: $notes";
        sendSMS($citizen['contact_number'], $sms_msg);
    }

    // Notify Dept Admin (if completed)
    if ($new_status == 'Completed') {
        // Find who assigned it
        $stmt = $pdo->prepare("SELECT assigned_by FROM task_assignments WHERE complaint_id = ? AND worker_id = ?");
        $stmt->execute([$complaint_id, $user_id]);
        $assigner_id = $stmt->fetchColumn();
        
        if ($assigner_id) {
            $admin_msg = "Task Completed: Complaint #$complaint_id has been resolved by Field Worker.";
            $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$assigner_id, $admin_msg]);
            
            // Email Admin
            $admin = getUserById($pdo, $assigner_id);
            sendEmail($admin['email'], "Task Completed", "<p>Field Worker has resolved Complaint #$complaint_id.</p>");
        }
    }

    header("Location: view.php?id=$complaint_id");
    exit();
}

// Fetch Field Workers for Dropdown (Dept Admin)
$workers = [];
if ($role == 'Department Admin') {
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE r.name = 'Field Worker' AND u.dept_id = ?
    ");
    $stmt->execute([$dept_id]);
    $workers = $stmt->fetchAll();
}

include '../../includes/header.php';
?>

</div> 
<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <a href="javascript:history.back()" class="btn" style="margin-bottom: 20px; background: #95a5a6; color: white;">&larr; Back</a>
        
        <h2>Complaint #<?php echo $complaint['id']; ?> Details</h2>
        
        <div class="card">
            <div style="display: flex; justify-content: space-between;">
                <h3><?php echo htmlspecialchars($complaint['title']); ?></h3>
                <span class="status-badge status-<?php echo $complaint['status']; ?>"><?php echo $complaint['status']; ?></span>
            </div>
            <p><strong>Department:</strong> <?php echo $complaint['dept_name']; ?></p>
            <p><strong>Citizen:</strong> <?php echo htmlspecialchars($complaint['citizen_name']); ?></p>
            <p><strong>Date:</strong> <?php echo formatDate($complaint['created_at']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($complaint['location']); ?></p>
            <hr>
            <h4>Description</h4>
            <p><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>
        </div>

        <!-- TRACKER SYSTEM -->
        <div class="card" style="margin-top: 30px;">
            <h3>Complaint Lifecycle</h3>
            
            <!-- 1. Visual Progress Stepper -->
            <div class="tracker-wrapper">
                <div class="progress-track">
                    <?php 
                        // Determine Progress State
                        $status = $complaint['status'];
                        $is_lodged = true;
                        $is_progress = ($status == 'In Progress' || $status == 'Resolved' || $status == 'Closed');
                        $is_resolved = ($status == 'Resolved' || $status == 'Closed');
                    ?>
                    
                    <!-- Step 1: Lodged -->
                    <div class="step <?php echo $is_lodged ? 'completed' : ''; ?>">
                        <div class="icon-wrap"><i class="fas fa-file-alt"></i></div>
                        <span class="step-label">Lodged</span>
                    </div>

                    <!-- Step 2: In Progress -->
                    <div class="step <?php echo $is_resolved ? 'completed' : ($is_progress ? 'active' : ''); ?>">
                        <div class="icon-wrap"><i class="fas fa-tools"></i></div>
                        <span class="step-label">In Progress</span>
                    </div>

                    <!-- Step 3: Resolved -->
                    <div class="step <?php echo $is_resolved ? 'completed' : ''; ?>">
                        <div class="icon-wrap"><i class="fas fa-check-circle"></i></div>
                        <span class="step-label">Resolved</span>
                    </div>
                </div>
            </div>

            <!-- 2. Detailed Timeline -->
            <h3>Activity History</h3>
            <div class="timeline">
                <?php
                    // Fetch Logs
                    $log_stmt = $pdo->prepare("
                        SELECT s.*, u.full_name as changer_name 
                        FROM status_logs s
                        JOIN users u ON s.changed_by = u.id
                        WHERE s.complaint_id = ?
                        ORDER BY s.timestamp DESC
                    ");
                    $log_stmt->execute([$complaint['id']]);
                    $logs = $log_stmt->fetchAll();

                    // Fetch Task Assignment Notes (if any)
                    $note_stmt = $pdo->prepare("SELECT completion_notes, status, worker_id, assigned_at FROM task_assignments WHERE complaint_id = ?");
                    $note_stmt->execute([$complaint['id']]);
                    $assignment_info = $note_stmt->fetch();


                    // Add "Created" Initial Log if empty or effectively
                    // Display Logs
                    foreach($logs as $log):
                ?>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-date"><?php echo formatDate($log['timestamp']); ?></div>
                    <div class="timeline-content">
                        <h4>Status changed to <strong><?php echo $log['new_status']; ?></strong></h4>
                        <p>Updated by <?php echo htmlspecialchars($log['changer_name']); ?></p>
                        <?php 
                            // Show notes if this log entry corresponds to the current completion/progress 
                            // This is a naive match, ideally we'd have notes in logs.
                            // But if the log status is 'Resolved' or 'In Progress' and we have notes, show them.
                           if (($log['new_status'] == 'Resolved' || $log['new_status'] == 'In Progress') && !empty($assignment_info['completion_notes'])) {
                                echo "<p style='margin-top:5px; padding:5px; background:#f9f9f9; border-left: 3px solid #2ecc71;'><strong>Field Worker Note:</strong> ".htmlspecialchars($assignment_info['completion_notes'])."</p>";
                            }
                        ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Original Creation Log (Always last) -->
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-date"><?php echo formatDate($complaint['created_at']); ?></div>
                    <div class="timeline-content">
                        <h4>Complaint Lodged</h4>
                        <p>Role: Citizen (<?php echo htmlspecialchars($complaint['citizen_name']); ?>)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dept Admin Actions -->
        <?php if($role == 'Department Admin' && $complaint['status'] != 'Closed' && $complaint['status'] != 'Resolved'): ?>
        <div class="card" style="margin-top: 20px;">
            <h3>Assign Field Worker</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Select Worker</label>
                    <select name="worker_id" class="form-control" require>
                        <option value="">-- Choose Worker --</option>
                        <?php foreach($workers as $w): ?>
                            <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="assign_worker" class="btn btn-primary">Assign Task</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Field Worker Actions -->
        <?php if($role == 'Field Worker' && $complaint['status'] != 'Resolved'): ?>
        <div class="card" style="margin-top: 20px;">
            <h3>Update Status</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Task Status</label>
                    <select name="status" class="form-control">
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Completion Notes</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" name="update_status" class="btn btn-success" style="background-color: #27ae60; color: white;">Update Status</button>
            </form>
        </div>
        <?php endif; ?>

        </div>
    </div>
</div>
<div class="container" style="display:none;">
<?php include '../../includes/footer.php'; ?>
