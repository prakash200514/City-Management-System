<?php
// pages/auth/login.php
require '../../config/db.php';
require '../../includes/functions.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize($_POST['email']);
    $password = trim($_POST['password']); // Trim whitespace!

    if (empty($email) || empty($password)) {
        $error = "Please fill all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // Prevent Session Fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name']; // 'Super Admin', 'Citizen', etc.
            $_SESSION['dept_id'] = $user['dept_id'];
            $_SESSION['full_name'] = $user['full_name'];

            // Redirect based on role
            $role_name = $user['role_name'];
            if ($role_name == 'Super Admin') {
                header("Location: ../dashboard/super_admin.php");
            } elseif ($role_name == 'Department Admin') {
                header("Location: ../dashboard/dept_admin.php");
            } elseif ($role_name == 'Field Worker') {
                header("Location: ../dashboard/field_worker.php");
            } else {
                header("Location: ../dashboard/citizen.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="auth-container">
    <h2>Login</h2>
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="" autocomplete="off">
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="">
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <p style="margin-top: 15px; text-align: center;">
        Don't have an account? <a href="register.php">Register here</a>
    </p>
</div>

<?php include '../../includes/footer.php'; ?>
