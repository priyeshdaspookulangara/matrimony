<?php
session_start();
@include_once 'includes/db_connect.php'; // $db
@include_once 'includes/functions.php';   // For future helper functions

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
// In a real application, this function would query the database.
function get_user_profile_simulation($db_conn_placeholder, $current_user_id) {
    // Simulate that user_id 1 (our test user) has an existing profile.
    if ($current_user_id == 1) {
        // These values would typically come from a 'profiles' table.
        return [
            'user_id' => 1,
            'photo_path' => 'uploads/user_1_photo.jpg', // Conceptual existing photo
            'description' => 'A bit about myself for the test user profile. I enjoy coding and long walks.',
            'height' => '5ft 10in',
            'religion' => 'Agnostic',
            'caste' => 'N/A',
            'education' => 'PhD in Computer Science',
            'occupation' => 'Lead Developer',
            'income_range' => '20-30LPA',
            'family_type' => 'Nuclear'
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

    // Retrieve uploaded file info from $_FILES['profile_photo']
    $profile_photo_info = $_FILES['profile_photo'] ?? null;
    $new_photo_path = $profile_data['photo_path'] ?? null; // Keep old photo if new one isn't uploaded

    // --- Validation ---
    if (strlen($description) > 1000) {
        $errors[] = "Description should not exceed 1000 characters.";
    }
    if (strlen($height) > 20) { $errors[] = "Height seems too long."; }
    if (strlen($religion) > 50) { $errors[] = "Religion seems too long."; }
    // Add more specific validations as needed for other fields.

    // File Upload Validation
    $allowed_photo_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_photo_size = 5 * 1024 * 1024; // 5 MB

    if (isset($profile_photo_info) && $profile_photo_info['error'] == UPLOAD_ERR_OK) {
        if (!in_array($profile_photo_info['type'], $allowed_photo_types)) {
            $errors[] = "Invalid photo file type. Allowed types: JPG, PNG, GIF.";
        }
        if ($profile_photo_info['size'] > $max_photo_size) {
            $errors[] = "Photo file size exceeds the limit of 5MB.";
        }

        if (empty($errors)) { // Only proceed with file handling if basic checks passed
            $photo_extension = pathinfo($profile_photo_info['name'], PATHINFO_EXTENSION);
            $unique_photo_filename = "user_" . $user_id . "_" . time() . "." . $photo_extension;
            $target_upload_path = "uploads/" . $unique_photo_filename;

            // Conceptual: move_uploaded_file($profile_photo_info['tmp_name'], $target_upload_path);
            // For this simulation, we just use the $target_upload_path if validation passes.
            $new_photo_path = $target_upload_path; // This would be stored in DB
            $success_message .= "Photo '" . htmlspecialchars($profile_photo_info['name']) . "' processed conceptually. Would be saved as " . htmlspecialchars($target_upload_path) . ". ";
        }
    } elseif (isset($profile_photo_info) && $profile_photo_info['error'] != UPLOAD_ERR_NO_FILE && $profile_photo_info['error'] != UPLOAD_ERR_OK) {
        $errors[] = "There was an error uploading the photo (Error code: " . $profile_photo_info['error'] . ").";
    }


    if (empty($errors)) {
        // --- Sanitization (Conceptual) ---
        // $db must be a valid mysqli connection from db_connect.php
        $escaped_description = isset($db) && $db instanceof mysqli ? mysqli_real_escape_string($db, $description) : htmlspecialchars($description);
        $escaped_height = isset($db) && $db instanceof mysqli ? mysqli_real_escape_string($db, $height) : htmlspecialchars($height);
        // ... Sanitize other text fields similarly ...
        $escaped_religion = isset($db) && $db instanceof mysqli ? mysqli_real_escape_string($db, $religion) : htmlspecialchars($religion);
        $escaped_caste = isset($db) && $db instanceof mysqli ? mysqli_real_escape_string($db, $caste) : htmlspecialchars($caste);
        $escaped_education = isset($db) && $db instanceof mysqli ? mysqli_real_escape_string($db, $education) : htmlspecialchars($education);
        $escaped_occupation = isset($db) && $db instanceof mysqli ? mysqli_real_escape_string($db, $occupation) : htmlspecialchars($occupation);
        // Selects ($income_range, $family_type) are from a predefined list, less risk, but escaping is still good practice.
        $escaped_income_range = isset($db) && $db instanceof mysqli ? mysqli_real_escape_string($db, $income_range) : htmlspecialchars($income_range);
        $escaped_family_type = isset($db) && $db instanceof mysqli ? mysqli_real_escape_string($db, $family_type) : htmlspecialchars($family_type);
        $escaped_photo_path = isset($db) && $db instanceof mysqli && $new_photo_path ? mysqli_real_escape_string($db, $new_photo_path) : htmlspecialchars($new_photo_path ?? '');


        // --- Database Interaction (Conceptual) ---
        $sql_query = "";
        if ($profile_data !== null) { // Profile exists, so UPDATE
            $sql_query = "UPDATE profiles SET ";
            $sql_query .= "description = '$escaped_description', ";
            $sql_query .= "height = '$escaped_height', ";
            $sql_query .= "religion = '$escaped_religion', ";
            $sql_query .= "caste = '$escaped_caste', ";
            $sql_query .= "education = '$escaped_education', ";
            $sql_query .= "occupation = '$escaped_occupation', ";
            $sql_query .= "income_range = '$escaped_income_range', ";
            $sql_query .= "family_type = '$escaped_family_type', ";
            $sql_query .= "photo_path = '$escaped_photo_path', "; // Update photo path
            $sql_query .= "last_updated = NOW() ";
            $sql_query .= "WHERE user_id = $user_id;";
            $success_message .= "Profile updated successfully (simulation).";
        } else { // No existing profile, so INSERT
            $sql_query = "INSERT INTO profiles (user_id, description, height, religion, caste, education, occupation, income_range, family_type, photo_path, created_at, last_updated) VALUES (";
            $sql_query .= "$user_id, ";
            $sql_query .= "'$escaped_description', ";
            $sql_query .= "'$escaped_height', ";
            $sql_query .= "'$escaped_religion', ";
            $sql_query .= "'$escaped_caste', ";
            $sql_query .= "'$escaped_education', ";
            $sql_query .= "'$escaped_occupation', ";
            $sql_query .= "'$escaped_income_range', ";
            $sql_query .= "'$escaped_family_type', ";
            $sql_query .= "'$escaped_photo_path', ";
            $sql_query .= "NOW(), NOW());";
            $success_message .= "Profile created successfully (simulation).";
        }
        $success_message .= " Query: " . htmlspecialchars($sql_query);

        // Update $profile_data with new values to reflect on the form immediately
        $profile_data = [
            'user_id' => $user_id,
            'description' => $description,
            'height' => $height,
            'religion' => $religion,
            'caste' => $caste,
            'education' => $education,
            'occupation' => $occupation,
            'income_range' => $income_range,
            'family_type' => $family_type,
            'photo_path' => $new_photo_path // This is the new path after potential upload
        ];
    }
}

// Income range options
$income_options = [
    '' => 'Select Income Range',
    '<5LPA' => 'Less than 5 LPA',
    '5-10LPA' => '5 LPA to 10 LPA',
    '10-15LPA' => '10 LPA to 15 LPA',
    '15-20LPA' => '15 LPA to 20 LPA',
    '20-30LPA' => '20 LPA to 30 LPA',
    '30LPA+' => '30 LPA and above'
];

// Family type options
$family_type_options = [
    '' => 'Select Family Type',
    'Nuclear' => 'Nuclear',
    'Joint' => 'Joint',
    'Other' => 'Other'
];

include 'includes/header.php'; // Display header
?>

<h2><?php echo $page_title; ?></h2>

<?php if (!empty($errors)): ?>
    <div class="errors" style="color:red; border:1px solid red; padding:10px; margin-bottom:15px;">
        <strong>Please correct the following errors:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <div class="success" style="color:green; border:1px solid green; padding:10px; margin-bottom:15px;">
        <p><?php echo $success_message; // Already htmlspecialchars in construction for query part ?></p>
    </div>
<?php endif; ?>

<form action="profile.php" method="POST" enctype="multipart/form-data">
    <div>
        <label for="profile_photo">Profile Photo:</label>
        <input type="file" name="profile_photo" id="profile_photo">
        <?php if (!empty($profile_data['photo_path'])): ?>
            <p>Current photo: <img src="<?php echo htmlspecialchars($profile_data['photo_path']); ?>" alt="Profile Photo" style="max-width: 150px; max-height: 150px; display:block; margin-top:5px;"></p>
            <small>(Uploading a new photo will replace the current one. Conceptual path: <?php echo htmlspecialchars($profile_data['photo_path']); ?>)</small>
        <?php else: ?>
            <small>(No photo uploaded yet)</small>
        <?php endif; ?>
    </div>

    <div>
        <label for="description">Brief Description (max 1000 chars):</label><br>
        <textarea name="description" id="description" rows="5" cols="60" maxlength="1000"><?php echo htmlspecialchars($profile_data['description'] ?? ''); ?></textarea>
    </div>

    <div>
        <label for="height">Height:</label>
        <input type="text" name="height" id="height" value="<?php echo htmlspecialchars($profile_data['height'] ?? ''); ?>" maxlength="20">
    </div>

    <div>
        <label for="religion">Religion:</label>
        <input type="text" name="religion" id="religion" value="<?php echo htmlspecialchars($profile_data['religion'] ?? ''); ?>" maxlength="50">
    </div>

    <div>
        <label for="caste">Caste (Optional):</label>
        <input type="text" name="caste" id="caste" value="<?php echo htmlspecialchars($profile_data['caste'] ?? ''); ?>" maxlength="50">
    </div>

    <div>
        <label for="education">Education:</label>
        <input type="text" name="education" id="education" value="<?php echo htmlspecialchars($profile_data['education'] ?? ''); ?>" maxlength="100">
    </div>

    <div>
        <label for="occupation">Occupation:</label>
        <input type="text" name="occupation" id="occupation" value="<?php echo htmlspecialchars($profile_data['occupation'] ?? ''); ?>" maxlength="100">
    </div>

    <div>
        <label for="income_range">Annual Income Range:</label>
        <select name="income_range" id="income_range">
            <?php foreach ($income_options as $value => $label): ?>
                <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (isset($profile_data['income_range']) && $profile_data['income_range'] == $value) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="family_type">Family Type:</label>
        <select name="family_type" id="family_type">
             <?php foreach ($family_type_options as $value => $label): ?>
                <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (isset($profile_data['family_type']) && $profile_data['family_type'] == $value) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <button type="submit">Save Profile</button>
    </div>
</form>

<?php include 'includes/footer.php'; // Display footer ?>
