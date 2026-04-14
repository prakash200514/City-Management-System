<?php
// pages/dashboard/analytics.php
require '../../config/db.php';
require '../../includes/functions.php';

requireLogin();
if ($_SESSION['role_name'] != 'Super Admin' && $_SESSION['role_name'] != 'Department Admin') {
    die("Access Denied");
}

$dept_filter = "";
$params = [];

if ($_SESSION['role_name'] == 'Department Admin') {
    $dept_filter = " WHERE dept_id = ?";
    $params[] = $_SESSION['dept_id'];
}

// 1. Complaints by Status
$query_status = "SELECT status, COUNT(*) as count FROM complaints $dept_filter GROUP BY status";
$stmt = $pdo->prepare($query_status);
$stmt->execute($params);
$status_data = $stmt->fetchAll();

// 2. Complaints by Department (Only for Super Admin)
$dept_data = [];
if ($_SESSION['role_name'] == 'Super Admin') {
    $query_dept = "SELECT d.name, COUNT(c.id) as count FROM departments d LEFT JOIN complaints c ON d.id = c.dept_id GROUP BY d.name";
    $dept_data = $pdo->query($query_dept)->fetchAll();
}

include '../../includes/header.php';
?>

</div> 
<div class="dashboard-wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <h2>Analytics & Reports</h2>
        
        <div class="card-grid">
            <div class="card">
                <h3>Complaints Status</h3>
                <div style="height: 350px; position: relative; width: 100%;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <?php if ($_SESSION['role_name'] == 'Super Admin'): ?>
            <div class="card">
                <h3>Complaints per Department</h3>
                <div style="height: 350px; position: relative; width: 100%;">
                    <canvas id="deptChart"></canvas>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="container" style="display:none;">
<?php include '../../includes/footer.php'; ?>

<script>
    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusLabels = <?php echo json_encode(array_column($status_data, 'status')); ?>;
    const statusCounts = <?php echo json_encode(array_column($status_data, 'count')); ?>;

    // Doughnut with Gradient look
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                backgroundColor: [
                    'rgba(245, 158, 11, 0.8)', // Amber
                    'rgba(99, 102, 241, 0.8)', // Indigo
                    'rgba(16, 185, 129, 0.8)', // Emerald
                    'rgba(148, 163, 184, 0.8)' // Slate
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            cutout: '75%',
            animation: {
                animateScale: true,
                animateRotate: true
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { 
                        font: { family: 'Inter', size: 12, weight: '600' },
                        color: '#475569',
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    <?php if ($_SESSION['role_name'] == 'Super Admin'): ?>
    // Bar Chart with Translucency
    const deptCtx = document.getElementById('deptChart').getContext('2d');
    const deptLabels = <?php echo json_encode(array_column($dept_data, 'name')); ?>;
    const deptCounts = <?php echo json_encode(array_column($dept_data, 'count')); ?>;

    // Create gradient
    let gradient = deptCtx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.8)');
    gradient.addColorStop(1, 'rgba(168, 85, 247, 0.4)');

    new Chart(deptCtx, {
        type: 'bar',
        data: {
            labels: deptLabels,
            datasets: [{
                label: 'Complaints',
                data: deptCounts,
                backgroundColor: gradient,
                borderRadius: 8,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { color: '#94a3b8', font: { family: 'Inter' } },
                    grid: { color: 'rgba(0,0,0,0.03)', borderDash: [5, 5], drawBorder: false }
                },
                x: {
                    ticks: { color: '#64748b', font: { family: 'Inter', weight: '600' } },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
    <?php endif; ?>
</script>
