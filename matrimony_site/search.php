<?php
session_start();
@include_once 'includes/db_connect.php'; // $db (conceptual)
@include_once 'includes/functions.php';   // calculate_age(), sanitize_output()

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=login_required");
    exit();
}
$current_user_id = $_SESSION['user_id']; // To exclude from results

$page_title = "Search Profiles";
$search_results = [];
$search_errors = []; // Not actively used yet, but good for future validation

// --- Simulated User Data for Search ---
// This array acts as our "database" for this page.
$all_simulated_users = [
    1 => ['id' => 1, 'name' => 'Test User (Logged In)', 'dob' => '1990-01-01', 'gender' => 'Male', 'religion' => 'Agnostic', 'caste' => 'N/A', 'education' => 'PhD in Computer Science', 'photo_path' => 'uploads/user_1_photo.jpg', 'is_approved' => 1, 'description' => 'Loves coding.'],
    2 => ['id' => 2, 'name' => 'Jane Doe', 'dob' => '1992-05-15', 'gender' => 'Female', 'religion' => 'Spiritual', 'caste' => 'Does not believe in caste', 'education' => 'Masters in Arts', 'photo_path' => 'uploads/user_2_photo.jpg', 'is_approved' => 1, 'description' => 'Enjoys reading and hiking.'],
    3 => ['id' => 3, 'name' => 'Pending Approval User', 'dob' => '1995-10-20', 'gender' => 'Other', 'religion' => 'Unknown', 'caste' => 'None', 'education' => 'Bachelors', 'photo_path' => '', 'is_approved' => 0, 'description' => 'Awaiting approval.'], // Should be filtered out
    4 => ['id' => 4, 'name' => 'John Smith', 'dob' => '1985-03-10', 'gender' => 'Male', 'religion' => 'Hindu', 'caste' => 'Brahmin', 'education' => 'Masters Degree', 'photo_path' => 'uploads/user_4_photo.jpg', 'is_approved' => 1, 'description' => 'Looking for a life partner.'],
    5 => ['id' => 5, 'name' => 'Maria Garcia', 'dob' => '1998-07-22', 'gender' => 'Female', 'religion' => 'Christian', 'caste' => 'Catholic', 'education' => 'High School', 'photo_path' => 'uploads/user_5_photo.jpg', 'is_approved' => 1, 'description' => 'Simple and down to earth.'],
    6 => ['id' => 6, 'name' => 'Amit Patel', 'dob' => '1991-11-05', 'gender' => 'Male', 'religion' => 'Hindu', 'caste' => 'Patel', 'education' => 'MBA', 'photo_path' => '', 'is_approved' => 1, 'description' => 'Business professional.'],
    7 => ['id' => 7, 'name' => 'Sophia Lee', 'dob' => '1988-09-12', 'gender' => 'Female', 'religion' => 'Buddhist', 'caste' => 'N/A', 'education' => 'PhD in Physics', 'photo_path' => 'uploads/user_7_photo.jpg', 'is_approved' => 1, 'description' => 'Loves science and nature.'],
    8 => ['id' => 8, 'name' => 'Mohammed Ali', 'dob' => '1995-02-28', 'gender' => 'Male', 'religion' => 'Muslim', 'caste' => 'Sunni', 'education' => 'Software Engineer', 'photo_path' => 'uploads/user_8_photo.jpg', 'is_approved' => 1, 'description' => 'Tech enthusiast.'],
];


// --- Handle Form Submission (GET request) ---
$form_submitted = !empty($_GET); // Check if any GET parameters are present

if ($form_submitted) {
    // Retrieve and sanitize search parameters
    $min_age = isset($_GET['min_age']) && $_GET['min_age'] !== '' ? (int)$_GET['min_age'] : null;
    $max_age = isset($_GET['max_age']) && $_GET['max_age'] !== '' ? (int)$_GET['max_age'] : null;
    $gender = $_GET['gender'] ?? '';
    $religion = $_GET['religion'] ?? '';
    $caste = $_GET['caste'] ?? '';
    $education = $_GET['education'] ?? '';

    // --- Simulate Fetching and Filtering Data ---
    foreach ($all_simulated_users as $user) {
        // Skip non-approved users or the current logged-in user
        if ($user['is_approved'] != 1 || $user['id'] == $current_user_id) {
            continue;
        }

        $age = calculate_age($user['dob']);

        // Apply filters
        if ($min_age !== null && $age < $min_age) {
            continue;
        }
        if ($max_age !== null && $age > $max_age) {
            continue;
        }
        if (!empty($gender) && strtolower($user['gender']) !== strtolower($gender)) {
            continue;
        }
        // Using stripos for case-insensitive partial match for text fields
        if (!empty($religion) && (empty($user['religion']) || stripos($user['religion'], $religion) === false)) {
            continue;
        }
        if (!empty($caste) && (empty($user['caste']) || stripos($user['caste'], $caste) === false)) {
            continue;
        }
        if (!empty($education) && (empty($user['education']) || stripos($user['education'], $education) === false)) {
            continue;
        }

        // If all criteria passed, add to results
        $search_results[] = $user;
    }
}

include 'includes/header.php';
?>

<h2>Search Profiles</h2>

<form action="search.php" method="GET" class="search-form" style="margin-bottom: 20px; padding:15px; border:1px solid #ddd; background-color:#f9f9f9;">
    <div style="display:flex; flex-wrap:wrap; gap:10px;">
        <div>
            <label for="min_age">Min Age:</label><br>
            <input type="number" name="min_age" id="min_age" min="18" max="100" value="<?php echo sanitize_output($_GET['min_age'] ?? ''); ?>" style="width:80px; padding:5px;">
        </div>
        <div>
            <label for="max_age">Max Age:</label><br>
            <input type="number" name="max_age" id="max_age" min="18" max="100" value="<?php echo sanitize_output($_GET['max_age'] ?? ''); ?>" style="width:80px; padding:5px;">
        </div>
        <div>
            <label for="gender">Gender:</label><br>
            <select name="gender" id="gender" style="padding:5px;">
                <option value="" <?php echo (empty($_GET['gender'])) ? 'selected' : ''; ?>>Any</option>
                <option value="Male" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div>
            <label for="religion">Religion:</label><br>
            <input type="text" name="religion" id="religion" value="<?php echo sanitize_output($_GET['religion'] ?? ''); ?>" style="padding:5px;">
        </div>
        <div>
            <label for="caste">Caste:</label><br>
            <input type="text" name="caste" id="caste" value="<?php echo sanitize_output($_GET['caste'] ?? ''); ?>" style="padding:5px;">
        </div>
        <div>
            <label for="education">Education:</label><br>
            <input type="text" name="education" id="education" value="<?php echo sanitize_output($_GET['education'] ?? ''); ?>" style="padding:5px;">
        </div>
    </div>
    <div style="margin-top:15px;">
        <button type="submit" style="padding:8px 15px; background-color:#007bff; color:white; border:none; border-radius:4px; cursor:pointer;">Search</button>
        <a href="search.php" style="padding:8px 15px; text-decoration:none; color:#333; margin-left:10px;">Clear Search</a>
    </div>
</form>

<h3>Search Results</h3>
<?php if ($form_submitted): ?>
    <?php if (!empty($search_results)): ?>
        <p>Found <?php echo count($search_results); ?> profile(s) matching your criteria.</p>
        <div class="search-results-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
            <?php foreach ($search_results as $result): ?>
                <div class="profile-card" style="border: 1px solid #ccc; padding: 15px; border-radius: 5px; background-color: #fff;">
                    <?php
                    $photo_display_path = !empty($result['photo_path']) ? sanitize_output($result['photo_path']) : 'images/default_avatar.png';
                    // For conceptual paths, we might need to adjust if they are not directly servable
                    // In a real app, ensure 'uploads/' paths are correct relative to the web root.
                    ?>
                    <img src="<?php echo $photo_display_path; ?>" alt="Photo of <?php echo sanitize_output($result['name']); ?>" style="width: 100%; max-width:150px; height: auto; max-height:150px; object-fit:cover; border-radius: 4px; margin-bottom: 10px; display:block; margin-left:auto; margin-right:auto;">
                    <h4><a href="view_profile.php?id=<?php echo $result['id']; ?>"><?php echo sanitize_output($result['name']); ?></a></h4>
                    <p><strong>Age:</strong> <?php echo calculate_age($result['dob']); ?> years</p>
                    <p><strong>Gender:</strong> <?php echo sanitize_output($result['gender']); ?></p>
                    <p><strong>Religion:</strong> <?php echo sanitize_output($result['religion'] ?? 'N/A'); ?></p>
                    <p><strong>Education:</strong> <?php echo sanitize_output($result['education'] ?? 'N/A'); ?></p>
                    <p style="font-size:0.9em; color:#555;"><?php echo substr(sanitize_output($result['description'] ?? ''), 0, 70); ?>...</p>
                    <a href="view_profile.php?id=<?php echo $result['id']; ?>" style="display:inline-block; margin-top:10px; padding:5px 10px; background-color:#5cb85c; color:white; text-decoration:none; border-radius:3px;">View Full Profile</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No matching profiles found for your criteria. Please try different search terms.</p>
    <?php endif; ?>
<?php else: ?>
    <p>Please enter your search criteria above to find profiles.</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
