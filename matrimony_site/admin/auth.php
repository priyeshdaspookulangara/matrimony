<?php
session_start();

// Define hardcoded admin credentials
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'password123'); // In a real app, this should be a hashed password

// For consistency, include paths are relative to this file's location (admin folder)
// These files might not be strictly needed for this simple auth, but good for structure
@include_once '../includes/db_connect.php'; // Not used for DB here, but for consistency
@include_once '../includes/functions.php';   // For any utility functions if needed

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submitted_username = $_POST['username'] ?? '';
    $submitted_password = $_POST['password'] ?? '';

    // --- Authentication Check ---
    // IMPORTANT: For a real application:
    // 1. The stored admin password should be hashed using password_hash().
    // 2. The comparison should be done using password_verify($submitted_password, HASHED_ADMIN_PASSWORD).
    // For this simulation, we are doing a direct string comparison.
    if ($submitted_username === ADMIN_USERNAME && $submitted_password === ADMIN_PASSWORD) {
        // Authentication successful

        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        // Set admin-specific session variables
        $_SESSION['admin_id'] = 1; // A placeholder admin ID
        $_SESSION['admin_username'] = $submitted_username;
        $_SESSION['admin_logged_in_timestamp'] = time();
        // You could also set a role, e.g., $_SESSION['admin_role'] = 'administrator';

        // Redirect to the admin dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        // Authentication failed
        // Redirect back to the login page with an error message
        header("Location: index.php?error=invalid_credentials");
        exit();
    }
} else {
    // If not a POST request, redirect to login page (or show an error)
    // This prevents direct access to auth.php via GET
    header("Location: index.php");
    exit();
}
?>
