<?php
session_start();
require_once 'config.php';

// Log the logout activity if user is logged in
if (isLoggedIn()) {
    $conn = getDBConnection();
    $user_id = $_SESSION['admin_id'] ?? null;
    $user_email = $_SESSION['admin_email'] ?? 'Unknown';
    
    // Log the logout activity
    logActivity($conn, $user_id, 'user_logout', "User logged out: $user_email");
    
    $conn->close();
}

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Delete remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Destroy the session
session_destroy();

// Start a new session for the success message
session_start();
$_SESSION['success_message'] = "You have been successfully logged out.";

// Redirect to login page
header("Location: admin.php");
exit();
?>