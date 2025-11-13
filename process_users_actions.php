<?php
session_start();
require_once 'config.php';

// Require login
requireLogin();

// Check if user has admin privileges
if (!isset($_SESSION['admin_role']) || !in_array($_SESSION['admin_role'], ['admin', 'super_admin'])) {
    $_SESSION['user_management_error'] = "You don't have permission to perform this action.";
    header("Location: manage_users.php");
    exit();
}

// Verify this is a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: manage_users.php");
    exit();
}

// CSRF Protection
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['user_management_error'] = "Invalid request. Please try again.";
    header("Location: manage_users.php");
    exit();
}

// Get form inputs
$user_id = intval($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

// Validate inputs
if ($user_id <= 0 || empty($action)) {
    $_SESSION['user_management_error'] = "Invalid request parameters.";
    header("Location: manage_users.php");
    exit();
}

// Get database connection
$conn = getDBConnection();

// Get user details
$stmt = $conn->prepare("SELECT id, full_name, email, role, status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['user_management_error'] = "User not found.";
    $stmt->close();
    $conn->close();
    header("Location: manage_users.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Prevent super admins from being modified by regular admins
if ($user['role'] === 'super_admin' && $_SESSION['admin_role'] !== 'super_admin') {
    $_SESSION['user_management_error'] = "You cannot modify a super admin account.";
    $conn->close();
    header("Location: manage_users.php");
    exit();
}

// Prevent self-modification
if ($user_id === $_SESSION['admin_id']) {
    $_SESSION['user_management_error'] = "You cannot modify your own account status.";
    $conn->close();
    header("Location: manage_users.php");
    exit();
}

// Process the action
switch ($action) {
    case 'approve':
        if ($user['status'] !== 'pending') {
            $_SESSION['user_management_error'] = "This user is not pending approval.";
            break;
        }
        
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            // Log the activity
            logActivity($conn, $_SESSION['admin_id'], 'user_approved', "Approved user: {$user['email']} (ID: {$user_id})");
            
            // Send approval email
            sendApprovalEmail($user);
            
            $_SESSION['user_management_success'] = "User '{$user['full_name']}' has been approved successfully.";
        } else {
            $_SESSION['user_management_error'] = "Failed to approve user. Please try again.";
        }
        $stmt->close();
        break;
        
    case 'reject':
        if ($user['status'] !== 'pending') {
            $_SESSION['user_management_error'] = "This user is not pending approval.";
            break;
        }
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            // Log the activity
            logActivity($conn, $_SESSION['admin_id'], 'user_rejected', "Rejected user: {$user['email']} (ID: {$user_id})");
            
            // Send rejection email
            sendRejectionEmail($user);
            
            $_SESSION['user_management_success'] = "User '{$user['full_name']}' has been rejected and removed.";
        } else {
            $_SESSION['user_management_error'] = "Failed to reject user. Please try again.";
        }
        $stmt->close();
        break;
        
    case 'suspend':
        if ($user['status'] !== 'active') {
            $_SESSION['user_management_error'] = "Only active users can be suspended.";
            break;
        }
        
        $stmt = $conn->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            // Log the activity
            logActivity($conn, $_SESSION['admin_id'], 'user_suspended', "Suspended user: {$user['email']} (ID: {$user_id})");
            
            // Send suspension email
            sendSuspensionEmail($user);
            
            $_SESSION['user_management_success'] = "User '{$user['full_name']}' has been suspended.";
        } else {
            $_SESSION['user_management_error'] = "Failed to suspend user. Please try again.";
        }
        $stmt->close();
        break;
        
    case 'activate':
        if ($user['status'] !== 'suspended') {
            $_SESSION['user_management_error'] = "Only suspended users can be activated.";
            break;
        }
        
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            // Log the activity
            logActivity($conn, $_SESSION['admin_id'], 'user_activated', "Activated user: {$user['email']} (ID: {$user_id})");
            
            // Send activation email
            sendActivationEmail($user);
            
            $_SESSION['user_management_success'] = "User '{$user['full_name']}' has been activated.";
        } else {
            $_SESSION['user_management_error'] = "Failed to activate user. Please try again.";
        }
        $stmt->close();
        break;
        
    default:
        $_SESSION['user_management_error'] = "Invalid action specified.";
}

$conn->close();
header("Location: manage_users.php");
exit();

// ---
// ## Email Helper Functions
// ---

/**
 * Send approval email to user
 */
function sendApprovalEmail($user) {
    global $APP_NAME, $APP_URL; // Assuming these are defined in config.php and made global if needed
    
    $subject = "Account Approved - " . APP_NAME;
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { padding: 30px; background: #f9f9f9; }
            .success { background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 15px; margin: 20px 0; color: #155724; }
            .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . APP_NAME . "</h1>
            </div>
            <div class='content'>
                <h2>🎉 Your Account Has Been Approved!</h2>
                <p>Hi " . htmlspecialchars($user['full_name']) . ",</p>
                
                <div class='success'>
                    <strong>Great news!</strong> Your account has been approved by our administrators.
                </div>
                
                <p>You can now access all features of the GSU Research Platform.</p>
                
                <div style='text-align: center;'>
                    <a href='" . APP_URL . "/admin.php' class='button'>Login to Your Account</a>
                </div>
                
                <p>If you have any questions, please don't hesitate to contact support.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Georgia State University. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    sendEmail($user['email'], $subject, $message);
}

/**
 * Send rejection email to user
 */
function sendRejectionEmail($user) {
    global $APP_NAME; // Assuming this is defined in config.php and made global if needed
    
    $subject = "Registration Update - " . APP_NAME;
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #6c757d; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { padding: 30px; background: #f9f9f9; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .error { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 15px; margin: 20px 0; color: #721c24; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . APP_NAME . "</h1>
            </div>
            <div class='content'>
                <h2>Registration Status Update</h2>
                <p>Hi " . htmlspecialchars($user['full_name']) . ",</p>
                
                <p>Thank you for your interest in the GSU Research Platform.</p>
                
                <div class='error'>
                    <strong>Important:</strong> After careful review, we are unable to approve your registration at this time. This decision is based on our current eligibility requirements.
                </div>
                
                <p>If you believe this is an error or have questions, please contact support at support@gsu.edu.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Georgia State University. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    sendEmail($user['email'], $subject, $message);
} // <--- Missing closing brace added here

/**
 * Send suspension email to user
 */
function sendSuspensionEmail($user) {
    global $APP_NAME;
    
    $subject = "Account Suspension - " . APP_NAME;
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { padding: 30px; background: #f9f9f9; }
            .warning { background: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px; padding: 15px; margin: 20px 0; color: #856404; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Account Suspended</h1>
            </div>
            <div class='content'>
                <h2>⚠️ Important Account Notice</h2>
                <p>Hi " . htmlspecialchars($user['full_name']) . ",</p>
                
                <div class='warning'>
                    Your account on the GSU Research Platform has been **suspended**.
                </div>
                
                <p>This action was taken due to a violation of our terms of service or a policy issue.</p>
                
                <p>Your access to the platform is currently restricted.</p>
                
                <p>Please contact our support team at support@gsu.edu for more information or to appeal this decision.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Georgia State University. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    sendEmail($user['email'], $subject, $message);
}

/**
 * Send activation email to user
 */
function sendActivationEmail($user) {
    global $APP_NAME, $APP_URL;
    
    $subject = "Account Reactivated - " . APP_NAME;
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { padding: 30px; background: #f9f9f9; }
            .info { background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 8px; padding: 15px; margin: 20px 0; color: #0c5460; }
            .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . APP_NAME . "</h1>
            </div>
            <div class='content'>
                <h2>✅ Account Reactivation Complete!</h2>
                <p>Hi " . htmlspecialchars($user['full_name']) . ",</p>
                
                <div class='info'>
                    Good news! Your account on the GSU Research Platform has been **reactivated**.
                </div>
                
                <p>You can now log in and continue to access all platform features.</p>
                
                <div style='text-align: center;'>
                    <a href='" . APP_URL . "/admin.php' class='button'>Login to Your Account</a>
                </div>
                
                <p>If you have any questions, please don't hesitate to contact support.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Georgia State University. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    sendEmail($user['email'], $subject, $message);
}
// Final closing PHP tag is optional in PHP files, but included here for completeness
// ?>