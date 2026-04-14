<?php 
require_once 'config/db.php';
include 'includes/header.php'; 
?>

<div class="landing-wrapper" style="text-align: center; padding: 50px 0;">
    <h1>Welcome to Smart City Operations & Resource Tracking System</h1>
    <p class="lead">Efficiently managing city resources and public grievances.</p>
    
    <div style="margin-top: 30px;">
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="pages/auth/login.php" class="btn btn-primary" style="margin: 0 10px;">Login</a>
            <a href="pages/auth/register.php" class="btn btn-primary" style="background-color: #27ae60; margin: 0 10px;">Register (Citizen)</a>
        <?php else: ?>
            <a href="pages/dashboard/index.php" class="btn btn-primary">Go to Dashboard</a>
        <?php endif; ?>
    </div>

    <div class="card-grid" style="margin-top: 50px; text-align: center;">
        <div class="card">
            <div style="font-size: 2.5rem; color: #3b82f6; margin-bottom: 15px;">
                <i class="fas fa-edit"></i>
            </div>
            <h3>Complaint Management</h3>
            <p style="color: var(--text-muted);">Citizens can report issues related to water, electricity, roads, and sanitation.</p>
        </div>
        <div class="card">
            <div style="font-size: 2.5rem; color: #8b5cf6; margin-bottom: 15px;">
                <i class="fas fa-search-location"></i>
            </div>
            <h3>Real-time Tracking</h3>
            <p style="color: var(--text-muted);">Track the status of your complaints in real-time with updates.</p>
        </div>
        <div class="card">
            <div style="font-size: 2.5rem; color: #ec4899; margin-bottom: 15px;">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3>Department Efficiency</h3>
            <p style="color: var(--text-muted);">Departments can manage tasks and resources effectively.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
