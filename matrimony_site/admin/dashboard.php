<?php
session_start();

// Authentication Check: If admin_id is not set, redirect to admin login page
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php?error=not_logged_in");
    exit();
}

$page_title = "Admin Dashboard";
include_once 'admin_header.php'; // Include the new admin header
?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>
<p class="welcome-message" style="font-size: 18px; margin-bottom: 20px;">
    Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!
</p>

<p>This is the main dashboard for the site administration. From here, you can manage users, view site statistics (conceptual), and perform other administrative tasks.</p>

<h3>Quick Links:</h3>
<ul style="list-style: none; padding: 0;">
    <li style="margin-bottom: 10px;"><a href="manage_users.php" style="text-decoration: none; color: #3498db; font-size: 18px;">Manage Users</a></li>
    <li style="margin-bottom: 10px;"><a href="#" style="text-decoration: none; color: #3498db; font-size: 18px;">View Site Statistics (Conceptual)</a></li>
    <li style="margin-bottom: 10px;"><a href="#" style="text-decoration: none; color: #3498db; font-size: 18px;">Settings (Conceptual)</a></li>
</ul>

<?php
include_once 'admin_footer.php'; // Include the new admin footer
?>
