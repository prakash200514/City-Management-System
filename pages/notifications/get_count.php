<?php
// pages/notifications/get_count.php
require '../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$count = $stmt->fetchColumn();

header('Content-Type: application/json');
echo json_encode(['count' => $count]);
?>
