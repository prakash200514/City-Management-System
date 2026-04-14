<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart City Operations</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <link rel="stylesheet" href="/city/assets/css/style.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/city/assets/js/notifications.js" defer></script>
    <script src="/city/assets/js/ui.js" defer></script>
</head>
<body>
    <nav class="navbar">
        <a href="/city/index.php" class="navbar-brand">
            <i class="fas fa-city"></i> Smart City Ops
        </a>
        <button class="menu-toggle" id="menu-toggle" style="display: none; background: none; border: none; font-size: 1.5rem; color: var(--primary); cursor: pointer;">
            <i class="fas fa-bars"></i>
        </button>
        <ul class="navbar-nav">
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php
                // Fetch Unread Notifications Count
                $uid = $_SESSION['user_id'];
                $n_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                $n_stmt->execute([$uid]);
                $unread_count = $n_stmt->fetchColumn();
                ?>
                <li>
                    <a href="/city/pages/notifications/index.php" class="nav-link" style="position: relative;">
                        <i class="fas fa-bell"></i>
                        <span id="notif-badge" style="position: absolute; top: 0; right: 0; background: #ef4444; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.7rem; display: <?php echo ($unread_count > 0) ? 'inline-block' : 'none'; ?>;"><?php echo $unread_count; ?></span>
                    </a>
                </li>
                <li><a href="/city/pages/dashboard/<?php echo strtolower(str_replace(' ', '_', $_SESSION['role_name'])); ?>.php" class="nav-link">Dashboard</a></li>
                <li><a href="/city/pages/auth/logout.php" class="nav-link" style="color: #ef4444; border-color: #fee2e2; background: #fef2f2;">Logout</a></li>
            <?php else: ?>
                <li><a href="/city/pages/auth/login.php" class="nav-link">Login</a></li>
                <li><a href="/city/pages/auth/register.php" class="nav-link" style="background: var(--primary); color: white; border: none; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="container">
        <!-- Main Content Starts Here -->
