<?php
session_start();
require_once 'config.php';

// Verify this is a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin.php");
    exit();
}

// CSRF Protection
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['signup_error'] = "Invalid request. Please try again.";
    header("Location: admin.php");
    exit();
}

// Get database connection
$conn = getDBConnection();

// Get and sanitize inputs
$full_name = sanitizeInput($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? '';

// Validation array to store errors
$errors = [];

// Validate full name
if (empty($full_name)) {
    $errors[] = "Full name is required.";
} elseif (strlen($full_name) < 2) {
    $errors[] = "Full name must be at least 2 characters.";
}

// Validate email
if (empty($email)) {
    $errors[] = "Email is required.";
} elseif (!validateEmail($email)) {
    $errors[] = "Invalid email format.";
}

// Validate password
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

// Validate password confirmation
if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

// Validate role
$valid_roles = ['student', 'professor', 'admin'];
if (empty($role) || !in_array($role, $valid_roles)) {
    $errors[] = "Please select a valid role.";
}

// If there are validation errors, redirect back with error message
if (!empty($errors)) {
    $_SESSION['signup_error'] = implode(" ", $errors);
    header("Location: admin.php");
    exit();
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['signup_error'] = "This email is already registered. Please use a different email or try logging in.";
    $stmt->close();
    $conn->close();
    header("Location: admin.php");
    exit();
}
$stmt->close();

// Hash the password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Set default status to 'pending' (requires admin approval)
$status = 'pending';

// Insert new user into database
$stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role, status, created_at) 
                       VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssss", $full_name, $email, $password_hash, $role, $status);

if ($stmt->execute()) {
    $new_user_id = $conn->insert_id;
    
    // Log the registration activity
    logActivity($conn, $new_user_id, 'user_registered', "New user registered: $email with role: $role");
    
    // Send welcome email (optional)
    $email_subject = "Welcome to " . APP_NAME;
    $email_message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . APP_NAME . "</h1>
            </div>
            <div class='content'>
                <h2>Welcome, " . htmlspecialchars($full_name) . "!</h2>
                <p>Thank you for registering with the GSU Research Platform.</p>
                <p><strong>Your account is currently pending approval.</strong></p>
                <p>An administrator will review your registration and activate your account shortly. You will receive another email once your account has been approved.</p>
                <p>If you have any questions, please contact support.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Georgia State University. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    sendEmail($email, $email_subject, $email_message);
    
    // Notify admins about new registration (optional)
    notifyAdminsNewRegistration($conn, $full_name, $email, $role);
    
    // Success message
    $_SESSION['success_message'] = "Registration successful! Your account is pending approval. You will receive an email once approved.";
    
    $stmt->close();
    $conn->close();
    
    // Redirect back to login page
    header("Location: admin.php");
    exit();
    
} else {
    // Registration failed
    error_log("Registration Error: " . $stmt->error);
    $_SESSION['signup_error'] = "Registration failed. Please try again later.";
    
    $stmt->close();
    $conn->close();
    
    header("Location: admin.php");
    exit();
}

/**
 * Notify administrators about new user registration
 */
function notifyAdminsNewRegistration($conn, $full_name, $email, $role) {
    // Get all super admins and admins
    $stmt = $conn->prepare("SELECT email FROM users WHERE role IN ('super_admin', 'admin') AND status = 'active'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($admin = $result->fetch_assoc()) {
        $admin_email = $admin['email'];
        
        $subject = "New User Registration - " . APP_NAME;
        $message = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>New User Registration</h2>
            <p>A new user has registered and is awaiting approval:</p>
            <ul>
                <li><strong>Name:</strong> " . htmlspecialchars($full_name) . "</li>
                <li><strong>Email:</strong> " . htmlspecialchars($email) . "</li>
                <li><strong>Role:</strong> " . htmlspecialchars($role) . "</li>
            </ul>
            <p>Please log in to the admin dashboard to approve or reject this registration.</p>
            <p><a href='" . APP_URL . "/option.php'>Go to Admin Dashboard</a></p>
        </body>
        </html>
        ";
        
        sendEmail($admin_email, $subject, $message);
    }
    
    $stmt->close();
}
?>