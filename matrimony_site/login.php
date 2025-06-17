<?php
session_start();
@include_once 'includes/db_connect.php'; // $db connection will be available here, or null/false if connection failed
@include_once 'includes/functions.php';   // For future helper functions

$errors = [];
$email = ''; // For repopulating form

// Check if user is already logged in, if so, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Display logout message if present
$info_message = '';
if (isset($_GET['message']) && $_GET['message'] === 'logged_out') {
    $info_message = 'You have been successfully logged out.';
}
if (isset($_GET['message']) && $_GET['message'] === 'login_required') {
    $info_message = 'Please login to access that page.';
}
if (isset($_GET['message']) && $_GET['message'] === 'session_hijacked') {
    $info_message = 'Session terminated for security reasons. Please login again.';
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $escaped_email = '';
        // Sanitize email for database query
        if (isset($db) && $db instanceof mysqli && !mysqli_connect_errno()) {
            $escaped_email = mysqli_real_escape_string($db, $email);
        } else {
            // If DB connection is not valid, we cannot safely query.
            // For this simulation, we might proceed if it's the test user,
            // but in production, this should be a hard error.
            // $errors[] = "Database connection error. Cannot proceed with login.";
            // Fallback for simulation if db is not available:
            $escaped_email = $email; // Not safe for real DB query if $email contains special chars
        }

        // --- SIMULATE DATABASE FETCH ---
        $user_found = false;
        $simulated_user_data = null;

        // Define the "test" user
        $test_user_email = 'test@example.com';
        $test_user_password_plain = 'password123';
        // Hash the test user's password to simulate what's stored in DB
        $test_user_hashed_password = password_hash($test_user_password_plain, PASSWORD_DEFAULT);

        if ($escaped_email === $test_user_email) {
            // User "found" by email, now verify password
            if (password_verify($password, $test_user_hashed_password)) {
                // Password matches
                $simulated_user_data = [
                    'id' => 1,
                    'name' => 'Test User',
                    'email' => $test_user_email,
                    'password' => $test_user_hashed_password, // This is the hashed one
                    'is_approved' => 1 // 1 for approved, 0 for pending
                ];
                $user_found = true;
            }
        }
        // --- END SIMULATE DATABASE FETCH ---

        if ($user_found) {
            if ($simulated_user_data['is_approved'] == 1) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Store user data in session
                $_SESSION['user_id'] = $simulated_user_data['id'];
                $_SESSION['user_name'] = $simulated_user_data['name'];
                $_SESSION['user_email'] = $simulated_user_data['email'];
                $_SESSION['user_agent_snapshot'] = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 50);


                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "Your account is pending approval. Please contact an administrator.";
            }
        } else {
            // Only add this error if no DB connection error occurred earlier preventing the check
            if (!in_array("Database connection error. Cannot proceed with login.", $errors)) {
                 $errors[] = "Invalid email or password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Matrimony Site</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .errors { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; }
        .info { color: blue; border: 1px solid blue; padding: 10px; margin-bottom: 15px; }
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        form div { margin-bottom: 10px; }
        label { display: inline-block; width: 100px; }
        input[type="email"], input[type="password"] {
            width: 250px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;
        }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <header>
        <h1>Matrimony Site</h1>
        <nav>
            <a href="index.php">Home</a> |
            <a href="login.php">Login</a> |
            <a href="register.php">Register</a>
        </nav>
    </header>

    <main>
        <h2>Login</h2>

        <?php if (!empty($info_message)): ?>
            <div class="info">
                <p><?php echo htmlspecialchars($info_message); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <strong>Login failed:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div>
                <button type="submit">Login</button>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Matrimony Site</p>
    </footer>

</body>
</html>
