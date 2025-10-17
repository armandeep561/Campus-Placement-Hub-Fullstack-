<?php
// --- Database Configuration ---

// Define database parameters
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'placement_user'); // We will create this new user
define('DB_PASSWORD', 'CPH_Project@33'); // Use a strong, unique password
define('DB_NAME', 'miniproject');

// --- Establish Connection ---

// Attempt to connect to MySQL database using the MySQLi extension
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    // In a development environment, this is okay for debugging.
    // In a live/production environment, you would log this error and show a generic message.
    die("ERROR: Could not connect to the database. " . mysqli_connect_error());
}

// Optional: Set the character set to utf8mb4 for better Unicode support
mysqli_set_charset($conn, "utf8mb4");

?>