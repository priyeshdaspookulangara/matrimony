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
$search_errors = [];

// --- Simulated User Data for Search (Now includes Hobbies) ---
$all_simulated_users = [
    1 => ['id' => 1, 'name' => 'Test User (Logged In)', 'dob' => '1990-01-01', 'gender' => 'Male',
          'religion' => 'Agnostic', 'caste' => 'N/A', 'education' => 'PhD in Computer Science',
          'photo_path' => 'uploads/user_1_photo.jpg', 'is_approved' => 1,
          'description' => 'Loves coding.',
          'birth_star' => 'Rohini', 'birth_place' => 'New Delhi, India',
          'hobbies' => 'Reading, Traveling, Photography, Coding'],
    2 => ['id' => 2, 'name' => 'Jane Doe', 'dob' => '1992-05-15', 'gender' => 'Female',
          'religion' => 'Spiritual', 'caste' => 'Does not believe in caste', 'education' => 'Masters in Arts',
          'photo_path' => 'uploads/user_2_photo.jpg', 'is_approved' => 1,
          'description' => 'Enjoys reading and hiking.',
          'birth_star' => 'Ashwini', 'birth_place' => 'Mumbai, India',
          'hobbies' => 'Hiking, Painting, Yoga, Reading'],
    3 => ['id' => 3, 'name' => 'Pending Approval User', 'dob' => '1995-10-20', 'gender' => 'Other',
          'religion' => 'Unknown', 'caste' => 'None', 'education' => 'Bachelors',
          'photo_path' => '', 'is_approved' => 0,
          'description' => 'Awaiting approval.',
          'birth_star' => 'Bharani', 'birth_place' => 'Chennai, India',
          'hobbies' => 'Video Games, Sketching'], // Should be filtered out by is_approved
    4 => ['id' => 4, 'name' => 'John Smith', 'dob' => '1985-03-10', 'gender' => 'Male',
          'religion' => 'Hindu', 'caste' => 'Brahmin', 'education' => 'Masters Degree',
          'photo_path' => 'uploads/user_4_photo.jpg', 'is_approved' => 1,
          'description' => 'Looking for a life partner.',
          'birth_star' => 'Krittika', 'birth_place' => 'New Delhi, India',
          'hobbies' => 'Cricket, Movies, Politics'],
    5 => ['id' => 5, 'name' => 'Maria Garcia', 'dob' => '1998-07-22', 'gender' => 'Female',
          'religion' => 'Christian', 'caste' => 'Catholic', 'education' => 'High School',
          'photo_path' => 'uploads/user_5_photo.jpg', 'is_approved' => 1,
          'description' => 'Simple and down to earth.',
          'birth_star' => 'Rohini', 'birth_place' => 'Goa, India',
          'hobbies' => 'Cooking, Gardening, Church activities'],
    6 => ['id' => 6, 'name' => 'Amit Patel', 'dob' => '1991-11-05', 'gender' => 'Male',
          'religion' => 'Hindu', 'caste' => 'Patel', 'education' => 'MBA',
          'photo_path' => '', 'is_approved' => 1,
          'description' => 'Business professional.',
          'birth_star' => 'Mrigashira', 'birth_place' => 'Ahmedabad, Gujarat',
          'hobbies' => 'Stock Trading, Gym, Networking'],
    7 => ['id' => 7, 'name' => 'Sophia Lee', 'dob' => '1988-09-12', 'gender' => 'Female',
          'religion' => 'Buddhist', 'caste' => 'N/A', 'education' => 'PhD in Physics',
          'photo_path' => 'uploads/user_7_photo.jpg', 'is_approved' => 1,
          'description' => 'Loves science and nature.',
          'birth_star' => 'Ardra', 'birth_place' => 'Gangtok, Sikkim',
          'hobbies' => 'Trekking, Meditation, Writing, Star Gazing'],
    8 => ['id' => 8, 'name' => 'Mohammed Ali', 'dob' => '1995-02-28', 'gender' => 'Male',
          'religion' => 'Muslim', 'caste' => 'Sunni', 'education' => 'Software Engineer',
          'photo_path' => 'uploads/user_8_photo.jpg', 'is_approved' => 1,
          'description' => 'Tech enthusiast.',
          'birth_star' => 'Punarvasu', 'birth_place' => 'Hyderabad, India',
          'hobbies' => 'Blogging, Open Source, Cycling, Calligraphy'],
];


// --- Handle Form Submission (GET request) ---
$form_submitted = !empty($_GET);

if ($form_submitted) {
    // Retrieve and sanitize search parameters
    $min_age = isset($_GET['min_age']) && $_GET['min_age'] !== '' ? (int)$_GET['min_age'] : null;
    $max_age = isset($_GET['max_age']) && $_GET['max_age'] !== '' ? (int)$_GET['max_age'] : null;
    $gender = $_GET['gender'] ?? '';
    $religion = $_GET['religion'] ?? '';
    $caste = $_GET['caste'] ?? '';
    $education = $_GET['education'] ?? '';
    $search_birth_star = $_GET['birth_star'] ?? '';
    $search_birth_place = $_GET['birth_place'] ?? '';
    // Hobbies search criteria will be added in a subsequent step, not filtered here yet.

    // --- Simulate Fetching and Filtering Data ---
    foreach ($all_simulated_users as $user) {
        if ($user['is_approved'] != 1 || $user['id'] == $current_user_id) {
            continue;
        }

        $age = calculate_age($user['dob']);

        // Apply filters
        if ($min_age !== null && $age < $min_age) continue;
        if ($max_age !== null && $age > $max_age) continue;
        if (!empty($gender) && strtolower($user['gender']) !== strtolower($gender)) continue;
        if (!empty($religion) && (empty($user['religion']) || stripos($user['religion'], $religion) === false)) continue;
        if (!empty($caste) && (empty($user['caste']) || stripos($user['caste'], $caste) === false)) continue;
        if (!empty($education) && (empty($user['education']) || stripos($user['education'], $education) === false)) continue;
        if (!empty($search_birth_star) && (empty($user['birth_star']) || stripos($user['birth_star'], $search_birth_star) === false)) continue;
        if (!empty($search_birth_place) && (empty($user['birth_place']) || stripos($user['birth_place'], $search_birth_place) === false)) continue;

        $search_results[] = $user;
    }
}

include 'includes/header.php';
?>

<h2>Search Profiles</h2>

<form action="search.php" method="GET" class="search-form" style="margin-bottom: 20px; padding:15px; border:1px solid #ddd; background-color:#f9f9f9;">
    <div style="display:flex; flex-wrap:wrap; gap:15px;">
        <div>
            <label for="min_age">Min Age:</label><br>
            <input type="number" name="min_age" id="min_age" min="18" max="100" value="<?php echo sanitize_output($_GET['min_age'] ?? ''); ?>" style="width:80px; padding:8px; box-sizing: border-box;">
        </div>
        <div>
            <label for="max_age">Max Age:</label><br>
            <input type="number" name="max_age" id="max_age" min="18" max="100" value="<?php echo sanitize_output($_GET['max_age'] ?? ''); ?>" style="width:80px; padding:8px; box-sizing: border-box;">
        </div>
        <div>
            <label for="gender">Gender:</label><br>
            <select name="gender" id="gender" style="padding:8px; box-sizing: border-box;">
                <option value="" <?php echo (empty($_GET['gender'])) ? 'selected' : ''; ?>>Any</option>
                <option value="Male" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div>
            <label for="religion">Religion:</label><br>
            <input type="text" name="religion" id="religion" value="<?php echo sanitize_output($_GET['religion'] ?? ''); ?>" style="padding:8px; box-sizing: border-box;">
        </div>
        <div>
            <label for="caste">Caste:</label><br>
            <input type="text" name="caste" id="caste" value="<?php echo sanitize_output($_GET['caste'] ?? ''); ?>" style="padding:8px; box-sizing: border-box;">
        </div>
        <div>
            <label for="education">Education:</label><br>
            <input type="text" name="education" id="education" value="<?php echo sanitize_output($_GET['education'] ?? ''); ?>" style="padding:8px; box-sizing: border-box;">
        </div>
        <div>
            <label for="search_birth_star">Birth Star:</label><br>
            <input type="text" name="birth_star" id="search_birth_star" value="<?php echo sanitize_output($_GET['birth_star'] ?? ''); ?>" style="padding:8px; box-sizing: border-box;">
        </div>
        <div>
            <label for="search_birth_place">Birth Place:</label><br>
            <input type="text" name="birth_place" id="search_birth_place" value="<?php echo sanitize_output($_GET['birth_place'] ?? ''); ?>" style="padding:8px; box-sizing: border-box;">
        </div>
        <!-- Hobbies search input will be added in a subsequent step -->
    </div>
    <div style="margin-top:20px;">
        <button type="submit" style="padding:10px 20px; background-color:#007bff; color:white; border:none; border-radius:4px; cursor:pointer;">Search</button>
        <a href="search.php" style="padding:10px 20px; text-decoration:none; color:#333; margin-left:10px; background-color:#eee; border:1px solid #ccc; border-radius:4px;">Clear Search</a>
    </div>
</form>

<h3>Search Results</h3>
<?php if ($form_submitted): ?>
    <?php if (!empty($search_results)): ?>
        <p>Found <?php echo count($search_results); ?> profile(s) matching your criteria.</p>
        <div class="search-results-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
            <?php foreach ($search_results as $result): ?>
                <div class="profile-card" style="border: 1px solid #ccc; padding: 15px; border-radius: 5px; background-color: #fff; box-shadow: 2px 2px 5px rgba(0,0,0,0.05);">
                    <?php
                    $photo_display_path = !empty($result['photo_path']) ? sanitize_output($result['photo_path']) : 'images/default_avatar.png';
                    ?>
                    <img src="<?php echo $photo_display_path; ?>" alt="Photo of <?php echo sanitize_output($result['name']); ?>" style="width: 100%; max-width:120px; height: 120px; object-fit:cover; border-radius: 50%; margin-bottom: 10px; display:block; margin-left:auto; margin-right:auto; border:2px solid #eee;">
                    <h4 style="text-align:center; margin-bottom:5px;"><a href="view_profile.php?id=<?php echo $result['id']; ?>"><?php echo sanitize_output($result['name']); ?></a></h4>
                    <p style="font-size:0.9em; text-align:center; color:#555; margin-top:0;">Age: <?php echo calculate_age($result['dob']); ?> | Gender: <?php echo sanitize_output($result['gender']); ?></p>
                    <p style="font-size:0.9em;"><strong>Religion:</strong> <?php echo sanitize_output($result['religion'] ?? 'N/A'); ?></p>
                    <p style="font-size:0.9em;"><strong>Education:</strong> <?php echo sanitize_output($result['education'] ?? 'N/A'); ?></p>
                    <p style="font-size:0.9em;"><strong>Birth Star:</strong> <?php echo sanitize_output($result['birth_star'] ?? 'N/A'); ?></p>
                    <p style="font-size:0.9em;"><strong>Birth Place:</strong> <?php echo sanitize_output($result['birth_place'] ?? 'N/A'); ?></p>
                    <!-- Hobbies display in results will be added in a subsequent step -->
                    <p style="font-size:0.85em; color:#777; height: 40px; overflow:hidden;"><?php echo substr(sanitize_output($result['description'] ?? ''), 0, 60); ?>...</p>
                    <a href="view_profile.php?id=<?php echo $result['id']; ?>" style="display:block; margin-top:10px; padding:8px 10px; background-color:#5cb85c; color:white; text-decoration:none; border-radius:3px; text-align:center;">View Full Profile</a>
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
