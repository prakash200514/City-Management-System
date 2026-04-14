<?php
// pages/auth/register.php
require '../../config/db.php';
require '../../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $contact_number = sanitize($_POST['contact_number']);

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($contact_number)) {
        $error = "Please fill all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email already registered.";
        } else {
            // Get Citizen Role ID
            $stmt = $pdo->query("SELECT id FROM roles WHERE name = 'Citizen'");
            $role = $stmt->fetch();
            $role_id = $role['id'];

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role_id, contact_number) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$full_name, $email, $hashed_password, $role_id, $contact_number]);
                $success = "Registration successful! You can now <a href='login.php'>Login</a>.";
            } catch (PDOException $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>

<?php include '../../includes/header.php'; ?>

<div class="auth-container">
    <h2>Citizen Registration</h2>
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" required placeholder="John Doe">
        </div>
        <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control" required placeholder="example@gmail.com">
        </div>
        <div class="form-group">
            <label class="form-label">Contact Number *</label>
            <input type="tel" name="contact_number" class="form-control" required placeholder="+91 9876543210" pattern="[0-9+\s-]{10,15}" title="Please enter a valid phone number (10-15 digits)">
        </div>
        <div class="form-group">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">Confirm Password *</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    <p style="margin-top: 15px; text-align: center;">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

<?php include '../../includes/footer.php'; ?>
