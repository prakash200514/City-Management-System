<?php
// pages/dashboard/manage_depts.php
require '../../config/db.php';
require '../../includes/functions.php';

requireRole('Super Admin');

$error = '';
$success = '';

// Add Department
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    
    if (empty($name)) {
        $error = "Department Name is required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $success = "Department added successfully.";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

$departments = $pdo->query("SELECT * FROM departments")->fetchAll();

include '../../includes/header.php';
?>

</div> 
<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h2>Manage Departments</h2>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
             <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 20px;">
            <h3>Add New Department</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" require>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add Department</button>
            </form>
        </div>

        <div class="card">
            <h3>Existing Departments</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($departments as $d): ?>
                    <tr>
                        <td><?php echo $d['id']; ?></td>
                        <td><?php echo htmlspecialchars($d['name']); ?></td>
                        <td><?php echo htmlspecialchars($d['description']); ?></td>
                        <td><?php echo formatDate($d['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="container" style="display:none;">
<?php include '../../includes/footer.php'; ?>
