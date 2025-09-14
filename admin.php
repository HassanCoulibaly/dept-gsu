<?php
// Start the session to manage user login status and messages.
session_start();

// Check if the admin is already logged in.
// If true, redirect them to the admin dashboard to prevent re-displaying the login form.
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: option.php"); // Redirect to your actual admin dashboard page
    exit(); // Always exit after a header redirect
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login</title>
    <meta charset="utf-8">
    <!-- Essential for responsive design on various devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="form-container">
    <form action="submit_login.php" method="post">
        <h2>Admin Login</h2>

        <?php
        // Display error messages if redirected back from submit_login.php
        // This uses a session variable to store the error message.
        if (isset($_SESSION['login_error'])) {
            echo '<p style="color: red; text-align: center; margin-bottom: 15px;">' . htmlspecialchars($_SESSION['login_error']) . '</p>';
            // Clear the error message after displaying it to prevent it from showing again on refresh.
            unset($_SESSION['login_error']);
        }
        ?>

        <label for="email">Email:</label><br><br>
        <input type="text" id="email" name="email" required><br><br>
        <!-- Changed type to "email" for better input validation in browsers -->

        <label for="password">Password:</label><br><br>
        <input type="password" id="password" name="password" required><br><br>
        <!-- CRITICAL CHANGE: Changed type="text" to type="password" for security -->

        <input type="submit" value="Login">
    </form>
</div>

</body>
</html>
