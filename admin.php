
<?php
// Start the session to manage user login status and messages.
session_start();

// Check if the admin is already logged in.
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: option.php");
    exit();
}

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Portal - GSU Research Platform</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="form-container">
    <!-- Tab Navigation -->
    <div class="tab-container">
        <button class="tab-button active" onclick="openTab(event, 'signin')">Sign In</button>
        <button class="tab-button" onclick="openTab(event, 'signup')">Sign Up</button>
    </div>

    <!-- Sign In Form -->
    <div id="signin" class="tab-content active">
        <form action="submit_login.php" method="post">
            <h2>Admin Sign In</h2>

            <?php
            // Display error messages
            if (isset($_SESSION['login_error'])) {
                echo '<div class="error-message">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
                unset($_SESSION['login_error']);
            }
            
            // Display success messages
            if (isset($_SESSION['success_message'])) {
                echo '<div class="success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                unset($_SESSION['success_message']);
            }
            ?>

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label for="signin-email">Email:</label>
            <input type="email" id="signin-email" name="email" required>

            <label for="signin-password">Password:</label>
            <div class="password-container">
                <input type="password" id="signin-password" name="password" required>
                <span class="toggle-password" onclick="togglePassword('signin-password')">👁️</span>
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember_me"> Remember Me
                </label>
                <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
            </div>

            <input type="submit" value="Sign In">

            <!-- Social Login Options -->
            <div class="social-login">
                <p class="divider"><span>OR</span></p>
                <a href="google_oauth.php" class="google-btn">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google logo">
                    Continue with Google
                </a>
                <a href="github_oauth.php" class="github-btn">
                    <svg height="20" width="20" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/>
                    </svg>
                    Continue with GitHub
                </a>
            </div>
        </form>
    </div>

    <!-- Sign Up Form -->
    <div id="signup" class="tab-content">
        <form action="submit_signup.php" method="post">
            <h2>Admin Sign Up</h2>

            <?php
            // Display signup error messages
            if (isset($_SESSION['signup_error'])) {
                echo '<div class="error-message">' . htmlspecialchars($_SESSION['signup_error']) . '</div>';
                unset($_SESSION['signup_error']);
            }
            ?>

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label for="signup-name">Full Name:</label>
            <input type="text" id="signup-name" name="full_name" required>

            <label for="signup-email">Email:</label>
            <input type="email" id="signup-email" name="email" required>

            <label for="signup-password">Password:</label>
            <div class="password-container">
                <input type="password" id="signup-password" name="password" required minlength="8">
                <span class="toggle-password" onclick="togglePassword('signup-password')">👁️</span>
            </div>
            <div id="password-strength" class="password-strength"></div>

            <label for="signup-confirm-password">Confirm Password:</label>
            <div class="password-container">
                <input type="password" id="signup-confirm-password" name="confirm_password" required>
                <span class="toggle-password" onclick="togglePassword('signup-confirm-password')">👁️</span>
            </div>

            <label for="signup-role">Role:</label>
            <select id="signup-role" name="role" required>
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="professor">Professor</option>
                <option value="admin">Admin</option>
            </select>

            <div class="form-note">
                <small>⚠️ Your account will require approval from a super admin before you can access the system.</small>
            </div>

            <input type="submit" value="Sign Up">
        </form>
    </div>
</div>

<script>
// Tab switching functionality
function openTab(evt, tabName) {
    // Hide all tab contents
    var tabContents = document.getElementsByClassName("tab-content");
    for (var i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove("active");
    }
    
    // Remove active class from all tab buttons
    var tabButtons = document.getElementsByClassName("tab-button");
    for (var i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove("active");
    }
    
    // Show the current tab and mark button as active
    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
}

// Toggle password visibility
function togglePassword(fieldId) {
    var field = document.getElementById(fieldId);
    var type = field.getAttribute('type') === 'password' ? 'text' : 'password';
    field.setAttribute('type', type);
}

// Password strength checker
document.addEventListener('DOMContentLoaded', function() {
    var passwordField = document.getElementById('signup-password');
    var strengthDiv = document.getElementById('password-strength');
    
    if (passwordField) {
        passwordField.addEventListener('input', function() {
            var password = this.value;
            var strength = 0;
            var feedback = '';
            
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
    }
});
</script>

</body>
</html>