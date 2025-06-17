<?php
session_start();
@include_once 'includes/db_connect.php'; // $db (conceptual)
@include_once 'includes/functions.php';   // sanitize_output()

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=login_required");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = "Manage Partner Preferences";

$success_message = '';
$errors = [];

// Initialize user preferences data storage in session if not set
if (!isset($_SESSION['user_preferences_data'])) {
    $_SESSION['user_preferences_data'] = [];
}

// Fetch existing preferences for the current user (simulated)
// Uses an empty array as default if no preferences are saved for the user ID
$current_preferences = $_SESSION['user_preferences_data'][$user_id] ?? [];

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve preference fields from $_POST
    $pref_min_age = !empty($_POST['pref_min_age']) ? (int)$_POST['pref_min_age'] : null;
    $pref_max_age = !empty($_POST['pref_max_age']) ? (int)$_POST['pref_max_age'] : null;
    $pref_min_height = $_POST['pref_min_height'] ?? '';
    $pref_max_height = $_POST['pref_max_height'] ?? '';
    $pref_religion = $_POST['pref_religion'] ?? '';
    $pref_caste = $_POST['pref_caste'] ?? '';
    $pref_education = $_POST['pref_education'] ?? '';
    $pref_occupation = $_POST['pref_occupation'] ?? '';
    $pref_birth_star = $_POST['pref_birth_star'] ?? '';
    $pref_skin_complexion = $_POST['pref_skin_complexion'] ?? '';
    $pref_district = $_POST['pref_district'] ?? '';
    $pref_family_type = $_POST['pref_family_type'] ?? '';

    // Basic Validation
    if ($pref_min_age !== null && $pref_max_age !== null && $pref_min_age > $pref_max_age) {
        $errors[] = "Preferred minimum age cannot be greater than maximum age.";
    }
    // Further age boundary checks
    if ($pref_min_age !== null && ($pref_min_age < 18 || $pref_min_age > 100)) {
        $errors[] = "Preferred minimum age must be between 18 and 100.";
    }
    if ($pref_max_age !== null && ($pref_max_age < 18 || $pref_max_age > 100)) {
        $errors[] = "Preferred maximum age must be between 18 and 100.";
    }
    // Basic length validation for text areas
    $text_fields_to_validate = [
        'Religion' => $pref_religion, 'Caste' => $pref_caste, 'Education' => $pref_education,
        'Occupation' => $pref_occupation, 'Birth Star' => $pref_birth_star,
        'Skin Complexion' => $pref_skin_complexion, 'District' => $pref_district,
        'Min Height' => $pref_min_height, 'Max Height' => $pref_max_height
    ];
    foreach ($text_fields_to_validate as $field_name => $value) {
        if (strlen($value) > 255) {
            $errors[] = "Preferred {$field_name} input is too long (max 255 characters).";
        }
    }


    if (empty($errors)) {
        $submitted_preferences = [
            'pref_min_age' => $pref_min_age,
            'pref_max_age' => $pref_max_age,
            'pref_min_height' => $pref_min_height,
            'pref_max_height' => $pref_max_height,
            'pref_religion' => $pref_religion,
            'pref_caste' => $pref_caste,
            'pref_education' => $pref_education,
            'pref_occupation' => $pref_occupation,
            'pref_birth_star' => $pref_birth_star,
            'pref_skin_complexion' => $pref_skin_complexion,
            'pref_district' => $pref_district,
            'pref_family_type' => $pref_family_type,
        ];

        // Save Preferences (Simulated)
        $_SESSION['user_preferences_data'][$user_id] = $submitted_preferences;
        $current_preferences = $submitted_preferences; // Update current view
        $success_message = "Preferences saved successfully!";
    }
}

$family_type_options = ['' => 'Any', 'Nuclear' => 'Nuclear', 'Joint' => 'Joint', 'Other' => 'Other'];

include 'includes/header.php';
?>

<h2><?php echo sanitize_output($page_title); ?></h2>

<?php if (!empty($success_message)): ?>
    <div class="message success" style="padding:10px; margin-bottom:15px; border:1px solid green; background-color:#d4edda; color:#155724; border-radius:4px;">
        <?php echo sanitize_output($success_message); ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="message error" style="padding:10px; margin-bottom:15px; border:1px solid red; background-color:#f8d7da; color:#721c24; border-radius:4px;">
        <strong>Please correct the following errors:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo sanitize_output($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="manage_preferences.php" method="POST" class="preferences-form">
    <fieldset>
        <legend>Age & Height Preferences</legend>
        <div>
            <label for="pref_min_age">Minimum Age (18-100):</label>
            <input type="number" name="pref_min_age" id="pref_min_age" min="18" max="100" value="<?php echo sanitize_output($current_preferences['pref_min_age'] ?? ''); ?>">
        </div>
        <div>
            <label for="pref_max_age">Maximum Age (18-100):</label>
            <input type="number" name="pref_max_age" id="pref_max_age" min="18" max="100" value="<?php echo sanitize_output($current_preferences['pref_max_age'] ?? ''); ?>">
        </div>
        <div>
            <label for="pref_min_height">Minimum Height (e.g., 5ft 2in):</label>
            <input type="text" name="pref_min_height" id="pref_min_height" value="<?php echo sanitize_output($current_preferences['pref_min_height'] ?? ''); ?>" maxlength="20">
        </div>
        <div>
            <label for="pref_max_height">Maximum Height (e.g., 6ft):</label>
            <input type="text" name="pref_max_height" id="pref_max_height" value="<?php echo sanitize_output($current_preferences['pref_max_height'] ?? ''); ?>" maxlength="20">
        </div>
    </fieldset>

    <fieldset>
        <legend>Community & Background Preferences</legend>
        <div>
            <label for="pref_religion">Religion(s) (comma-separated):</label>
            <textarea name="pref_religion" id="pref_religion" rows="2"><?php echo sanitize_output($current_preferences['pref_religion'] ?? ''); ?></textarea>
        </div>
        <div>
            <label for="pref_caste">Caste(s) (comma-separated):</label>
            <textarea name="pref_caste" id="pref_caste" rows="2"><?php echo sanitize_output($current_preferences['pref_caste'] ?? ''); ?></textarea>
        </div>
        <div>
            <label for="pref_birth_star">Birth Star(s) (Nakshatra, comma-separated):</label>
            <textarea name="pref_birth_star" id="pref_birth_star" rows="2"><?php echo sanitize_output($current_preferences['pref_birth_star'] ?? ''); ?></textarea>
        </div>
    </fieldset>

    <fieldset>
        <legend>Education & Career Preferences</legend>
        <div>
            <label for="pref_education">Education(s) (comma-separated):</label>
            <textarea name="pref_education" id="pref_education" rows="2"><?php echo sanitize_output($current_preferences['pref_education'] ?? ''); ?></textarea>
        </div>
        <div>
            <label for="pref_occupation">Occupation(s) (comma-separated):</label>
            <textarea name="pref_occupation" id="pref_occupation" rows="2"><?php echo sanitize_output($current_preferences['pref_occupation'] ?? ''); ?></textarea>
        </div>
    </fieldset>

    <fieldset>
        <legend>Other Preferences</legend>
        <div>
            <label for="pref_skin_complexion">Skin Complexion(s) (comma-separated):</label>
            <textarea name="pref_skin_complexion" id="pref_skin_complexion" rows="2"><?php echo sanitize_output($current_preferences['pref_skin_complexion'] ?? ''); ?></textarea>
        </div>
         <div>
            <label for="pref_district">Preferred District(s) (comma-separated):</label>
            <textarea name="pref_district" id="pref_district" rows="2"><?php echo sanitize_output($current_preferences['pref_district'] ?? ''); ?></textarea>
        </div>
        <div>
            <label for="pref_family_type">Family Type:</label>
            <select name="pref_family_type" id="pref_family_type">
                <?php foreach ($family_type_options as $value => $label): ?>
                    <option value="<?php echo sanitize_output($value); ?>" <?php echo (isset($current_preferences['pref_family_type']) && $current_preferences['pref_family_type'] == $value) ? 'selected' : ''; ?>>
                        <?php echo sanitize_output($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>

    <div style="margin-top: 20px;">
        <button type="submit">Save Preferences</button>
    </div>
</form>

<style>
    .preferences-form fieldset { margin-bottom: 20px; border: 1px solid #ccc; padding: 15px; border-radius: 5px; }
    .preferences-form legend { font-weight: bold; color: #333; }
    .preferences-form div { margin-bottom: 10px; }
    .preferences-form label { display: block; margin-bottom: 5px; font-weight: normal; }
    .preferences-form input[type="number"],
    .preferences-form input[type="text"],
    .preferences-form textarea,
    .preferences-form select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
    .preferences-form textarea { min-height: 60px; }
    .preferences-form button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
    .preferences-form button:hover { background-color: #0056b3; }
</style>

<?php include 'includes/footer.php'; ?>
