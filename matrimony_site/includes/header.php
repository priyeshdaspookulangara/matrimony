<?php
// It's good practice to start the session at the very beginning of your script,
// before any output. If this header is included in all user-facing pages,
// starting it here can be a good strategy. Ensure it's not called again if
// the including page already started it.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Matrimony Site</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- You might want to add more specific styles or per-page styles here -->
    <style>
        /* Basic styles for demonstration - can be moved to style.css */
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; background-color: #f4f4f4; color: #333; }
        .container { width: 80%; margin: auto; overflow: hidden; padding: 20px; background: #fff; min-height: 70vh; /* Adjusted for footer */ }
        header.main-header { background: #333; color: #fff; padding-top: 10px; padding-bottom: 10px; }
        header.main-header h1 { margin: 0; padding-left: 20px; display: inline-block; }
        header.main-header nav { float: right; margin-top: 10px; margin-right: 20px;}
        header.main-header nav ul { padding: 0; margin: 0; list-style: none; }
        header.main-header nav ul li { display: inline; margin-left: 20px; }
        header.main-header nav a { color: #fff; text-decoration: none; text-transform: uppercase; font-size: 14px; }
        header.main-header nav a:hover { color: #ccc; }
        footer.main-footer { text-align: center; padding: 20px; background: #333; color: #fff; margin-top: 20px;}
    </style>
</head>
<body>
    <header class="main-header">
        <h1><a href="index.php" style="color: #fff; text-decoration: none;">Matrimony Site</a></h1>
        <nav>
            <ul>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="search.php">Search</a></li>
                    <li><a href="logout.php">Logout (<?php echo htmlspecialchars(substr($_SESSION['user_name'] ?? 'User', 0, 10)); ?>)</a></li>
                <?php else: ?>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="container">
        <!-- Main content of the page will go here -->
