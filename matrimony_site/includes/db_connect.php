<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Placeholder DB username
define('DB_PASS', '');     // Placeholder DB password (empty)
define('DB_NAME', 'matrimony_db');   // Placeholder DB name

// Attempt to connect to MySQL database
// The @ symbol is used to suppress the default PHP error handling,
// allowing custom error handling below.
$db = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (mysqli_connect_errno()) {
    // If there is an error, stop the script and display a generic error message.
    // It's good practice not to reveal detailed connection errors in a production environment.
    die("Failed to connect to the database. Please try again later.");
    // For development, you might want to display the actual error:
    // die("Failed to connect to MySQL: " . mysqli_connect_error());
}

// Optional: Set character set to utf8 (good practice for handling various character encodings)
if ($db) { // Check if $db is a valid resource before using it
    mysqli_set_charset($db, "utf8");
}

// The $db variable can now be used by other scripts to interact with the database.
// PHP will automatically close the connection at the end of script execution
// unless specific circumstances (like long-running scripts or explicit resource management)
// require manual closing using mysqli_close($db).
?>
