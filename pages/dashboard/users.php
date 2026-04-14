<?php
// pages/dashboard/users.php
require '../../config/db.php';
require '../../includes/functions.php';

requireLogin();
if ($_SESSION['role_name'] != 'Super Admin' && $_SESSION['role_name'] != 'Department Admin') {
    die("Access Denied");
}

$current_role = $_SESSION['role_name'];
$current_dept_id = $_SESSION['dept_id'];

$error = '';
$success = '';

// Add User Logic
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if ($current_role != 'Super Admin') {
        die("Access Denied");
    }
    $delete_id = $_GET['delete'];
    
    // Prevent deleting yourself
    if ($delete_id == $_SESSION['user_id']) {
        $error = "You cannot delete yourself.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$delete_id]);
            $success = "User deleted successfully.";
        } catch (PDOException $e) {
            // Check for foreign key constraint violation
            if ($e->getCode() == 23000) {
                 $error = "Cannot delete user. They have associated complaints or tasks. Disable their account instead (Feature coming soon) or reassign their data.";
            } else {
                 $error = "Error deleting user: " . $e->getMessage();
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $contact_number = sanitize($_POST['contact_number']); // Add Contact Number
    $role_id = $_POST['role_id'];
    $password = trim($_POST['password']); 
    
    // Dept Logic
    if ($current_role == 'Department Admin') {
        $dept_id = $current_dept_id; // Auto-assign
    } else {
        $dept_id = !empty($_POST['dept_id']) ? $_POST['dept_id'] : NULL;
    }

    if (empty($full_name) || empty($email) || empty($password) || empty($role_id)) {
        $error = "All fields are required.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, contact_number, password, role_id, dept_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$full_name, $email, $contact_number, $hashed_password, $role_id, $dept_id]);
                $success = "User added successfully.";
            } catch (PDOException $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Fetch Roles for Dropdown
if ($current_role == 'Super Admin') {
    $roles = $pdo->query("SELECT * FROM roles WHERE name != 'Citizen'")->fetchAll();
} else {
    $roles = $pdo->query("SELECT * FROM roles WHERE name = 'Field Worker'")->fetchAll();
}

// Fetch Departments
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();

// Fetch Lists
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$list_query = "SELECT u.*, r.name as role_name, d.name as dept_name FROM users u JOIN roles r ON u.role_id = r.id LEFT JOIN departments d ON u.dept_id = d.id";
$list_params = [];

$conditions = [];

if ($current_role == 'Department Admin') {
    $conditions[] = "u.dept_id = ?";
    $list_params[] = $current_dept_id;
}

if ($search) {
    $conditions[] = "(u.full_name LIKE ? OR u.email LIKE ?)";
    $list_params[] = "%$search%";
    $list_params[] = "%$search%";
}

if (!empty($conditions)) {
    $list_query .= " WHERE " . implode(" AND ", $conditions);
}

$list_query .= " ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($list_query);
$stmt->execute($list_params);
$users = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h2>Manage Users (<?php echo $current_role; ?> View)</h2>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
             <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Search Bar -->
        <div class="card" style="margin-bottom: 20px; padding: 1.5rem;">
             <form method="GET" action="" class="search-form" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <input type="text" name="search" class="form-control" placeholder="Search by Name or Email..." value="<?php echo htmlspecialchars($search); ?>" style="width: 100%;">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                <?php if($search): ?>
                    <a href="users.php" class="btn" style="background: #94a3b8; color: white;">Clear</a>
                <?php endif; ?>
             </form>
        </div>
        
        <div class="card" style="margin-bottom: 2rem;">
            <h3><i class="fas fa-user-plus"></i> Add New User</h3>
            <form method="POST" action="" class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" placeholder="e.g. +91 9876543210">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Enter strong password">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-control" required>
                        <option value="">-- Select Role --</option>
                        <?php foreach($roles as $r): ?>
                            <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($current_role == 'Super Admin'): ?>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select name="dept_id" class="form-control">
                        <option value="">-- Select Department --</option>
                        <?php foreach($departments as $d): ?>
                            <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: ?>
                    <input type="hidden" name="dept_id" value="<?php echo $current_dept_id; ?>">
                <?php endif; ?>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-plus-circle"></i> Create User
                    </button>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Registered Users</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Since</th>
                        <?php if($current_role == 'Super Admin'): ?>
                        <th>Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sno = 1;
                    foreach($users as $u): 
                    ?>
                    <tr>
                        <td><?php echo $sno++; ?></td>
                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['role_name']); ?></td>
                        <td><?php echo htmlspecialchars($u['dept_name'] ?? 'N/A'); ?></td>
                        <td><?php echo formatDate($u['created_at']); ?></td>
                        <?php if($current_role == 'Super Admin'): ?>
                        <td>
                            <?php if($u['id'] != $_SESSION['user_id']): ?>
                            <a href="users.php?delete=<?php echo $u['id']; ?>" class="btn" style="background: #fee2e2; color: #ef4444; padding: 5px 10px; font-size: 0.8rem;" onclick="return confirm('Are you sure? This action cannot be undone.');">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
