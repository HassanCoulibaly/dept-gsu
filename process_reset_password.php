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

// Get form inputs
$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate inputs
$errors = [];

if (empty($token)) {
    $errors[] = "Invalid reset token.";
}

if (empty($password)) {
    $errors[] = "Password is required.";
} elseif (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters long.";
} elseif (!preg_match("/[A-Z]/", $password)) {
    $errors[] = "Password must contain at least one uppercase letter.";
} elseif (!preg_match("/[a-z]/", $password)) {
    $errors[] = "Password must contain at least one lowercase letter.";
} elseif (!preg_match("/[0-9]/", $password)) {
    $errors[] = "Password must contain at least one number.";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

// If validation fails, redirect back with errors
if (!empty($errors)) {
    $_SESSION['reset_error'] = implode(" ", $errors);
    header("Location: reset_password.php?token=" . urlencode($token));
    exit();
}

// Get database connection
$conn = getDBConnection();
$token_hash = hash('sha256', $token);

// Verify token and get user
$stmt = $conn->prepare("SELECT pr.id, pr.user_id, pr.expires_at, u.email, u.full_name 
                       FROM password_resets pr 
                       JOIN users u ON pr.user_id = u.id 
                       WHERE pr.token = ? AND pr.used = FALSE");
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['reset_error'] = "Invalid or expired reset token.";
    $stmt->close();
    $conn->close();
    header("Location: forgot_password.php");
    exit();
}

$reset_data = $result->fetch_assoc();
$stmt->close();

// Check if token has expired
if (strtotime($reset_data['expires_at']) < time()) {
    $_SESSION['reset_error'] = "This reset link has expired. Please request a new one.";
    $conn->close();
    header("Location: forgot_password.php");
    exit();
}

// Hash the new password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Update user's password
$stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
$stmt->bind_param("si", $password_hash, $reset_data['user_id']);

if ($stmt->execute()) {
    // Mark token as used
    $mark_used = $conn->prepare("UPDATE password_resets SET used = TRUE WHERE id = ?");
    $mark_used->bind_param("i", $reset_data['id']);
    $mark_used->execute();
    $mark_used->close();
    
    // Log the activity
    logActivity($conn, $reset_data['user_id'], 'password_reset_completed', "Password was reset for: " . $reset_data['email']);
    
    // Send confirmation email
    $subject = "Password Changed Successfully - " . APP_NAME;
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { padding: 30px; background: #f9f9f9; }
            .success { background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 15px; margin: 20px 0; color: #155724; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . APP_NAME . "</h1>
            </div>
            <div class='content'>
                <h2>Password Changed Successfully</h2>
                <p>Hi " . htmlspecialchars($reset_data['full_name']) . ",</p>
                
                <div class='success'>
                    <strong>✓ Your password has been changed successfully!</strong>
                </div>
                
                <p>You can now log in to your account using your new password.</p>
                
                <p><a href='" . APP_URL . "/admin.php' style='display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>Go to Login Page</a></p>
                
                <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>
                    <strong>Security Note:</strong> If you did not make this change, please contact support immediately.
                </p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Georgia State University. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    sendEmail($reset_data['email'], $subject, $message);
    
    // Success - redirect to login with success message
    $_SESSION['success_message'] = "Password reset successful! You can now log in with your new password.";
    $stmt->close();
    $conn->close();
    
    header("Location: admin.php");
    exit();
    
} else {
    error_log("Failed to update password: " . $stmt->error);
    $_SESSION['reset_error'] = "An error occurred while resetting your password. Please try again.";
    
    $stmt->close();
    $conn->close();
    
    header("Location: reset_password.php?token=" . urlencode($token));
    exit();
}
?>