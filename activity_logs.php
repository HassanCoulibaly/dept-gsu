<?php
session_start();
require_once 'config.php';

// Require login and admin privileges
requireLogin();
if (!in_array($_SESSION['admin_role'], ['admin', 'super_admin'])) {
    header("Location: option.php");
    exit();
}

$conn = getDBConnection();
// Fetch logs, joining with the users table to get the admin's name
$logs_result = $conn->query("
    SELECT logs.*, users.full_name 
    FROM activity_logs AS logs
    LEFT JOIN users ON logs.user_id = users.id
    ORDER BY logs.created_at DESC
    LIMIT 100
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Activity Logs</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .container { max-width: 1200px; margin: 20px auto; }
        .back-button { margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Admin Activity Logs</h2>
    <div class="back-button">
         <a href="option.php"><button>← Back to Dashboard</button></a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Admin User</th>
                <th>Action</th>
                <th>Details</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($logs_result->num_rows > 0): ?>
                <?php while ($log = $logs_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($log['full_name'] ?? 'System'); ?></td>
                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">No activity logs found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>