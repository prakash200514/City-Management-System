<?php
// pages/auth/dept_login.php
require '../../config/db.php';
require '../../includes/functions.php';

// Must be logged in as a Citizen
requireLogin();
if ($_SESSION['role_name'] != 'Citizen') {
    header("Location: ../dashboard/citizen.php");
    exit();
}

$error = '';
$dept_id = isset($_GET['dept_id']) ? intval($_GET['dept_id']) : 0;

// Validate department
if (!$dept_id) {
    header("Location: ../complaints/lodge.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
$stmt->execute([$dept_id]);
$department = $stmt->fetch();

if (!$department) {
    header("Location: ../complaints/lodge.php");
    exit();
}

// Department icon mapping
$dept_icons = [
    'Water Supply'               => 'fa-faucet',
    'Electricity'                => 'fa-bolt',
    'Sanitation'                 => 'fa-trash',
    'Roads'                      => 'fa-road',
    'Public Complaint Management'=> 'fa-users',
    'Traffic'                    => 'fa-traffic-light'
];
$icon  = $dept_icons[$department['name']] ?? 'fa-building';
$color = ($department['name'] == 'Electricity') ? '#f59e0b'
       : (($department['name'] == 'Water Supply') ? '#3b82f6' : '#6366f1');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please fill in both fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password']) && $user['id'] == $_SESSION['user_id']) {
            // Mark this department as verified for this session
            if (!isset($_SESSION['dept_verified'])) {
                $_SESSION['dept_verified'] = [];
            }
            $_SESSION['dept_verified'][$dept_id] = true;
            header("Location: ../complaints/lodge.php?dept_id=" . $dept_id);
            exit();
        } else {
            $error = "Invalid credentials. Please re-enter your email and password.";
        }
    }
}
?>
<?php include '../../includes/header.php'; ?>

<style>
    .dept-login-wrapper {
        min-height: calc(100vh - 80px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        background: linear-gradient(135deg, rgba(99,102,241,0.05) 0%, rgba(139,92,246,0.05) 100%);
    }

    .dept-login-card {
        background: var(--card-bg, #fff);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.10);
        padding: 2.5rem 2.5rem;
        width: 100%;
        max-width: 440px;
        animation: fadeInUp 0.5s ease both;
    }

    .dept-login-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 1.2rem;
    }

    .dept-login-title {
        text-align: center;
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 0.4rem;
        color: var(--text-main, #1e293b);
    }

    .dept-login-subtitle {
        text-align: center;
        color: var(--text-muted, #64748b);
        font-size: 0.9rem;
        margin-bottom: 1.8rem;
        line-height: 1.5;
    }

    .dept-login-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .dept-back-link {
        text-align: center;
        margin-top: 1.2rem;
        font-size: 0.9rem;
        color: var(--text-muted, #64748b);
    }

    .dept-back-link a {
        color: var(--primary, #6366f1);
        text-decoration: none;
        font-weight: 500;
    }

    .dept-back-link a:hover {
        text-decoration: underline;
    }

    .alert-error {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        margin-bottom: 1.2rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .lock-hint {
        background: rgba(99,102,241,0.07);
        border-radius: 10px;
        padding: 0.7rem 1rem;
        font-size: 0.83rem;
        color: var(--text-muted, #64748b);
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        margin-bottom: 1.4rem;
    }

    .lock-hint i {
        color: #6366f1;
        margin-top: 2px;
        flex-shrink: 0;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="dept-login-wrapper">
    <div class="dept-login-card">

        <!-- Department Icon -->
        <div class="dept-login-icon" style="background: <?php echo $color; ?>18;">
            <i class="fas <?php echo $icon; ?>" style="color: <?php echo $color; ?>;"></i>
        </div>

        <div class="dept-login-title">
            <?php echo htmlspecialchars($department['name']); ?>
        </div>
        <p class="dept-login-subtitle">
            Please verify your identity to access this department and lodge a complaint.
        </p>

        <!-- Security hint -->
        <div class="lock-hint">
            <i class="fas fa-lock"></i>
            <span>For your security, we ask you to confirm your login before entering a department. Use the same credentials you registered with.</span>
        </div>

        <!-- Error -->
        <?php if ($error): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="" autocomplete="off">
            <input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>">

            <div class="form-group">
                <label class="form-label">Your Email</label>
                <input type="email" name="email" class="form-control"
                       placeholder="Enter your registered email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">
                <i class="fas fa-unlock-alt" style="margin-right: 6px;"></i>
                Verify & Enter Department
            </button>
        </form>

        <div class="dept-back-link">
            <a href="../complaints/lodge.php">
                <i class="fas fa-arrow-left" style="font-size: 0.8rem;"></i> Back to Department Selection
            </a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
