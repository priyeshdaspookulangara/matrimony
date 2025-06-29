<?php
// matrimony_site/includes/functions.php

/**
 * Calculates age based on a date of birth string.
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
        return 'N/A';
    }
}

/**
 * Basic input sanitization for display.
 */
function sanitize_output($data) {
    return $data !== null ? htmlspecialchars($data, ENT_QUOTES, 'UTF-8') : '';
}


/**
 * SIMULATION FUNCTION: Returns a static list of users for admin management demo.
 */
function get_all_users_simulation($db_conn_placeholder = null) {
    return [
        1 => ['id'=>1, 'name'=>'Test User', 'email'=>'test@example.com', 'gender'=>'Male', 'dob'=>'1990-01-01',
              'is_approved'=>1, 'is_premium'=>0, 'created_at'=>'2023-01-15 10:00:00',
              'birth_star' => 'Rohini', 'time_of_birth' => '10:30', 'birth_place' => 'New Delhi, India',
              'hobbies' => 'Reading, Traveling, Photography'],
        2 => ['id'=>2, 'name'=>'Jane Doe', 'email'=>'jane@example.com', 'gender'=>'Female', 'dob'=>'1992-05-15',
              'is_approved'=>1, 'is_premium'=>1, 'created_at'=>'2023-02-20 11:30:00',
              'birth_star' => 'Ashwini', 'time_of_birth' => '14:45', 'birth_place' => 'Mumbai, India',
              'hobbies' => 'Hiking, Painting, Yoga'],
        3 => ['id'=>3, 'name'=>'Pending User', 'email'=>'pending@example.com', 'gender'=>'Other', 'dob'=>'1995-10-20',
              'is_approved'=>0, 'is_premium'=>0, 'created_at'=>'2023-03-10 14:15:00',
              'birth_star' => 'Bharani', 'time_of_birth' => '08:00', 'birth_place' => 'Kolkata, India',
              'hobbies' => 'Gaming, Anime, Blogging'],
        4 => ['id'=>4, 'name'=>'Another User', 'email'=>'another@example.com', 'gender'=>'Male', 'dob'=>'1988-07-07',
              'is_approved'=>1, 'is_premium'=>0, 'created_at'=>'2023-04-01 09:05:00',
              'birth_star' => 'Krittika', 'time_of_birth' => '18:15', 'birth_place' => 'Chennai, India',
              'hobbies' => 'Gym, Cooking, Film Making'],
        5 => ['id'=>5, 'name'=>'Unapproved Premium', 'email'=>'unapproved.premium@example.com', 'gender'=>'Female', 'dob'=>'1993-11-25',
              'is_approved'=>0, 'is_premium'=>1, 'created_at'=>'2023-05-12 16:45:00',
              'birth_star' => 'Mrigashira', 'time_of_birth' => '22:00', 'birth_place' => 'Bengaluru, India',
              'hobbies' => 'Dancing, Social Work, Cycling'],
    ];
}

// Future general-purpose functions can be added here.
?>
