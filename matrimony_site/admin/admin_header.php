<?php
// This check should be at the top of actual pages like dashboard.php, manage_users.php
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: index.php?error=not_logged_in");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Admin Panel'; ?> - Matrimony Site</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f7f6; color: #333; }
        .admin-header-bar { background-color: #2c3e50; color: #ecf0f1; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .admin-header-bar h1 { margin: 0; font-size: 24px; }
        .admin-header-bar nav a { color: #ecf0f1; text-decoration: none; margin-left: 20px; font-size: 16px; }
        .admin-header-bar nav a:hover { text-decoration: underline; }
        .admin-main-container { padding: 20px; }
        .admin-main-container h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #34495e; color: #ecf0f1; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        .action-links a { margin-right: 10px; text-decoration: none; }
        .action-links a.approve { color: green; }
        .action-links a.reject { color: red; }
        .action-links a.premium { color: blue; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 14px; text-align: center; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .message.info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <header class="admin-header-bar">
        <h1>Admin Panel</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_users.php">Manage Users</a>
            <?php if (isset($_SESSION['admin_username'])): ?>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['admin_username']); ?>)</a>
            <?php else: ?>
                <a href="index.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="admin-main-container">
        <!-- Page specific content starts here -->
