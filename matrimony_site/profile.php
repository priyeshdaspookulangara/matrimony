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
$page_title = "Manage Profile & View Preferences";

$errors = [];
$success_message = '';

// --- Simulate Fetching Existing Profile Data (Conceptual) ---
function get_user_profile_simulation($db_conn_placeholder, $current_user_id) {
    if ($current_user_id == 1) {
        return [
            'user_id' => 1, 'photo_path' => 'uploads/user_1_photo.jpg',
            'description' => 'A bit about myself for the test user profile. I enjoy coding and long walks.',
            'height' => '5ft 10in', 'religion' => 'Agnostic', 'caste' => 'N/A',
            'education' => 'PhD in Computer Science', 'occupation' => 'Lead Developer',
            'income_range' => '20-30LPA', 'family_type' => 'Nuclear',
            'birth_star' => 'Rohini', 'time_of_birth' => '10:30', 'birth_place' => 'New Delhi, India',
            'hobbies' => 'Reading, Traveling, Photography' // New field
        ];
    }
    return null;
}
$profile_data = get_user_profile_simulation(isset($db) ? $db : null, $user_id);

// --- Fetch User's Own Preferences (Simulated from Session) ---
if (!isset($_SESSION['user_preferences_data'])) {
    $_SESSION['user_preferences_data'] = [];
}
$user_own_preferences = $_SESSION['user_preferences_data'][$user_id] ?? [];


// --- Handle Form Submission for Profile Edit ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type']) && $_POST['form_type'] == 'profile_edit') {
    // Retrieve profile fields from $_POST
    $description = $_POST['description'] ?? '';
    $height = $_POST['height'] ?? '';
    $religion = $_POST['religion'] ?? '';
    $caste = $_POST['caste'] ?? '';
    $education = $_POST['education'] ?? '';
    $occupation = $_POST['occupation'] ?? '';
    $income_range = $_POST['income_range'] ?? '';
    $family_type = $_POST['family_type'] ?? '';
    $birth_star = $_POST['birth_star'] ?? '';
    $time_of_birth = $_POST['time_of_birth'] ?? '';
    $birth_place = $_POST['birth_place'] ?? '';
    $hobbies = $_POST['hobbies'] ?? ''; // New field

    $profile_photo_info = $_FILES['profile_photo'] ?? null;
    $new_photo_path = $profile_data['photo_path'] ?? null;

    // --- Validation ---
    if (strlen($description) > 1000) $errors[] = "Description should not exceed 1000 characters.";
    if (strlen($hobbies) > 255) $errors[] = "Hobbies field should not exceed 255 characters."; // New validation
    // ... (all other validations for profile fields) ...
    if (strlen($birth_star) > 50) $errors[] = "Birth Star input seems too long.";
    if (!empty($time_of_birth) && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time_of_birth)) {
        $errors[] = "Invalid Time of Birth format. Please use HH:MM (e.g., 14:30).";
    }
    if (strlen($birth_place) > 100) $errors[] = "Birth Place input seems too long.";

    // File Upload Validation (remains the same)
    $allowed_photo_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_photo_size = 5 * 1024 * 1024;
    if (isset($profile_photo_info) && $profile_photo_info['error'] == UPLOAD_ERR_OK) {
        if (!in_array($profile_photo_info['type'], $allowed_photo_types)) $errors[] = "Invalid photo file type.";
        if ($profile_photo_info['size'] > $max_photo_size) $errors[] = "Photo file size exceeds 5MB.";
        if (empty($errors)) {
            $photo_extension = pathinfo($profile_photo_info['name'], PATHINFO_EXTENSION);
            $unique_photo_filename = "user_" . $user_id . "_" . time() . "." . $photo_extension;
            $target_upload_path = "uploads/" . $unique_photo_filename;
            $new_photo_path = $target_upload_path;
            $success_message .= "Photo '" . sanitize_output($profile_photo_info['name']) . "' processed conceptually. ";
        }
    } elseif (isset($profile_photo_info) && $profile_photo_info['error'] != UPLOAD_ERR_NO_FILE && $profile_photo_info['error'] != UPLOAD_ERR_OK) {
        $errors[] = "Error uploading photo (Code: " . $profile_photo_info['error'] . ").";
    }

    if (empty($errors)) {
        $db_available = isset($db) && $db instanceof mysqli;
        // Conceptual Sanitization
        $escaped_description = $db_available ? mysqli_real_escape_string($db, $description) : sanitize_output($description);
        $escaped_height = $db_available ? mysqli_real_escape_string($db, $height) : sanitize_output($height);
        $escaped_religion = $db_available ? mysqli_real_escape_string($db, $religion) : sanitize_output($religion);
        $escaped_caste = $db_available ? mysqli_real_escape_string($db, $caste) : sanitize_output($caste);
        $escaped_education = $db_available ? mysqli_real_escape_string($db, $education) : sanitize_output($education);
        $escaped_occupation = $db_available ? mysqli_real_escape_string($db, $occupation) : sanitize_output($occupation);
        $escaped_income_range = $db_available ? mysqli_real_escape_string($db, $income_range) : sanitize_output($income_range);
        $escaped_family_type = $db_available ? mysqli_real_escape_string($db, $family_type) : sanitize_output($family_type);
        $escaped_birth_star = $db_available ? mysqli_real_escape_string($db, $birth_star) : sanitize_output($birth_star);
        $escaped_time_of_birth = $db_available ? mysqli_real_escape_string($db, $time_of_birth) : sanitize_output($time_of_birth);
        $escaped_birth_place = $db_available ? mysqli_real_escape_string($db, $birth_place) : sanitize_output($birth_place);
        $escaped_hobbies = $db_available ? mysqli_real_escape_string($db, $hobbies) : sanitize_output($hobbies); // New field
        $escaped_photo_path = $db_available && $new_photo_path ? mysqli_real_escape_string($db, $new_photo_path) : sanitize_output($new_photo_path ?? '');

        // Conceptual DB Interaction
        $sql_query = "";
        if ($profile_data !== null) { // UPDATE
            $sql_query = "UPDATE profiles SET description = '$escaped_description', height = '$escaped_height', religion = '$escaped_religion', caste = '$escaped_caste', education = '$escaped_education', occupation = '$escaped_occupation', income_range = '$escaped_income_range', family_type = '$escaped_family_type', birth_star = '$escaped_birth_star', time_of_birth = '$escaped_time_of_birth', birth_place = '$escaped_birth_place', hobbies = '$escaped_hobbies', photo_path = '$escaped_photo_path', last_updated = NOW() WHERE user_id = $user_id;"; // Added hobbies
            $success_message .= "Profile updated successfully (simulation).";
        } else { // INSERT
            $sql_query = "INSERT INTO profiles (user_id, description, height, religion, caste, education, occupation, income_range, family_type, birth_star, time_of_birth, birth_place, hobbies, photo_path, created_at, last_updated) VALUES ($user_id, '$escaped_description', '$escaped_height', '$escaped_religion', '$escaped_caste', '$escaped_education', '$escaped_occupation', '$escaped_income_range', '$escaped_family_type', '$escaped_birth_star', '$escaped_time_of_birth', '$escaped_birth_place', '$escaped_hobbies', '$escaped_photo_path', NOW(), NOW());"; // Added hobbies
            $success_message .= "Profile created successfully (simulation).";
        }
        // $success_message .= " Query: " . sanitize_output($sql_query);

        $profile_data = [ // Update $profile_data for immediate reflection
            'user_id' => $user_id, 'description' => $description, 'height' => $height,
            'religion' => $religion, 'caste' => $caste, 'education' => $education,
            'occupation' => $occupation, 'income_range' => $income_range,
            'family_type' => $family_type, 'birth_star' => $birth_star,
            'time_of_birth' => $time_of_birth, 'birth_place' => $birth_place,
            'hobbies' => $hobbies, // New field
            'photo_path' => $new_photo_path
        ];
    }
}

$income_options = ['' => 'Select Income Range', '<5LPA' => 'Less than 5 LPA', '5-10LPA' => '5 LPA to 10 LPA', '10-15LPA' => '10 LPA to 15 LPA', '15-20LPA' => '15 LPA to 20 LPA', '20-30LPA' => '20 LPA to 30 LPA', '30LPA+' => '30 LPA and above'];
$family_type_options = ['' => 'Select Family Type', 'Nuclear' => 'Nuclear', 'Joint' => 'Joint', 'Other' => 'Other'];

include 'includes/header.php';
?>

<h2><?php echo sanitize_output($page_title); ?></h2>

<?php if (!empty($success_message) && strpos($success_message, "Profile") !== false ): ?>
    <div class="success" style="color:green; border:1px solid green; padding:10px; margin-bottom:15px;">
        <p><?php echo $success_message; ?></p>
    </div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="errors" style="color:red; border:1px solid red; padding:10px; margin-bottom:15px;">
        <strong>Please correct the following errors:</strong>
        <ul><?php foreach ($errors as $error) echo "<li>" . sanitize_output($error) . "</li>"; ?></ul>
    </div>
<?php endif; ?>

<h3 style="border-bottom:1px solid #ccc; padding-bottom:5px;">Edit Your Profile</h3>
<form action="profile.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="form_type" value="profile_edit">
    <div>
        <label for="profile_photo">Profile Photo:</label>
        <input type="file" name="profile_photo" id="profile_photo">
        <?php if (!empty($profile_data['photo_path'])): ?>
            <p>Current photo: <img src="<?php echo sanitize_output($profile_data['photo_path']); ?>" alt="Profile Photo" style="max-width: 100px; max-height: 100px; display:block; margin-top:5px;"></p>
        <?php endif; ?>
    </div>
    <div>
        <label for="description">Brief Description:</label><br>
        <textarea name="description" id="description" rows="4" cols="50"><?php echo sanitize_output($profile_data['description'] ?? ''); ?></textarea>
    </div>
    <div>
        <label for="hobbies">Hobbies (comma-separated, max 255 chars):</label>
        <textarea name="hobbies" id="hobbies" rows="3" maxlength="255"><?php echo sanitize_output($profile_data['hobbies'] ?? ''); ?></textarea>
    </div>

    <fieldset style="margin-top:10px; margin-bottom:10px; padding:10px; border:1px solid #eee;">
        <legend>Personal & Family</legend>
        Height: <input type="text" name="height" value="<?php echo sanitize_output($profile_data['height'] ?? ''); ?>"> |
        Religion: <input type="text" name="religion" value="<?php echo sanitize_output($profile_data['religion'] ?? ''); ?>"> |
        Caste: <input type="text" name="caste" value="<?php echo sanitize_output($profile_data['caste'] ?? ''); ?>"> |
        Family Type: <select name="family_type">
            <?php foreach ($family_type_options as $val => $lab): ?>
            <option value="<?php echo sanitize_output($val); ?>" <?php echo (isset($profile_data['family_type']) && $profile_data['family_type'] == $val) ? 'selected' : ''; ?>><?php echo sanitize_output($lab); ?></option>
            <?php endforeach; ?>
        </select>
    </fieldset>
    <fieldset style="margin-top:10px; margin-bottom:10px; padding:10px; border:1px solid #eee;">
        <legend>Horoscope</legend>
        Birth Star: <input type="text" name="birth_star" value="<?php echo sanitize_output($profile_data['birth_star'] ?? ''); ?>"> |
        Time of Birth: <input type="time" name="time_of_birth" value="<?php echo sanitize_output($profile_data['time_of_birth'] ?? ''); ?>"> |
        Birth Place: <input type="text" name="birth_place" value="<?php echo sanitize_output($profile_data['birth_place'] ?? ''); ?>">
    </fieldset>
    <fieldset style="margin-top:10px; margin-bottom:10px; padding:10px; border:1px solid #eee;">
        <legend>Education & Career</legend>
        Education: <input type="text" name="education" value="<?php echo sanitize_output($profile_data['education'] ?? ''); ?>"> |
        Occupation: <input type="text" name="occupation" value="<?php echo sanitize_output($profile_data['occupation'] ?? ''); ?>"> |
        Income: <select name="income_range">
             <?php foreach ($income_options as $val => $lab): ?>
            <option value="<?php echo sanitize_output($val); ?>" <?php echo (isset($profile_data['income_range']) && $profile_data['income_range'] == $val) ? 'selected' : ''; ?>><?php echo sanitize_output($lab); ?></option>
            <?php endforeach; ?>
        </select>
    </fieldset>
    <button type="submit">Save Profile</button>
</form>

<hr style="margin-top:30px; margin-bottom:20px;">

<h3 style="border-bottom:1px solid #ccc; padding-bottom:5px;">Your Saved Partner Preferences</h3>
<?php if (!empty($user_own_preferences)): ?>
    <div class="preferences-display" style="line-height:1.6;">
        <p><strong>Preferred Age Range:</strong> <?php echo sanitize_output($user_own_preferences['pref_min_age'] ?? 'N/A'); ?> - <?php echo sanitize_output($user_own_preferences['pref_max_age'] ?? 'N/A'); ?> years</p>
        <p><strong>Preferred Height Range:</strong> <?php echo sanitize_output($user_own_preferences['pref_min_height'] ?? 'N/A'); ?> - <?php echo sanitize_output($user_own_preferences['pref_max_height'] ?? 'N/A'); ?></p>
        <p><strong>Preferred Religion(s):</strong> <?php echo nl2br(sanitize_output($user_own_preferences['pref_religion'] ?? 'N/A')); ?></p>
        <p><strong>Preferred Caste(s):</strong> <?php echo nl2br(sanitize_output($user_own_preferences['pref_caste'] ?? 'N/A')); ?></p>
        <p><strong>Preferred Education(s):</strong> <?php echo nl2br(sanitize_output($user_own_preferences['pref_education'] ?? 'N/A')); ?></p>
        <p><strong>Preferred Occupation(s):</strong> <?php echo nl2br(sanitize_output($user_own_preferences['pref_occupation'] ?? 'N/A')); ?></p>
        <p><strong>Preferred Birth Star(s):</strong> <?php echo nl2br(sanitize_output($user_own_preferences['pref_birth_star'] ?? 'N/A')); ?></p>
        <p><strong>Preferred Skin Complexion(s):</strong> <?php echo nl2br(sanitize_output($user_own_preferences['pref_skin_complexion'] ?? 'N/A')); ?></p>
        <p><strong>Preferred District(s):</strong> <?php echo nl2br(sanitize_output($user_own_preferences['pref_district'] ?? 'N/A')); ?></p>
        <p><strong>Preferred Family Type:</strong> <?php echo sanitize_output($user_own_preferences['pref_family_type'] ?? 'N/A'); ?></p>
        <p style="margin-top:15px;"><a href="manage_preferences.php" style="padding:8px 12px; background-color:#007bff; color:white; text-decoration:none; border-radius:4px;">Edit Preferences</a></p>
    </div>
<?php else: ?>
    <p>You have not set any partner preferences yet. <a href="manage_preferences.php" style="color:#007bff; text-decoration:none;">Set Preferences Now</a></p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
