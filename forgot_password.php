<?php
session_start();

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password - GSU Research Platform</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="form-container">
    <form action="process_forgot_password.php" method="post">
        <h2>Reset Password</h2>
        
        <p style="text-align: center; color: #666; margin-bottom: 20px;">
            Enter your email address and we'll send you a link to reset your password.
        </p>

        <?php
        // Display error messages
        if (isset($_SESSION['reset_error'])) {
            echo '<div class="error-message">' . htmlspecialchars($_SESSION['reset_error']) . '</div>';
            unset($_SESSION['reset_error']);
        }
        
        // Display success messages
        if (isset($_SESSION['reset_success'])) {
            echo '<div class="success-message">' . htmlspecialchars($_SESSION['reset_success']) . '</div>';
            unset($_SESSION['reset_success']);
        }
        ?>

        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required placeholder="Enter your email">

        <input type="submit" value="Send Reset Link">

        <div style="text-align: center; margin-top: 20px;">
            <a href="admin.php" style="color: #007bff; text-decoration: none; font-weight: 600;">
                ← Back to Login
            </a>
        </div>
    </form>
</div>

</body>
</html>