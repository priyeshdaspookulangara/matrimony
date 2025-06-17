<?php
// matrimony_site/includes/functions.php

/**
 * Calculates age based on a date of birth string.
 *
 * @param string $dob_string Date of birth in a format recognizable by DateTime (e.g., YYYY-MM-DD).
 * @return int|string The calculated age in years, or 'N/A' if DOB is invalid or empty.
 */
function calculate_age($dob_string) {
    if (empty($dob_string)) {
        return 'N/A';
    }
    try {
        $dob = new DateTime($dob_string);
        $now = new DateTime();
        $difference = $now->diff($dob);
        return $difference->y;
    } catch (Exception $e) {
        // Log error or handle appropriately if date format is consistently wrong
        return 'N/A'; // Invalid date format
    }
}

/**
 * Basic input sanitization for display.
 * Uses htmlspecialchars to prevent XSS.
 *
 * @param string|null $data The data to sanitize.
 * @return string The sanitized data, or an empty string if input was null.
 */
function sanitize_output($data) {
    return $data !== null ? htmlspecialchars($data, ENT_QUOTES, 'UTF-8') : '';
}


/**
 * SIMULATION FUNCTION: Returns a static list of users for admin management demo.
 * In a real application, this would fetch users from the database.
 *
 * @param mixed $db_conn_placeholder Placeholder for DB connection (not used in simulation).
 * @return array An array of user data.
 */
function get_all_users_simulation($db_conn_placeholder = null) {
    return [
        1 => ['id'=>1, 'name'=>'Test User', 'email'=>'test@example.com', 'gender'=>'Male', 'dob'=>'1990-01-01', 'is_approved'=>1, 'is_premium'=>0, 'created_at'=>'2023-01-15 10:00:00'],
        2 => ['id'=>2, 'name'=>'Jane Doe', 'email'=>'jane@example.com', 'gender'=>'Female', 'dob'=>'1992-05-15', 'is_approved'=>1, 'is_premium'=>1, 'created_at'=>'2023-02-20 11:30:00'],
        3 => ['id'=>3, 'name'=>'Pending User', 'email'=>'pending@example.com', 'gender'=>'Other', 'dob'=>'1995-10-20', 'is_approved'=>0, 'is_premium'=>0, 'created_at'=>'2023-03-10 14:15:00'],
        4 => ['id'=>4, 'name'=>'Another User', 'email'=>'another@example.com', 'gender'=>'Male', 'dob'=>'1988-07-07', 'is_approved'=>1, 'is_premium'=>0, 'created_at'=>'2023-04-01 09:05:00'],
        5 => ['id'=>5, 'name'=>'Unapproved Premium', 'email'=>'unapproved.premium@example.com', 'gender'=>'Female', 'dob'=>'1993-11-25', 'is_approved'=>0, 'is_premium'=>1, 'created_at'=>'2023-05-12 16:45:00'], // Edge case: premium but not approved
    ];
}

// Future general-purpose functions can be added here.
?>
