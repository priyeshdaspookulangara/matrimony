<?php
session_start();

// Check if user_id is set in session, if not, redirect to login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=login_required");
    exit();
}

// Basic User-Agent check for session hijacking
// Ensure HTTP_USER_AGENT is set before accessing it
$current_user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 50);
if (isset($_SESSION['user_agent_snapshot']) && $_SESSION['user_agent_snapshot'] !== $current_user_agent) {
    // Unset all session variables
    $_SESSION = array();
    // Destroy the session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: login.php?message=session_hijacked");
    exit();
}

// If the snapshot is not set, set it now (first page load after login)
if (!isset($_SESSION['user_agent_snapshot'])) {
    $_SESSION['user_agent_snapshot'] = $current_user_agent;
}


// Include header and footer
// Note: header.php and footer.php will be created/updated later
// For now, this structure assumes they exist or will exist.
// If header.php also calls session_start(), ensure it's handled correctly (e.g., only call once).
// For this task, session_start() is in each main file.

$page_title = "Dashboard"; // Will be used by header.php
// For now, we'll put minimal HTML here, and later integrate with header/footer.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Matrimony Site'); ?> - Matrimony Site</title>
    <link rel="stylesheet" href="css/style.css">
     <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; background-color: #f4f4f4; color: #333; }
        header { background: #333; color: #fff; padding: 1rem 0; text-align: center; }
        header h1 { margin: 0; }
        nav ul { padding: 0; list-style: none; text-align: center; }
        nav ul li { display: inline; margin-right: 20px; }
        nav a { color: #fff; text-decoration: none; }
        .container { width: 80%; margin: auto; overflow: hidden; padding: 20px; background: #fff; min-height: 70vh;}
        footer { text-align: center; padding: 20px; background: #333; color: #fff; margin-top: 20px;}
    </style>
</head>
<body>

    <header>
        <h1>Matrimony Site</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="search.php">Search Profiles</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>User Dashboard</h2>
        <p>Welcome, <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User'; ?>!</p>
        <p>This is your dashboard. From here you can manage your profile, search for partners, and more.</p>
        <ul>
            <li><a href="profile.php">Manage Your Profile</a></li>
            <li><a href="manage_preferences.php">Manage Partner Preferences</a></li>
            <li><a href="search.php">Search for Profiles</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Matrimony Site. All rights reserved.</p>
    </footer>

</body>
</html>
