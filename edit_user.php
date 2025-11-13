<?php
session_start();
require_once 'config.php';

// Require login and admin privileges
requireLogin();
if (!in_array($_SESSION['admin_role'], ['admin', 'super_admin'])) {
    header("Location: option.php");
    exit();
}

$user_id = intval($_GET['id'] ?? 0);
$conn = getDBConnection();

// Handle form submission to UPDATE the user's role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $new_role = $_POST['role'];
        $valid_roles = ['student', 'professor', 'admin']; // Define roles an admin can assign

        // Super admin can assign 'super_admin' role
        if ($_SESSION['admin_role'] === 'super_admin') {
            $valid_roles[] = 'super_admin';
        }

        if (in_array($new_role, $valid_roles)) {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $new_role, $user_id);
            if ($stmt->execute()) {
                $_SESSION['user_management_success'] = "User role updated successfully.";
            } else {
                $_SESSION['user_management_error'] = "Failed to update role.";
            }
            $stmt->close();
            header("Location: manage_users.php");
            exit();
        }
    }
}


// Fetch user data to display in the form
$stmt = $conn->prepare("SELECT full_name, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="admin.css"> </head>
<body>
<div class="form-container">
    <form action="" method="post">
        <h2>Edit User: <?php echo htmlspecialchars($user['full_name']); ?></h2>
        <p style="text-align: center; color: #666;"><?php echo htmlspecialchars($user['email']); ?></p>

        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <label for="role">Change Role:</label>
        <select id="role" name="role" required>
            <option value="student" <?php echo ($user['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
            <option value="professor" <?php echo ($user['role'] === 'professor') ? 'selected' : ''; ?>>Professor</option>
            <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
             <?php if ($_SESSION['admin_role'] === 'super_admin'): ?>
                <option value="super_admin" <?php echo ($user['role'] === 'super_admin') ? 'selected' : ''; ?>>Super Admin</option>
            <?php endif; ?>
        </select>

        <input type="submit" name="update_role" value="Update Role">
        <div style="text-align: center; margin-top: 20px;">
            <a href="manage_users.php" style="color: #007bff; text-decoration: none;">← Back to User List</a>
        </div>
    </form>
</div>
</body>
</html>