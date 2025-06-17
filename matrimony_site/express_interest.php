<?php
session_start();
@include_once 'includes/db_connect.php'; // Conceptual, for future DB interaction
@include_once 'includes/functions.php';   // For any utility functions if needed

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page.
    // Pass the intended action (target_id) so they can be redirected back after login (optional enhancement)
    $redirect_url = "login.php?message=login_required";
    if(isset($_GET['target_id'])) {
        // $redirect_url .= "&return_to=view_profile.php?id=" . urlencode($_GET['target_id']);
    }
    header("Location: " . $redirect_url);
    exit();
}

$from_user_id = $_SESSION['user_id'];
$target_id = null;

// --- Get Target User ID ---
if (isset($_GET['target_id'])) {
    if (filter_var($_GET['target_id'], FILTER_VALIDATE_INT) && $_GET['target_id'] > 0) {
        $target_id = (int)$_GET['target_id'];
    } else {
        // Invalid ID format
        header("Location: dashboard.php?message=invalid_target_profile");
        exit();
    }
} else {
    // No ID provided
    header("Location: dashboard.php?message=no_target_id_provided");
    exit();
}

// --- Prevent Self-Interest ---
if ($from_user_id == $target_id) {
    header("Location: view_profile.php?id=" . urlencode($target_id) . "&message=cannot_interest_self");
    exit();
}

// --- Simulate Checking for Existing Interest and Storing New Interest ---

// Initialize $_SESSION['expressed_interests'] as an array if it doesn't exist.
// This array will store keys like "fromUserID_toUserID".
if (!isset($_SESSION['expressed_interests'])) {
    $_SESSION['expressed_interests'] = [];
}

$interest_key = $from_user_id . "_" . $target_id;

// Check for Duplicate Interest
if (isset($_SESSION['expressed_interests'][$interest_key])) {
    // Interest already expressed
    header("Location: view_profile.php?id=" . urlencode($target_id) . "&message=interest_already_expressed");
    exit();
}

// Store New Interest (Simulated)
// In a real application, this would be an INSERT into a database table:
// $query = "INSERT INTO interests (from_user_id, to_user_id, interest_date) VALUES ($from_user_id, $target_id, NOW())";
// For simulation, we use the session:
$_SESSION['expressed_interests'][$interest_key] = time(); // Store current timestamp as value

// Conceptual success - redirect with success message
header("Location: view_profile.php?id=" . urlencode($target_id) . "&message=interest_expressed_successfully");
exit();

// Fallback redirect in case of unforeseen issues (though logic above should always redirect)
header("Location: dashboard.php?message=interest_general_error");
exit();
?>
