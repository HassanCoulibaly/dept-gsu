<?php
session_start();
require_once 'config.php';

// Require login
requireLogin();

// Check if user has admin privileges
if (!isset($_SESSION['admin_role']) || !in_array($_SESSION['admin_role'], ['admin', 'super_admin'])) {
    $_SESSION['login_error'] = "You don't have permission to access this page.";
    header("Location: option.php");
    exit();
}

// Get database connection
$conn = getDBConnection();

// Handle status filter
$status_filter = $_GET['status'] ?? 'all';
$role_filter = $_GET['role'] ?? 'all';
$search_query = $_GET['search'] ?? '';

// Build SQL query with filters
$sql = "SELECT id, full_name, email, role, status, created_at, last_login FROM users WHERE 1=1";
$params = [];
$types = "";

if ($status_filter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($role_filter !== 'all') {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

if (!empty($search_query)) {
    $sql .= " AND (full_name LIKE ? OR email LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_users,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended_users
    FROM users";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// Generate CSRF token
generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Management - GSU Research Platform</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 24px;
        }
        
        .header .user-info {
            float: right;
            margin-top: 5px;
        }
        
        .header .user-info a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px 16px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .header .user-info a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #667eea;
        }
        
        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #333;
        }
        
        .stat-card.pending { border-left-color: #ffc107; }
        .stat-card.active { border-left-color: #28a745; }
        .stat-card.suspended { border-left-color: #dc3545; }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filters select,
        .filters input[type="text"] {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .filters button {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .filters button:hover {
            transform: translateY(-2px);
        }
        
        .users-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge.active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge.suspended {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge.student { background: #d1ecf1; color: #0c5460; }
        .badge.professor { background: #d4edda; color: #155724; }
        .badge.admin { background: #e2e3e5; color: #383d41; }
        .badge.super_admin { background: #cce5ff; color: #004085; }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .btn-suspend {
            background: #ffc107;
            color: #333;
        }
        
        .btn-activate {
            background: #17a2b8;
            color: white;
        }
        
        .btn-edit {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.8;
            transform: translateY(-2px);
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state svg {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .header .user-info {
                float: none;
                margin-top: 15px;
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .users-table {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>👥 User Management</h1>
    <div class="user-info">
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
        <a href="option.php">← Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
    <div style="clear: both;"></div>
</div>

<div class="container">
    
    <?php
    // Display success/error messages
    if (isset($_SESSION['user_management_success'])) {
        echo '<div class="message success">' . htmlspecialchars($_SESSION['user_management_success']) . '</div>';
        unset($_SESSION['user_management_success']);
    }
    
    if (isset($_SESSION['user_management_error'])) {
        echo '<div class="message error">' . htmlspecialchars($_SESSION['user_management_error']) . '</div>';
        unset($_SESSION['user_management_error']);
    }
    ?>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Users</h3>
            <div class="number"><?php echo $stats['total_users']; ?></div>
        </div>
        <div class="stat-card pending">
            <h3>Pending Approval</h3>
            <div class="number"><?php echo $stats['pending_users']; ?></div>
        </div>
        <div class="stat-card active">
            <h3>Active Users</h3>
            <div class="number"><?php echo $stats['active_users']; ?></div>
        </div>
        <div class="stat-card suspended">
            <h3>Suspended</h3>
            <div class="number"><?php echo $stats['suspended_users']; ?></div>
        </div>
    </div>
    
    <!-- Filters -->
    <form method="GET" action="" class="filters">
        <select name="status" id="status">
            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="suspended" <?php echo $status_filter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
        </select>
        
        <select name="role" id="role">
            <option value="all" <?php echo $role_filter === 'all' ? 'selected' : ''; ?>>All Roles</option>
            <option value="student" <?php echo $role_filter === 'student' ? 'selected' : ''; ?>>Student</option>
            <option value="professor" <?php echo $role_filter === 'professor' ? 'selected' : ''; ?>>Professor</option>
            <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
            <option value="super_admin" <?php echo $role_filter === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
        </select>
        
        <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 1; min-width: 250px;">
        
        <button type="submit">🔍 Filter</button>
        <a href="manage_users.php" class="btn" style="background: #6c757d; color: white; text-decoration: none;">Clear</a>
    </form>
    
    <!-- Users Table -->
    <div class="users-table">
        <?php if ($users->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><span class="badge <?php echo $user['role']; ?>"><?php echo str_replace('_', ' ', ucwords($user['role'], '_')); ?></span></td>
                    <td><span class="badge <?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td><?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never'; ?></td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($user['status'] === 'pending'): ?>
                                <form method="POST" action="process_user_action.php" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-approve">✓ Approve</button>
                                </form>
                                <form method="POST" action="process_user_action.php" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this user?')">✕ Reject</button>
                                </form>
                            <?php elseif ($user['status'] === 'active'): ?>
                                <form method="POST" action="process_user_action.php" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="suspend">
                                    <button type="submit" class="btn btn-suspend" onclick="return confirm('Are you sure you want to suspend this user?')">⊗ Suspend</button>
                                </form>
                            <?php elseif ($user['status'] === 'suspended'): ?>
                                <form method="POST" action="process_user_action.php" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="btn btn-activate">↻ Activate</button>
                                </form>
                            <?php endif; ?>
                            
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-edit">✎ Edit</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <h3>No users found</h3>
            <p>Try adjusting your filters or search query</p>
        </div>
        <?php endif; ?>
    </div>
    
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>