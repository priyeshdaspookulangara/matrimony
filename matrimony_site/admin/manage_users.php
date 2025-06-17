<?php
session_start();
@include_once '../includes/db_connect.php'; // $db (conceptual)
@include_once '../includes/functions.php';   // get_all_users_simulation(), sanitize_output()

// Admin Authentication Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php?error=not_logged_in");
    exit();
}

$page_title = "Manage Users";

// Initialize simulated user data in session if not already present
if (!isset($_SESSION['simulated_users_data'])) {
    $_SESSION['simulated_users_data'] = get_all_users_simulation(null);
}
$all_users = &$_SESSION['simulated_users_data']; // Use a reference to modify session data directly

$action_message = '';
$action_message_type = ''; // success, error, info

// Handle Actions (Approve/Reject/Toggle Premium)
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $action = $_GET['action'];
    $user_id_to_modify = (int)$_GET['user_id'];

    if (array_key_exists($user_id_to_modify, $all_users)) {
        $user_name_for_message = sanitize_output($all_users[$user_id_to_modify]['name']);
        switch ($action) {
            case 'approve':
                $all_users[$user_id_to_modify]['is_approved'] = 1;
                $action_message = "User '{$user_name_for_message}' (ID: {$user_id_to_modify}) has been approved.";
                $action_message_type = 'success';
                break;
            case 'reject': // Sets is_approved to 0
                $all_users[$user_id_to_modify]['is_approved'] = 0;
                $action_message = "User '{$user_name_for_message}' (ID: {$user_id_to_modify}) has been rejected (unapproved).";
                $action_message_type = 'success';
                break;
            case 'toggle_premium':
                $all_users[$user_id_to_modify]['is_premium'] = !$all_users[$user_id_to_modify]['is_premium'];
                $premium_status = $all_users[$user_id_to_modify]['is_premium'] ? 'made premium' : 'removed from premium';
                $action_message = "User '{$user_name_for_message}' (ID: {$user_id_to_modify}) has been {$premium_status}.";
                $action_message_type = 'success';
                break;
            default:
                $action_message = "Invalid action specified.";
                $action_message_type = 'error';
        }
        // Redirect to clear GET parameters from URL and show message
        // Store message in session to display after redirect
        $_SESSION['action_message'] = $action_message;
        $_SESSION['action_message_type'] = $action_message_type;
        header("Location: manage_users.php");
        exit();
    } else {
        $_SESSION['action_message'] = "User ID {$user_id_to_modify} not found for action '{$action}'.";
        $_SESSION['action_message_type'] = 'error';
        header("Location: manage_users.php");
        exit();
    }
}

// Retrieve and clear action message from session
if (isset($_SESSION['action_message'])) {
    $action_message = $_SESSION['action_message'];
    $action_message_type = $_SESSION['action_message_type'];
    unset($_SESSION['action_message']);
    unset($_SESSION['action_message_type']);
}


include_once 'admin_header.php'; // Include admin header
?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php if (!empty($action_message)): ?>
    <div class="message <?php echo sanitize_output($action_message_type); ?>">
        <?php echo sanitize_output($action_message); ?>
    </div>
<?php endif; ?>

<?php if (!empty($all_users)): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Gender</th>
                <th>DOB</th>
                <th>Approved?</th>
                <th>Premium?</th>
                <th>Registered At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_users as $user): ?>
                <tr>
                    <td><?php echo sanitize_output($user['id']); ?></td>
                    <td><?php echo sanitize_output($user['name']); ?></td>
                    <td><?php echo sanitize_output($user['email']); ?></td>
                    <td><?php echo sanitize_output($user['gender']); ?></td>
                    <td><?php echo sanitize_output($user['dob']); ?></td>
                    <td><?php echo $user['is_approved'] ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>'; ?></td>
                    <td><?php echo $user['is_premium'] ? '<span style="color:blue;">Yes</span>' : '<span style="color:orange;">No</span>'; ?></td>
                    <td><?php echo sanitize_output( (new DateTime($user['created_at']))->format('Y-m-d H:i') ); ?></td>
                    <td class="action-links">
                        <?php if ($user['is_approved']): ?>
                            <a href="manage_users.php?action=reject&user_id=<?php echo $user['id']; ?>" class="reject" onclick="return confirm('Are you sure you want to reject (unapprove) this user?');">Reject</a>
                        <?php else: ?>
                            <a href="manage_users.php?action=approve&user_id=<?php echo $user['id']; ?>" class="approve" onclick="return confirm('Are you sure you want to approve this user?');">Approve</a>
                        <?php endif; ?>
                        |
                        <a href="manage_users.php?action=toggle_premium&user_id=<?php echo $user['id']; ?>" class="premium" onclick="return confirm('Are you sure you want to toggle premium status for this user?');">
                            <?php echo $user['is_premium'] ? 'Remove Premium' : 'Make Premium'; ?>
                        </a>
                        <!-- Add link to view/edit profile details later -->
                        <!-- | <a href="view_user_details.php?user_id=<?php echo $user['id']; ?>">Details</a> -->
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No users found in the system.</p>
<?php endif; ?>

<p style="margin-top:20px;">
    <a href="manage_users.php?action=reset_simulated_data">Reset Simulated User Data</a> (Note: This link is for demo purposes to reset data to initial state. It needs to be implemented if desired).
</p>

<?php
// Logic for resetting simulated data (optional, for testing)
if (isset($_GET['action']) && $_GET['action'] === 'reset_simulated_data') {
    unset($_SESSION['simulated_users_data']);
    header("Location: manage_users.php?message=sim_data_reset");
    exit();
}
if(isset($_GET['message']) && $_GET['message'] === 'sim_data_reset' && empty($action_message)){ // Avoid double message
    echo "<div class='message info'>Simulated user data has been reset to its initial state.</div>";
}

include_once 'admin_footer.php'; // Include admin footer
?>
