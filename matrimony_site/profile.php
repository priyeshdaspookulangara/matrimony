<?php
session_start();
@include_once 'includes/db_connect.php'; // $db
@include_once 'includes/functions.php';   // For sanitize_output() and other potential functions

// Authentication Check: Redirect to login.php if user_id is not set
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=login_required");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = "Manage Profile";

$errors = [];
$success_message = '';

// --- Simulate Fetching Existing Profile Data (Conceptual) ---
function get_user_profile_simulation($db_conn_placeholder, $current_user_id) {
    if ($current_user_id == 1) { // Simulate test user (user_id 1) has an existing profile.
        return [
            'user_id' => 1,
            'photo_path' => 'uploads/user_1_photo.jpg',
            'description' => 'A bit about myself for the test user profile. I enjoy coding and long walks.',
            'height' => '5ft 10in',
            'religion' => 'Agnostic',
            'caste' => 'N/A',
            'education' => 'PhD in Computer Science',
            'occupation' => 'Lead Developer',
            'income_range' => '20-30LPA',
            'family_type' => 'Nuclear',
            'birth_star' => 'Rohini', // New field
            'time_of_birth' => '10:30',  // New field (HH:MM format)
            'birth_place' => 'New Delhi, India' // New field
        ];
    }
    return null; // No profile found for other users in this simulation
}

// Fetch existing profile data (simulation)
$profile_data = get_user_profile_simulation(isset($db) ? $db : null, $user_id);

// --- Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve profile fields from $_POST
    $description = $_POST['description'] ?? '';
    $height = $_POST['height'] ?? '';
    $religion = $_POST['religion'] ?? '';
    $caste = $_POST['caste'] ?? '';
    $education = $_POST['education'] ?? '';
    $occupation = $_POST['occupation'] ?? '';
    $income_range = $_POST['income_range'] ?? '';
    $family_type = $_POST['family_type'] ?? '';
    // New fields
    $birth_star = $_POST['birth_star'] ?? '';
    $time_of_birth = $_POST['time_of_birth'] ?? '';
    $birth_place = $_POST['birth_place'] ?? '';


    // Retrieve uploaded file info from $_FILES['profile_photo']
    $profile_photo_info = $_FILES['profile_photo'] ?? null;
    $new_photo_path = $profile_data['photo_path'] ?? null;

    // --- Validation ---
    if (strlen($description) > 1000) $errors[] = "Description should not exceed 1000 characters.";
    if (strlen($height) > 20) $errors[] = "Height input seems too long.";
    if (strlen($religion) > 50) $errors[] = "Religion input seems too long.";
    if (strlen($caste) > 50) $errors[] = "Caste input seems too long.";
    if (strlen($education) > 100) $errors[] = "Education input seems too long.";
    if (strlen($occupation) > 100) $errors[] = "Occupation input seems too long.";
    // New field validations
    if (strlen($birth_star) > 50) $errors[] = "Birth Star input seems too long.";
    if (!empty($time_of_birth) && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time_of_birth)) {
        $errors[] = "Invalid Time of Birth format. Please use HH:MM (e.g., 14:30).";
    }
    if (strlen($birth_place) > 100) $errors[] = "Birth Place input seems too long.";


    // File Upload Validation (remains the same)
    $allowed_photo_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_photo_size = 5 * 1024 * 1024; // 5 MB

    if (isset($profile_photo_info) && $profile_photo_info['error'] == UPLOAD_ERR_OK) {
        if (!in_array($profile_photo_info['type'], $allowed_photo_types)) {
            $errors[] = "Invalid photo file type. Allowed types: JPG, PNG, GIF.";
        }
        if ($profile_photo_info['size'] > $max_photo_size) {
            $errors[] = "Photo file size exceeds the limit of 5MB.";
        }

        if (empty($errors)) {
            $photo_extension = pathinfo($profile_photo_info['name'], PATHINFO_EXTENSION);
            $unique_photo_filename = "user_" . $user_id . "_" . time() . "." . $photo_extension;
            $target_upload_path = "uploads/" . $unique_photo_filename;
            $new_photo_path = $target_upload_path;
            $success_message .= "Photo '" . sanitize_output($profile_photo_info['name']) . "' processed conceptually. Would be saved as " . sanitize_output($target_upload_path) . ". ";
        }
    } elseif (isset($profile_photo_info) && $profile_photo_info['error'] != UPLOAD_ERR_NO_FILE && $profile_photo_info['error'] != UPLOAD_ERR_OK) {
        $errors[] = "There was an error uploading the photo (Error code: " . $profile_photo_info['error'] . ").";
    }


    if (empty($errors)) {
        // --- Sanitization (Conceptual) ---
        $db_available = isset($db) && $db instanceof mysqli;
        $escaped_description = $db_available ? mysqli_real_escape_string($db, $description) : sanitize_output($description);
        $escaped_height = $db_available ? mysqli_real_escape_string($db, $height) : sanitize_output($height);
        $escaped_religion = $db_available ? mysqli_real_escape_string($db, $religion) : sanitize_output($religion);
        $escaped_caste = $db_available ? mysqli_real_escape_string($db, $caste) : sanitize_output($caste);
        $escaped_education = $db_available ? mysqli_real_escape_string($db, $education) : sanitize_output($education);
        $escaped_occupation = $db_available ? mysqli_real_escape_string($db, $occupation) : sanitize_output($occupation);
        $escaped_income_range = $db_available ? mysqli_real_escape_string($db, $income_range) : sanitize_output($income_range);
        $escaped_family_type = $db_available ? mysqli_real_escape_string($db, $family_type) : sanitize_output($family_type);
        // New fields sanitization
        $escaped_birth_star = $db_available ? mysqli_real_escape_string($db, $birth_star) : sanitize_output($birth_star);
        $escaped_time_of_birth = $db_available ? mysqli_real_escape_string($db, $time_of_birth) : sanitize_output($time_of_birth);
        $escaped_birth_place = $db_available ? mysqli_real_escape_string($db, $birth_place) : sanitize_output($birth_place);
        $escaped_photo_path = $db_available && $new_photo_path ? mysqli_real_escape_string($db, $new_photo_path) : sanitize_output($new_photo_path ?? '');


        // --- Database Interaction (Conceptual) ---
        $sql_query = "";
        if ($profile_data !== null) { // Profile exists, so UPDATE
            $sql_query = "UPDATE profiles SET ";
            $sql_query .= "description = '$escaped_description', height = '$escaped_height', religion = '$escaped_religion', caste = '$escaped_caste', education = '$escaped_education', occupation = '$escaped_occupation', income_range = '$escaped_income_range', family_type = '$escaped_family_type', ";
            $sql_query .= "birth_star = '$escaped_birth_star', time_of_birth = '$escaped_time_of_birth', birth_place = '$escaped_birth_place', "; // New fields
            $sql_query .= "photo_path = '$escaped_photo_path', last_updated = NOW() ";
            $sql_query .= "WHERE user_id = $user_id;";
            $success_message .= "Profile updated successfully (simulation).";
        } else { // No existing profile, so INSERT
            $sql_query = "INSERT INTO profiles (user_id, description, height, religion, caste, education, occupation, income_range, family_type, birth_star, time_of_birth, birth_place, photo_path, created_at, last_updated) VALUES ("; // New fields added
            $sql_query .= "$user_id, '$escaped_description', '$escaped_height', '$escaped_religion', '$escaped_caste', '$escaped_education', '$escaped_occupation', '$escaped_income_range', '$escaped_family_type', ";
            $sql_query .= "'$escaped_birth_star', '$escaped_time_of_birth', '$escaped_birth_place', "; // New fields
            $sql_query .= "'$escaped_photo_path', NOW(), NOW());";
            $success_message .= "Profile created successfully (simulation).";
        }
        $success_message .= " Query: " . sanitize_output($sql_query);

        // Update $profile_data with new values to reflect on the form immediately
        $profile_data = [
            'user_id' => $user_id, 'description' => $description, 'height' => $height, 'religion' => $religion, 'caste' => $caste, 'education' => $education, 'occupation' => $occupation, 'income_range' => $income_range, 'family_type' => $family_type,
            'birth_star' => $birth_star, 'time_of_birth' => $time_of_birth, 'birth_place' => $birth_place, // New fields
            'photo_path' => $new_photo_path
        ];
    }
}

// Income range options
$income_options = ['' => 'Select Income Range', '<5LPA' => 'Less than 5 LPA', '5-10LPA' => '5 LPA to 10 LPA', '10-15LPA' => '10 LPA to 15 LPA', '15-20LPA' => '15 LPA to 20 LPA', '20-30LPA' => '20 LPA to 30 LPA', '30LPA+' => '30 LPA and above'];
// Family type options
$family_type_options = ['' => 'Select Family Type', 'Nuclear' => 'Nuclear', 'Joint' => 'Joint', 'Other' => 'Other'];

include 'includes/header.php'; // Display header
?>

<h2><?php echo sanitize_output($page_title); ?></h2>

<?php if (!empty($errors)): ?>
    <div class="errors" style="color:red; border:1px solid red; padding:10px; margin-bottom:15px;">
        <strong>Please correct the following errors:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo sanitize_output($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <div class="success" style="color:green; border:1px solid green; padding:10px; margin-bottom:15px;">
        <p><?php echo $success_message; // Query part is already sanitized ?></p>
    </div>
<?php endif; ?>

<form action="profile.php" method="POST" enctype="multipart/form-data">
    <div>
        <label for="profile_photo">Profile Photo:</label>
        <input type="file" name="profile_photo" id="profile_photo">
        <?php if (!empty($profile_data['photo_path'])): ?>
            <p>Current photo: <img src="<?php echo sanitize_output($profile_data['photo_path']); ?>" alt="Profile Photo" style="max-width: 150px; max-height: 150px; display:block; margin-top:5px;"></p>
            <small>(Uploading a new photo will replace the current one. Conceptual path: <?php echo sanitize_output($profile_data['photo_path']); ?>)</small>
        <?php else: ?>
            <small>(No photo uploaded yet)</small>
        <?php endif; ?>
    </div>

    <div>
        <label for="description">Brief Description (max 1000 chars):</label><br>
        <textarea name="description" id="description" rows="5" cols="60" maxlength="1000"><?php echo sanitize_output($profile_data['description'] ?? ''); ?></textarea>
    </div>

    <fieldset style="margin-top:15px; margin-bottom:15px; padding:15px; border:1px solid #ccc;">
        <legend>Personal & Family Details</legend>
        <div>
            <label for="height">Height:</label>
            <input type="text" name="height" id="height" value="<?php echo sanitize_output($profile_data['height'] ?? ''); ?>" maxlength="20">
        </div>
        <div>
            <label for="religion">Religion:</label>
            <input type="text" name="religion" id="religion" value="<?php echo sanitize_output($profile_data['religion'] ?? ''); ?>" maxlength="50">
        </div>
        <div>
            <label for="caste">Caste (Optional):</label>
            <input type="text" name="caste" id="caste" value="<?php echo sanitize_output($profile_data['caste'] ?? ''); ?>" maxlength="50">
        </div>
         <div>
            <label for="family_type">Family Type:</label>
            <select name="family_type" id="family_type">
                 <?php foreach ($family_type_options as $value => $label): ?>
                    <option value="<?php echo sanitize_output($value); ?>" <?php echo (isset($profile_data['family_type']) && $profile_data['family_type'] == $value) ? 'selected' : ''; ?>>
                        <?php echo sanitize_output($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>

    <fieldset style="margin-top:15px; margin-bottom:15px; padding:15px; border:1px solid #ccc;">
        <legend>Horoscope Details</legend>
        <div>
            <label for="birth_star">Birth Star (Nakshatra):</label>
            <input type="text" name="birth_star" id="birth_star" value="<?php echo sanitize_output($profile_data['birth_star'] ?? ''); ?>" maxlength="50">
        </div>
        <div>
            <label for="time_of_birth">Time of Birth:</label>
            <input type="time" name="time_of_birth" id="time_of_birth" value="<?php echo sanitize_output($profile_data['time_of_birth'] ?? ''); ?>">
        </div>
        <div>
            <label for="birth_place">Birth Place:</label>
            <input type="text" name="birth_place" id="birth_place" value="<?php echo sanitize_output($profile_data['birth_place'] ?? ''); ?>" maxlength="100">
        </div>
    </fieldset>

    <fieldset style="margin-top:15px; margin-bottom:15px; padding:15px; border:1px solid #ccc;">
        <legend>Education & Career</legend>
        <div>
            <label for="education">Education:</label>
            <input type="text" name="education" id="education" value="<?php echo sanitize_output($profile_data['education'] ?? ''); ?>" maxlength="100">
        </div>
        <div>
            <label for="occupation">Occupation:</label>
            <input type="text" name="occupation" id="occupation" value="<?php echo sanitize_output($profile_data['occupation'] ?? ''); ?>" maxlength="100">
        </div>
        <div>
            <label for="income_range">Annual Income Range:</label>
            <select name="income_range" id="income_range">
                <?php foreach ($income_options as $value => $label): ?>
                    <option value="<?php echo sanitize_output($value); ?>" <?php echo (isset($profile_data['income_range']) && $profile_data['income_range'] == $value) ? 'selected' : ''; ?>>
                        <?php echo sanitize_output($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>

    <div>
        <button type="submit">Save Profile</button>
    </div>
</form>

<?php include 'includes/footer.php'; // Display footer ?>
