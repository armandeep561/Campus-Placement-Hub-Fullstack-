<?php
// delete_job.php

session_name("staff");
session_start();

// Ensure the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

require_once "config.php";

// Security Check: Job ID must be provided and must be a number
if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
    header("location: manage_jobs.php");
    exit;
}

require_once "config.php";
$job_id = $_GET["id"];

$sql = "DELETE FROM jobs WHERE job_id = ?";

if($stmt = $conn->prepare($sql)){
    $stmt->bind_param("i", $job_id);
    
    if($stmt->execute()){
        $_SESSION['flash_message'] = "The job posting has been deleted.";
    }
    $stmt->close();
}
$conn->close();

// Redirect back to the job list
header("location: manage_jobs.php");
exit;
?>
<link rel="icon" type="image/png" href="education.png">