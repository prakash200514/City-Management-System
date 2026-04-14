<?php
// pages/complaints/lodge.php
require '../../config/db.php';
require '../../includes/functions.php';

requireLogin(); // Anyone logged in can lodge? Or just Citizen? 
// requireRole('Citizen'); // Let's keep it restricted to Citizen for now
if ($_SESSION['role_name'] != 'Citizen') {
    die("Only Citizens can lodge complaints.");
}

$error = '';
$success = '';

// Step 1: Select Department (if not set)
$dept_id = isset($_GET['dept_id']) ? intval($_GET['dept_id']) : null;
$selected_dept = null;

if ($dept_id) {
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$dept_id]);
    $selected_dept = $stmt->fetch();
    
    if (!$selected_dept) {
        $error = "Invalid Department Selected.";
        $dept_id = null; // Reset
    } else {
        // Department Login Gate: user must have verified themselves for this dept
        if (empty($_SESSION['dept_verified'][$dept_id])) {
            header("Location: /city/pages/auth/dept_login.php?dept_id=" . $dept_id);
            exit();
        }
    }
}

// Handle Form Submission (Step 2)
if ($_SERVER["REQUEST_METHOD"] == "POST" && $dept_id) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $location = sanitize($_POST['location']);
    $citizen_id = $_SESSION['user_id'];

    if (empty($title) || empty($description) || empty($location)) {
        $error = "Please fill all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO complaints (citizen_id, dept_id, title, description, location) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$citizen_id, $dept_id, $title, $description, $location]);
            
            // Send Email to Citizen
            $citizen = getUserById($pdo, $citizen_id);
            $subject = "Complaint Lodged: $title";
            $message = "<h2>Complaint Lodged Successfully</h2>
                        <p>Dear {$citizen['full_name']},</p>
                        <p>Your complaint has been successfully lodged with the <strong>{$selected_dept['name']}</strong> department.</p>
                        <hr>
                        <h3>Complaint Details:</h3>
                        <p><strong>Title:</strong> $title</p>
                        <p><strong>Location:</strong> $location</p>
                        <p><strong>Description:</strong><br>" . nl2br($description) . "</p>
                        <hr>
                        <p>We will address it shortly. Thank you only for being a responsible citizen.</p>";
            sendEmail($citizen['email'], $subject, $message);

            // Send SMS to Citizen
            if (!empty($citizen['contact_number'])) {
                $last_id = $pdo->lastInsertId();
                $sms_msg = "Smart City Ops: Your complaint #$last_id ('$title') has been lodged successfully. We will resolve it soon.";
                sendSMS($citizen['contact_number'], $sms_msg);
            }

            // Send Email & Notification to Department Admin(s)
            $stmt_admin = $pdo->prepare("SELECT u.id, u.email FROM users u JOIN roles r ON u.role_id = r.id WHERE u.dept_id = ? AND r.name = 'Department Admin'");
            $stmt_admin->execute([$dept_id]);
            $dept_admins = $stmt_admin->fetchAll();

            foreach ($dept_admins as $admin) {
                // Email
                $admin_subject = "New Complaint Alert: $title";
                $admin_message = "<h2>New Complaint Received</h2>
                                  <p>Hello Admin,</p>
                                  <p>A new complaint has been lodged for your department (<strong>{$selected_dept['name']}</strong>).</p>
                                  <p><strong>Title:</strong> $title</p>
                                  <p><strong>Lodged By:</strong> {$citizen['full_name']} ({$citizen['email']})</p>
                                  <p><strong>Location:</strong> $location</p>
                                  <p>Please log in to the dashboard to review and assign detailed tasks.</p>";
                sendEmail($admin['email'], $admin_subject, $admin_message);
                
                // In-App Notification
                $notif_msg = "New Complaint: '$title' at $location";
                $stmt_notif = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $stmt_notif->execute([$admin['id'], $notif_msg]);
            }
            
            // In-App Notification for Citizen (Confirmation)
            $citizen_msg = "Complaint '$title' submitted successfully.";
            $stmt_c_notif = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt_c_notif->execute([$citizen_id, $citizen_msg]);

            $success = "Complaint lodged successfully! <a href='../dashboard/citizen.php'>View Dashboard</a>";
            // Clear department verification so re-login is required next time
            if (isset($_SESSION['dept_verified'][$dept_id])) {
                unset($_SESSION['dept_verified'][$dept_id]);
            }
            // Reset to allow new complaint
            $dept_id = null; 
            $selected_dept = null; 
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Fetch All Departments for Grid
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();

// Icon Mapping (Hardcoded for aesthetics)
$dept_icons = [
    'Water Supply' => 'fa-faucet',
    'Electricity' => 'fa-bolt',
    'Sanitation' => 'fa-trash',
    'Roads' => 'fa-road', 
    'Public Complaint Management' => 'fa-users',
    'Traffic' => 'fa-traffic-light'
];

include '../../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!$dept_id): ?>
            <!-- STEP 1: Department Selection Grid -->
            <h2>Select Department</h2>
            <p style="color: var(--text-muted); margin-bottom: 30px;">Choose the relevant department to lodge your complaint.</p>
            
            <div class="card-grid">
                <?php foreach($departments as $dept): ?>
                    <?php 
                        $icon = $dept_icons[$dept['name']] ?? 'fa-building'; 
                        $color = ($dept['name'] == 'Electricity') ? '#f59e0b' : (($dept['name'] == 'Water Supply') ? '#3b82f6' : '#6366f1');
                    ?>
                    <a href="/city/pages/auth/dept_login.php?dept_id=<?php echo $dept['id']; ?>" class="card" style="text-decoration: none; text-align: center; transition: all 0.3s; color: inherit; display: block;">
                        <div style="font-size: 2.5rem; color: <?php echo $color; ?>; margin-bottom: 15px;">
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <h3 style="font-size: 1.1rem; margin-bottom: 5px; color: var(--text-main);"><?php echo htmlspecialchars($dept['name']); ?></h3>
                        <p style="font-size: 0.9rem; color: var(--text-muted);"><?php echo htmlspecialchars(substr($dept['description'], 0, 50)); ?>...</p>
                    </a>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- STEP 2: Complaint Form -->
            <a href="lodge.php" class="btn" style="margin-bottom: 20px; background: rgba(0,0,0,0.05); color: var(--text-main);">&larr; Back to Departments</a>

            <div class="card" style="max-width: 600px; margin: 0 auto; animation: fadeInUp 0.5s ease;">
                <h2 style="margin-bottom: 20px;">Lodge Complaint: <span style="color: var(--primary);"><?php echo htmlspecialchars($selected_dept['name']); ?></span></h2>
                
                <form method="POST" action="">
                    <!-- Hidden Dept ID -->
                    <input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>">

                    <div class="form-group">
                        <label class="form-label">Subject / Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. No water supply since morning" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Location / Address</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g. 123 Main Street, Ward 4" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Please describe the issue in detail..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Complaint</button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</div>
<div class="container" style="display:none;">
<?php include '../../includes/footer.php'; ?>
