<?php
session_start();
require_once 'config.php';

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['reset_error'] = "Invalid or missing reset token.";
    header("Location: forgot_password.php");
    exit();
}

// Verify token exists and hasn't expired
$conn = getDBConnection();
$token_hash = hash('sha256', $token);

$stmt = $conn->prepare("SELECT pr.id, pr.user_id, pr.expires_at, u.email, u.full_name 
                       FROM password_resets pr 
                       JOIN users u ON pr.user_id = u.id 
                       WHERE pr.token = ? AND pr.used = FALSE");
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['reset_error'] = "Invalid or expired reset token. Please request a new one.";
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

$conn->close();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password - GSU Research Platform</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="form-container">
    <form action="process_reset_password.php" method="post" id="resetForm">
        <h2>Create New Password</h2>
        
        <p style="text-align: center; color: #666; margin-bottom: 20px;">
            Enter your new password below.
        </p>

        <?php
        // Display error messages
        if (isset($_SESSION['reset_error'])) {
            echo '<div class="error-message">' . htmlspecialchars($_SESSION['reset_error']) . '</div>';
            unset($_SESSION['reset_error']);
        }
        ?>

        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <label for="password">New Password:</label>
        <div class="password-container">
            <input type="password" id="password" name="password" required minlength="8">
            <span class="toggle-password" onclick="togglePassword('password')">👁️</span>
        </div>
        <div id="password-strength" class="password-strength"></div>

        <label for="confirm_password">Confirm New Password:</label>
        <div class="password-container">
            <input type="password" id="confirm_password" name="confirm_password" required>
            <span class="toggle-password" onclick="togglePassword('confirm_password')">👁️</span>
        </div>

        <div class="form-note">
            <small>
                <strong>Password Requirements:</strong>
                <ul style="margin: 5px 0 0 20px; padding: 0;">
                    <li>At least 8 characters long</li>
                    <li>Contains uppercase and lowercase letters</li>
                    <li>Contains at least one number</li>
                    <li>Contains at least one special character (recommended)</li>
                </ul>
            </small>
        </div>

        <input type="submit" value="Reset Password">

        <div style="text-align: center; margin-top: 20px;">
            <a href="admin.php" style="color: #007bff; text-decoration: none; font-weight: 600;">
                ← Back to Login
            </a>
        </div>
    </form>
</div>

<script>
// Toggle password visibility
function togglePassword(fieldId) {
    var field = document.getElementById(fieldId);
    var type = field.getAttribute('type') === 'password' ? 'text' : 'password';
    field.setAttribute('type', type);
}

// Password strength checker
document.addEventListener('DOMContentLoaded', function() {
    var passwordField = document.getElementById('password');
    var strengthDiv = document.getElementById('password-strength');
    
    passwordField.addEventListener('input', function() {
        var password = this.value;
        var strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z\d]/.test(password)) strength++;
        
        if (password.length === 0) {
            strengthDiv.innerHTML = '';
            strengthDiv.className = 'password-strength';
        } else if (strength <= 2) {
            strengthDiv.innerHTML = '<span class="weak">Weak password</span>';
            strengthDiv.className = 'password-strength weak';
        } else if (strength <= 3) {
            strengthDiv.innerHTML = '<span class="medium">Medium password</span>';
            strengthDiv.className = 'password-strength medium';
        } else {
            strengthDiv.innerHTML = '<span class="strong">Strong password</span>';
            strengthDiv.className = 'password-strength strong';
        }
    });
    
    // Validate passwords match on form submit
    document.getElementById('resetForm').addEventListener('submit', function(e) {
        var password = document.getElementById('password').value;
        var confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
    });
});
</script>

</body>
</html>