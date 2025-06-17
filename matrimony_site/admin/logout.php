<?php
session_start();

// Unset all admin-specific session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_logged_in_timestamp']);
// If you had other admin specific session variables, unset them too.
// e.g., unset($_SESSION['admin_role']);

// To completely clear the session, which might be desirable if admin and user
// sessions should never overlap and to ensure all traces are gone:
// 1. Set $_SESSION to an empty array
$_SESSION = array();

// 2. If using session cookies, delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finally, destroy the session.
session_destroy();

// Redirect to the admin login page with a success message
header("Location: index.php?message=logged_out");
exit();
?>
