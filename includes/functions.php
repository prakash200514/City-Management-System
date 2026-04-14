<?php
// includes/functions.php

session_start();

// Redirect if not logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
}

// Redirect if not specific role
function requireRole($role_name) {
    requireLogin();
    if ($_SESSION['role_name'] !== $role_name) {
        die("Access Denied: You do not have permission to view this page.");
    }
}

// Sanitize Input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Get User by ID
function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Format Date
function formatDate($date) {
    return date("d M Y, h:i A", strtotime($date));
}

// Send Email Helper
// Send Email Helper (PHPMailer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

if (file_exists(__DIR__ . '/../config/email_config.php')) {
    require_once __DIR__ . '/../config/email_config.php';
}

function sendEmail($to, $subject, $message) {
    // Log to file (Reference)
    $log_file = __DIR__ . '/../email_log.txt';
    $timestamp = date("Y-m-d H:i:s");
    $log_entry = "[$timestamp] To: $to | Subject: $subject" . PHP_EOL; // Shortened log
    file_put_contents($log_file, $log_entry, FILE_APPEND);

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('SMTP_USER') ? SMTP_USER : 'user@example.com';
        $mail->Password   = defined('SMTP_PASS') ? SMTP_PASS : 'secret';
        $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;

        // Recipients
        $mail->setFrom(defined('FROM_EMAIL') ? FROM_EMAIL : 'no-reply@example.com', defined('FROM_NAME') ? FROM_NAME : 'System');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log Error
        $error_entry = "[$timestamp] Mailer Error: {$mail->ErrorInfo}" . PHP_EOL;
        file_put_contents($log_file, $error_entry, FILE_APPEND);
        return false;
    }
}

// Send SMS Helper (Simulation)
function sendSMS($to, $message) {
    // In a real-world scenario, you would use an SMS Gateway API here (e.g., Twilio, Nexmo, Fast2SMS).
    // Example (Twilio-like):
    // $client = new Client($sid, $token);
    // $client->messages->create($to, ['from' => '+1234567890', 'body' => $message]);

    // For Development/Demo: Log the SMS to a file
    $log_file = __DIR__ . '/../sms_log.txt';
    $timestamp = date("Y-m-d H:i:s");
    $log_entry = "[$timestamp] To: $to | Message: $message" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    
    return true;
}
?>
