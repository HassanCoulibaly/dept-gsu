<?php
session_start();

// Database configuration
$servername = "127.0.0.1:3390";
$username = "root";
$password = "";
$database = "cs_research_new";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    error_log("Database Connection failed: " . $conn->connect_error);
    $_SESSION['login_error'] = "A server error occurred. Please try again later.";
    header("Location: admin.php");
    exit();
}

// Verify this is a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin.php");
    exit();
}

// CSRF Protection
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['login_error'] = "Invalid request. Please try again.";
    header("Location: admin.php");
    exit();
}

// Get user IP address for rate limiting
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$user_ip = getUserIP();
$email = trim($_POST['email']);
$password_input = $_POST['password'];
$remember_me = isset($_POST['remember_me']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_error'] = "Invalid email format.";
    header("Location: admin.php");
    exit();
}

// Rate Limiting: Check recent failed attempts from this IP
$time_threshold = date('Y-m-d H:i:s', strtotime('-15 minutes'));
$stmt = $conn->prepare("SELECT COUNT(*) as attempt_count FROM login_attempts 
                        WHERE ip_address = ? AND attempted_at > ? AND successful = FALSE");
$stmt->bind_param("ss", $user_ip, $time_threshold);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

// Block if too many failed attempts (5 in 15 minutes)
if ($row['attempt_count'] >= 5) {
    $_SESSION['login_error'] = "Too many failed login attempts. Please try again in 15 minutes.";
    header("Location: admin.php");
    exit();
}

// Query user from database
$stmt = $conn->prepare("SELECT id, email, password_hash, role, status, full_name FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Check if account is active
    if ($user['status'] !== 'active') {
        $status_message = ($user['status'] === 'pending') 
            ? "Your account is pending approval. Please wait for admin confirmation."
            : "Your account has been suspended. Please contact support.";
        
        $_SESSION['login_error'] = $status_message;
        
        // Log failed attempt
        logLoginAttempt($conn, $email, $user_ip, false);
        
        header("Location: admin.php");
        exit();
    }
    
    // Verify password using password_verify (for hashed passwords)
    if (password_verify($password_input, $user['password_hash'])) {
        // Successful login
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_name'] = $user['full_name'];
        
        // Update last login time
        $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $update_stmt->bind_param("i", $user['id']);
        $update_stmt->execute();
        $update_stmt->close();
        
        // Log successful attempt
        logLoginAttempt($conn, $email, $user_ip, true);
        
        // Handle "Remember Me" functionality
        if ($remember_me) {
            // Create a secure token
            $token = bin2hex(random_bytes(32));
            $token_hash = password_hash($token, PASSWORD_DEFAULT);
            
            // Store token in database (you'll need to create a remember_tokens table)
            // For now, we'll use a secure cookie with session ID
            setcookie('remember_token', $token, time() + (86400 * 30), "/", "", true, true); // 30 days
        }
        
        // Clear any old CSRF token and generate new one for next use
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Redirect to dashboard
        header("Location: option.php");
        exit();
        
    } else {
        // Invalid password
        $_SESSION['login_error'] = "Invalid email or password.";
        logLoginAttempt($conn, $email, $user_ip, false);
        header("Location: admin.php");
        exit();
    }
} else {
    // No user found - use same message as wrong password (security best practice)
    $_SESSION['login_error'] = "Invalid email or password.";
    logLoginAttempt($conn, $email, $user_ip, false);
    header("Location: admin.php");
    exit();
}

$stmt->close();
$conn->close();

// Function to log login attempts
function logLoginAttempt($conn, $email, $ip_address, $successful) {
    $stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, successful, attempted_at) 
                           VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("ssi", $email, $ip_address, $successful);
    $stmt->execute();
    $stmt->close();
}
?>