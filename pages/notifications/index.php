<?php
// pages/notifications/index.php
require '../../config/db.php';
require '../../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Mark all as read
if (isset($_GET['read_all'])) {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
    header("Location: index.php");
    exit();
}

// Fetch Notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Notifications</h2>
            <a href="?read_all=1" class="btn btn-primary" style="font-size: 0.8rem; padding: 0.5rem 1rem;">Mark all as read</a>
        </div>
        
        <div class="card">
            <?php if (count($notifications) > 0): ?>
                <ul style="list-style: none; padding: 0;">
                <?php foreach($notifications as $notif): ?>
                    <li style="padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.05); background: <?php echo $notif['is_read'] ? 'transparent' : 'rgba(99, 102, 241, 0.05)'; ?>; border-radius: 8px; margin-bottom: 5px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: <?php echo $notif['is_read'] ? 'normal' : 'bold'; ?>; color: var(--text-main);">
                                <?php echo htmlspecialchars($notif['message']); ?>
                            </span>
                            <small style="color: var(--text-muted); white-space: nowrap; margin-left: 10px;">
                                <?php echo formatDate($notif['created_at']); ?>
                            </small>
                        </div>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-muted);">You have no notifications.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
