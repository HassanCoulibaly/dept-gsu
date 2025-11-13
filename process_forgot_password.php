<?php
session_start();
require_once 'config.php';

// Verify this is a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: forgot_password.php");
    exit();
}

// CSRF Protection
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['reset_error'] = "Invalid request. Please try again.";
    header("Location: forgot_password.php");
    exit();
}

// Get and validate email
$email = trim($_POST['email'] ?? '');

if (empty($email) || !validateEmail($email)) {
    $_SESSION['reset_error'] = "Please enter a valid email address.";
    header("Location: forgot_password.php");
    exit();
}

// Get database connection
$conn = getDBConnection();

// Check if user exists
$stmt = $conn->prepare("SELECT id, full_name, email, status FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // For security, don't reveal if email exists or not
    // Show same success message either way
    $_SESSION['reset_success'] = "If an account exists with that email, a password reset link has been sent.";
    $stmt->close();
    $conn->close();
    header("Location: forgot_password.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Check if account is active
if ($user['status'] !== 'active') {
    $_SESSION['reset_error'] = "Your account is not active. Please contact support.";
    $conn->close();
    header("Location: forgot_password.php");
    exit();
}

// Generate secure reset token
$token = bin2hex(random_bytes(32));
$token_hash = hash('sha256', $token);
$expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour

// Delete any existing reset tokens for this user
$stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$stmt->close();

// Insert new reset token
$stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at, created_at) 
                       VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iss", $user['id'], $token_hash, $expires_at);

if ($stmt->execute()) {
    // Create reset link
    $reset_link = APP_URL . "/reset_password.php?token=" . urlencode($token);
    
    // Send password reset email
    $subject = "Password Reset Request - " . APP_NAME;
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { padding: 30px; background: #f9f9f9; }
            .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .warning { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin: 20px 0; color: #856404; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . APP_NAME . "</h1>
            </div>
            <div class='content'>
                <h2>Password Reset Request</h2>
                <p>Hi " . htmlspecialchars($user['full_name']) . ",</p>
                <p>We received a request to reset your password. Click the button below to create a new password:</p>
                
                <div style='text-align: center;'>
                    <a href='" . $reset_link . "' class='button'>Reset Password</a>
                </div>
                
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>" . $reset_link . "</p>
                
                <div class='warning'>
                    <strong>⚠️ Important:</strong>
                    <ul style='margin: 10px 0 0 0; padding-left: 20px;'>
                        <li>This link will expire in <strong>1 hour</strong></li>
                        <li>If you didn't request this reset, please ignore this email</li>
                        <li>Your password will remain unchanged unless you click the link above</li>
                    </ul>
                </div>
                
                <p>If you're having trouble clicking the button, you can also visit the password reset page and enter this token manually:</p>
                <p style='background: #e9ecef; padding: 10px; border-radius: 5px; font-family: monospace;'>" . $token . "</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Georgia State University. All rights reserved.</p>
                <p>This is an automated message, please do not reply.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    if (sendEmail($email, $subject, $message)) {
        // Log the activity
        logActivity($conn, $user['id'], 'password_reset_requested', "Password reset email sent to: $email");
        
        $_SESSION['reset_success'] = "If an account exists with that email, a password reset link has been sent.";
    } else {
        $_SESSION['reset_error'] = "Failed to send reset email. Please try again later.";
    }
} else {
    error_log("Failed to create password reset token: " . $stmt->error);
    $_SESSION['reset_error'] = "An error occurred. Please try again later.";
}

$stmt->close();
$conn->close();

header("Location: forgot_password.php");
exit();
?>