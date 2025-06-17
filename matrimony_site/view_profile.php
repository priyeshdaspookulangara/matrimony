<?php
session_start();
@include_once 'includes/db_connect.php'; // $db (conceptual for now)
@include_once 'includes/functions.php';   // For calculate_age(), sanitize_output()

// --- Message Handling ---
$display_notification = '';
if (isset($_GET['message'])) {
    $message_code = sanitize_output($_GET['message']); // Sanitize the message code itself
    $message_map = [
        'interest_expressed_successfully' => ['text' => 'Interest expressed successfully!', 'type' => 'success'],
        'interest_already_expressed' => ['text' => 'You have already expressed interest in this profile.', 'type' => 'info'],
        'cannot_interest_self' => ['text' => 'You cannot express interest in your own profile.', 'type' => 'error'],
        'invalid_target_profile' => ['text' => 'The specified profile for expressing interest was invalid.', 'type' => 'error'],
        'no_target_id_provided' => ['text' => 'No target profile ID was provided for expressing interest.', 'type' => 'error'],
        'interest_general_error' => ['text' => 'An unexpected error occurred while expressing interest.', 'type' => 'error'],
        // General messages for dashboard redirection
        'invalid_profile_id' => ['text' => 'The profile ID provided is invalid.', 'type' => 'error'],
        'no_profile_id' => ['text' => 'No profile ID was specified.', 'type' => 'error'],
    ];

    if (array_key_exists($message_code, $message_map)) {
        $message_details = $message_map[$message_code];
        $display_notification = "<div class='message {$message_details['type']}' style='padding:10px; margin-bottom:15px; border:1px solid; border-radius:4px; color: #fff; background-color: " . ($message_details['type'] == 'success' ? '#28a745' : ($message_details['type'] == 'error' ? '#dc3545' : '#17a2b8')) . ";'>" . sanitize_output($message_details['text']) . "</div>";
    } elseif (!empty($message_code)) { // Fallback for generic unknown messages
        $display_notification = "<div class='message info' style='padding:10px; margin-bottom:15px; border:1px solid #17a2b8; border-radius:4px; color: #fff; background-color: #17a2b8;'>" . sanitize_output($message_code) . "</div>";
    }
}


// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=login_required");
    exit();
}
$current_logged_in_user_id = $_SESSION['user_id'];

// --- Get Target User ID ---
$profile_user_id = null;
if (isset($_GET['id'])) {
    if (filter_var($_GET['id'], FILTER_VALIDATE_INT) && $_GET['id'] > 0) {
        $profile_user_id = (int)$_GET['id'];
    } else {
        // Invalid ID format, set message for display after header
        $_SESSION['temp_message'] = ['text' => 'Invalid profile ID format.', 'type' => 'error'];
        header("Location: dashboard.php?message=invalid_profile_id");
        exit();
    }
} else {
    // No ID provided
    $_SESSION['temp_message'] = ['text' => 'No profile ID specified.', 'type' => 'error'];
    header("Location: dashboard.php?message=no_profile_id");
    exit();
}

// Prevent users from viewing their own profile via this page (redirect to editable profile)
if ($profile_user_id === $current_logged_in_user_id) {
    header("Location: profile.php"); // Their own editable profile page
    exit();
}

// --- Simulate Fetching Full Profile Data (Conceptual) ---
// This function remains the same as before
function get_full_user_profile_simulation($db_conn_placeholder, $target_user_id) {
    $users_data = [
        1 => ['id' => 1, 'name' => 'Test User (Viewable)', 'email' => 'test@example.com', 'gender' => 'Male', 'dob' => '1990-01-01', 'is_approved' => 1, 'registration_date' => '2023-01-15'],
        2 => ['id' => 2, 'name' => 'Jane Doe (Approved)', 'email' => 'jane@example.com', 'gender' => 'Female', 'dob' => '1992-05-15', 'is_approved' => 1, 'registration_date' => '2023-02-20'],
        3 => ['id' => 3, 'name' => 'Pending User (Unapproved)', 'email' => 'pending@example.com', 'gender' => 'Other', 'dob' => '1995-10-20', 'is_approved' => 0, 'registration_date' => '2023-03-01'],
    ];
    $profiles_data = [
        1 => ['user_id' => 1, 'photo_path' => 'uploads/user_1_photo.jpg', 'description' => 'This is the sample bio for Test User. I enjoy photography and travel.', 'height' => '5ft 10in', 'religion' => 'Agnostic', 'caste' => 'N/A', 'education' => 'PhD in Computer Science', 'occupation' => 'Lead Developer', 'income_range' => '20-30LPA', 'family_type' => 'Nuclear', 'last_updated' => '2023-05-10'],
        2 => ['user_id' => 2, 'photo_path' => 'uploads/user_2_photo.jpg', 'description' => 'Jane Doe\'s bio: Enjoys reading, hiking, and volunteering.', 'height' => '5ft 6in', 'religion' => 'Spiritual', 'caste' => 'Does not believe in caste', 'education' => 'Masters in Arts', 'occupation' => 'Graphic Designer', 'income_range' => '10-15LPA', 'family_type' => 'Joint', 'last_updated' => '2023-06-01'],
        3 => ['user_id' => 3, 'description' => 'Awaiting approval.', 'height' => 'N/A']
    ];
    if (!isset($users_data[$target_user_id])) return null;
    $user_info = $users_data[$target_user_id];
    $profile_info = $profiles_data[$target_user_id] ?? [];
    return array_merge($user_info, $profile_info);
}

$view_profile_data = get_full_user_profile_simulation(isset($db) ? $db : null, $profile_user_id);

$page_title = "View Profile"; // Default
$display_content = '';

if ($view_profile_data === null) {
    $page_title = "Profile Not Found";
    $display_content = "<p>The profile you are trying to view does not exist.</p>";
} elseif ($view_profile_data['is_approved'] == 0) {
    $page_title = "Profile Not Active";
    $display_content = "<p>This profile is currently not active or is pending approval.</p>";
} else {
    $page_title = "View Profile: " . sanitize_output($view_profile_data['name']);
    $age = calculate_age($view_profile_data['dob']);

    $display_content .= "<h2>Profile of " . sanitize_output($view_profile_data['name']) . "</h2>";
    // Photo, personal details, about, lifestyle, education sections (as before) ...
    if (!empty($view_profile_data['photo_path'])) {
        $display_content .= "<p><img src='" . sanitize_output($view_profile_data['photo_path']) . "' alt='Profile photo of " . sanitize_output($view_profile_data['name']) . "' style='max-width: 200px; max-height: 200px; border: 1px solid #ccc;'></p>";
        $display_content .= "<p><small><i>Conceptual photo path: " . sanitize_output($view_profile_data['photo_path']) . "</i></small></p>";
    } else {
        $display_content .= "<p><img src='images/default_avatar.png' alt='Default profile photo' style='max-width: 150px; max-height: 150px; border: 1px solid #ccc;'></p>";
    }
    $display_content .= "<h3>Personal Details</h3>";
    $display_content .= "<p><strong>Name:</strong> " . sanitize_output($view_profile_data['name']) . "</p>";
    $display_content .= "<p><strong>Age:</strong> " . sanitize_output($age) . " years</p>";
    $display_content .= "<p><strong>Gender:</strong> " . sanitize_output($view_profile_data['gender']) . "</p>";
    $display_content .= "<p><strong>Member Since:</strong> " . sanitize_output( (new DateTime($view_profile_data['registration_date']))->format('M jS, Y') ) . "</p>";
    $display_content .= "<h3>About</h3>";
    $display_content .= "<p>" . nl2br(sanitize_output($view_profile_data['description'] ?? 'Not provided.')) . "</p>";
    $display_content .= "<h3>Lifestyle & Background</h3>";
    $display_content .= "<p><strong>Height:</strong> " . sanitize_output($view_profile_data['height'] ?? 'N/A') . "</p>";
    $display_content .= "<p><strong>Religion:</strong> " . sanitize_output($view_profile_data['religion'] ?? 'N/A') . "</p>";
    $display_content .= "<p><strong>Caste:</strong> " . sanitize_output($view_profile_data['caste'] ?? 'N/A') . "</p>";
    $display_content .= "<p><strong>Family Type:</strong> " . sanitize_output($view_profile_data['family_type'] ?? 'N/A') . "</p>";
    $display_content .= "<h3>Education & Career</h3>";
    $display_content .= "<p><strong>Education:</strong> " . sanitize_output($view_profile_data['education'] ?? 'N/A') . "</p>";
    $display_content .= "<p><strong>Occupation:</strong> " . sanitize_output($view_profile_data['occupation'] ?? 'N/A') . "</p>";
    $display_content .= "<p><strong>Income Range:</strong> " . sanitize_output($view_profile_data['income_range'] ?? 'N/A') . "</p>";
    if(isset($view_profile_data['last_updated'])) {
        $display_content .= "<p><small>Profile last updated: " . sanitize_output( (new DateTime($view_profile_data['last_updated']))->format('M jS, Y') ) . "</small></p>";
    }


    // --- "Express Interest" Button Logic ---
    $interest_check_key = $current_logged_in_user_id . "_" . $profile_user_id;
    $has_expressed_interest = isset($_SESSION['expressed_interests'][$interest_check_key]);

    $display_content .= "<div style='margin-top: 20px;'>";
    if ($has_expressed_interest) {
        $interest_timestamp = $_SESSION['expressed_interests'][$interest_check_key];
        $display_content .= "<button type='button' disabled style='padding: 10px 15px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: not-allowed;'>Interest Already Expressed</button>";
        $display_content .= "<p><small>You expressed interest on: " . date('M jS, Y \a\t H:i', $interest_timestamp) . "</small></p>";
    } else {
        $display_content .= "<a href='express_interest.php?target_id=" . urlencode($profile_user_id) . "' class='button' style='padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Express Interest</a>";
    }
    $display_content .= "</div>";
}

include 'includes/header.php'; // Now include header, $page_title is set
?>

<?php
// Display any notifications from GET parameters or session flash messages
if (!empty($display_notification)) {
    echo $display_notification;
}

// Display the main content (profile details or error messages)
echo $display_content;
?>

<?php include 'includes/footer.php'; ?>
