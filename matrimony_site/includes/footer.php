</div> <!-- End of .container div from header.php -->

    <footer class="main-footer">
        <p>&copy; <?php echo date("Y"); ?> Matrimony Site. All rights reserved.</p>
        <!-- You can add more links or information here -->
        <p>
            <a href="about_us.php">About Us</a> |
            <a href="contact_us.php">Contact Us</a> |
            <a href="privacy_policy.php">Privacy Policy</a>
        </p>
        <?php
        // Optional: Display debug information for session if needed during development
        // if (isset($_SESSION)) {
        //     echo "<pre>Session Data:\n";
        //     print_r($_SESSION);
        //     echo "</pre>";
        // }
        // if (isset($db) && $db instanceof mysqli && !mysqli_connect_errno()) {
        //     echo "<p style='color:green;'>Database connection active.</p>";
        // } elseif (isset($db)) {
        //     echo "<p style='color:orange;'>Database connection attempted but failed: " . mysqli_connect_error() . "</p>";
        // } else {
        //     echo "<p style='color:red;'>Database not connected.</p>";
        // }
        ?>
    </footer>
</body>
</html>
