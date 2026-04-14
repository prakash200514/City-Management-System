<?php
// includes/sidebar.php
$role = $_SESSION['role_name'] ?? '';

// Determine Dashboard URL based on Role
$dashboard_map = [
    'Super Admin' => 'super_admin.php',
    'Department Admin' => 'dept_admin.php',
    'Field Worker' => 'field_worker.php',
    'Citizen' => 'citizen.php'
];
$dashboard_url = isset($dashboard_map[$role]) ? $dashboard_map[$role] : 'citizen.php';
?>
<div class="sidebar">
    <div class="mobile-sidebar-header" style="display: none; padding: 1rem; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); margin-bottom: 1rem;">
        <span style="font-weight: 800; color: var(--primary); font-size: 1.2rem;">Menu</span>
        <button id="sidebar-close" style="background: none; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer;">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="/city/pages/dashboard/<?php echo $dashboard_url; ?>" class="sidebar-link active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        
        <?php if($role == 'Citizen'): ?>
            <li><a href="/city/pages/complaints/lodge.php" class="sidebar-link"><i class="fas fa-file-signature"></i> Lodge Complaint</a></li>
            <li><a href="/city/pages/dashboard/citizen.php" class="sidebar-link"><i class="fas fa-list-alt"></i> My Complaints</a></li>
        <?php endif; ?>

        <?php if($role == 'Department Admin'): ?>
            <li><a href="/city/pages/dashboard/manage_complaints.php" class="sidebar-link"><i class="fas fa-tasks"></i> Manage Complaints</a></li>
            <li><a href="/city/pages/dashboard/field_workers.php" class="sidebar-link"><i class="fas fa-hard-hat"></i> Field Workers</a></li>
            <li><a href="/city/pages/dashboard/analytics.php" class="sidebar-link"><i class="fas fa-chart-line"></i> Reports</a></li>
        <?php endif; ?>

        <?php if($role == 'Field Worker'): ?>
            <li><a href="/city/pages/dashboard/field_worker.php" class="sidebar-link"><i class="fas fa-clipboard-check"></i> My Tasks</a></li>
        <?php endif; ?>

        <?php if($role == 'Super Admin'): ?>
            <li><a href="/city/pages/dashboard/all_complaints.php" class="sidebar-link"><i class="fas fa-folder-open"></i> All Complaints</a></li>
            <li><a href="/city/pages/dashboard/manage_depts.php" class="sidebar-link"><i class="fas fa-building"></i> Departments</a></li>
            <li><a href="/city/pages/dashboard/analytics.php" class="sidebar-link"><i class="fas fa-chart-pie"></i> Analytics</a></li>
            <li><a href="/city/pages/dashboard/users.php" class="sidebar-link"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="/city/pages/dashboard/audit_logs.php" class="sidebar-link"><i class="fas fa-history"></i> Audit Logs</a></li>
        <?php endif; ?>

        <li><a href="/city/pages/auth/logout.php" class="sidebar-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>
