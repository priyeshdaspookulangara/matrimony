<?php
session_start();
// Attempt to include db_connect.php. The @ suppresses errors if the file is not found,
// but we'll handle the $db check later.
@include_once 'includes/db_connect.php';
@include_once 'includes/functions.php'; // For future helper functions

$errors = [];
$success_message = '';

// Default values for form fields to prevent errors if POST data is not set
$name = '';
$email = '';
$gender = '';
$dob = '';
$mobile = '';
// Note: We don't repopulate password fields for security reasons.

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve all form data from $_POST, using null coalescing operator for safety
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $mobile = $_POST['mobile'] ?? '';

    // Perform server-side validation
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    if (empty($confirm_password)) {
        $errors[] = "Confirm Password is required.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (empty($gender)) {
        $errors[] = "Gender is required.";
    }
    if (empty($dob)) {
        $errors[] = "Date of Birth is required.";
    }
    if (empty($mobile)) {
        $errors[] = "Mobile number is required.";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $mobile)) { // Basic validation for 10-15 digits
        $errors[] = "Invalid mobile number format (should be 10-15 digits).";
    }

    // Input Sanitization (Conceptual) and further processing if no validation errors
    if (empty($errors)) {
        // Check if $db is available and is a valid mysqli connection object
        // mysqli_connect_errno() would only be relevant if an *attempt* to connect was made and failed.
        // $db might be null if db_connect.php had a fatal error or was not found.
        if (isset($db) && $db instanceof mysqli && !mysqli_connect_errno()) {
            $escaped_name = mysqli_real_escape_string($db, $name);
            $escaped_email = mysqli_real_escape_string($db, $email);
            // Sanitize other string inputs similarly if they were to be used in a query
            $escaped_gender = mysqli_real_escape_string($db, $gender);
            $escaped_dob = mysqli_real_escape_string($db, $dob); // DOB is generally safe from date input, but good practice
            $escaped_mobile = mysqli_real_escape_string($db, $mobile);
        } else {
            // Handle case where DB connection is not available or not valid
            $errors[] = "Database connection error. Cannot sanitize input. Registration aborted.";
            // For demonstration, we'll create placeholder escaped variables if $db is not valid,
            // but this is NOT safe for production. In a real app, we'd stop here.
            if (!isset($db) || !$db instanceof mysqli) {
                 // $errors[] = "Debug: DB connection object (\$db) is not a valid mysqli instance.";
            }
            if (mysqli_connect_errno()){
                // $errors[] = "Debug: DB connection error: " . mysqli_connect_error();
            }

            $escaped_name = htmlspecialchars($name); // Fallback, not for SQL
            $escaped_email = htmlspecialchars($email); // Fallback, not for SQL
            $escaped_gender = htmlspecialchars($gender);
            $escaped_dob = htmlspecialchars($dob);
            $escaped_mobile = htmlspecialchars($mobile);
        }

        // Proceed only if DB sanitization didn't add a fatal error
        if (!in_array("Database connection error. Cannot sanitize input. Registration aborted.", $errors)) {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Construct the INSERT SQL query string (Conceptual - DO NOT EXECUTE)
            // Note: In a real query, ensure all values are correctly quoted if they are strings.
            $query = "INSERT INTO users (name, email, password, gender, dob, mobile_number, registration_date) VALUES (";
            $query .= "'$escaped_name', ";
            $query .= "'$escaped_email', ";
            $query .= "'$hashed_password', "; // Store the hashed password
            $query .= "'$escaped_gender', ";
            $query .= "'$escaped_dob', ";
            $query .= "'$escaped_mobile', ";
            $query .= "NOW()"; // Assuming registration_date is set to current timestamp
            $query .= ");";

            // Simulate success (since we are not executing the query)
            $success_message = "Registration successful (simulation)! Query for verification: " . htmlspecialchars($query);
            // Clear form fields on success
            $name = $email = $gender = $dob = $mobile = '';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration - Matrimony Site</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .errors { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; }
        .success { color: green; border: 1px solid green; padding: 10px; margin-bottom: 15px; }
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        form div { margin-bottom: 10px; }
        label { display: inline-block; width: 150px; }
        input[type="text"], input[type="email"], input[type="password"], input[type="date"], input[type="tel"] {
            width: 250px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;
        }
        input[type="radio"] { margin-right: 5px; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>

    <header>
        <h1>Matrimony Site</h1>
        <!-- Basic navigation example -->
        <nav>
            <a href="index.php">Home</a> |
            <a href="login.php">Login</a> |
            <a href="register.php">Register</a>
        </nav>
    </header>

    <main>
        <h2>User Registration</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <strong>Please correct the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success">
                <p><?php echo $success_message; // Already HTML-escaped if it contains the query string ?></p>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" novalidate>
            <div>
                <label for="name">Full Name:</label>
                <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($name); ?>">
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div>
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <div>
                <label>Gender:</label>
                <input type="radio" name="gender" id="male" value="Male" <?php if ($gender === 'Male') echo 'checked'; ?> required> <label for="male" style="width:auto;">Male</label>
                <input type="radio" name="gender" id="female" value="Female" <?php if ($gender === 'Female') echo 'checked'; ?>> <label for="female" style="width:auto;">Female</label>
                <input type="radio" name="gender" id="other" value="Other" <?php if ($gender === 'Other') echo 'checked'; ?>> <label for="other" style="width:auto;">Other</label>
            </div>
            <div>
                <label for="dob">Date of Birth:</label>
                <input type="date" name="dob" id="dob" required value="<?php echo htmlspecialchars($dob); ?>">
            </div>
            <div>
                <label for="mobile">Mobile Number:</label>
                <input type="tel" name="mobile" id="mobile" required pattern="[0-9]{10,15}" title="Mobile number should be 10-15 digits." value="<?php echo htmlspecialchars($mobile); ?>">
            </div>
            <div>
                <button type="submit">Register</button>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Matrimony Site</p>
    </footer>

</body>
</html>
