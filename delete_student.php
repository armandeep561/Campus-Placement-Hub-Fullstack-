<?php
// delete_student.php (FINAL WORKING VERSION)

session_name("staff");
session_start();

// Ensure the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

require_once "config.php";

if(!isset($_GET["regdno"]) || empty(trim($_GET["regdno"]))){
    header("location: manage_students.php");
    exit;
}

require_once "config.php";
$regdno = trim($_GET["regdno"]);

// Turn off autocommit to start a transaction
mysqli_autocommit($conn, false);

// Initialize a flag to track success
$all_queries_succeeded = true;

// Query 1: Delete from marks table
$sql1 = "DELETE FROM marks WHERE regdno = '$regdno'";
if (!mysqli_query($conn, $sql1)) {
    $all_queries_succeeded = false;
}

// Query 2: Delete from package table (if it exists and is relevant)
$sql2 = "DELETE FROM package WHERE regdno = '$regdno'";
if (!mysqli_query($conn, $sql2)) {
    $all_queries_succeeded = false;
}

// Query 3: Delete the student from the main student table
$sql3 = "DELETE FROM student WHERE regdno = '$regdno'";
if (!mysqli_query($conn, $sql3)) {
    $all_queries_succeeded = false;
}

// Now, check the flag to either commit or rollback
if ($all_queries_succeeded) {
    // All deletions were successful, commit the changes
    mysqli_commit($conn);
} else {
    // At least one deletion failed, roll back everything
    mysqli_rollback($conn);
}

// Close the connection
mysqli_close($conn);

// Redirect back to the management page
header("location: manage_students.php");
exit;
?>