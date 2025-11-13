<?php
/**
 * Database Configuration File
 * GSU Research Platform
 */

// Prevent direct access
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
}

// Database Configuration
define('DB_HOST', '127.0.0.1:3390');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cs_research_new');

// Application Configuration
define('APP_NAME', 'GSU Research Platform');
define('APP_URL', 'http://localhost/gsu-main');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.use_strict_mode', 1);

// Error Reporting (Turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('America/New_York');

/**
 * Get Database Connection
 * @return mysqli Database connection object
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        error_log("Database Connection Error: " . $conn->connect_error);
        die("Unable to connect to database. Please try again later.");
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Sanitize Input
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate Email
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Get User IP Address
 * @return string User's IP address
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require login - redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: admin.php");
        exit();
    }
}

/**
 * Generate CSRF Token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Send Email (using PHP mail function)
 * For production, consider using PHPMailer or similar
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email body
 * @return bool True if sent, false otherwise
 */
function sendEmail($to, $subject, $message) {
    $headers = "From: " . APP_NAME . " <noreply@gsu.edu>\r\n";
    $headers .= "Reply-To: noreply@gsu.edu\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Log Activity (for security auditing)
 * 
 * @param mysqli $conn Database connection
 * @param int $user_id User ID (null if not logged in)
 * @param string $action Action performed
 * @param string $details Additional details
 */
function logActivity($conn, $user_id, $action, $details = '') {
    $ip = getUserIP();
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) 
                           VALUES (?, ?, ?, ?, NOW())");
    
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
    }
}
?>