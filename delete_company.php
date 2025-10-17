<?php
// delete_company.php

session_name("staff");
session_start();

// Ensure the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

require_once "config.php";

require_once "config.php";

// Security Check: Company name must be provided
if(!isset($_GET["id"]) || empty($_GET["id"])){
    header("location: manage_companies.php");
    exit;
}

require_once "config.php";
$company_name = $_GET["id"];

// Use a prepared statement to prevent SQL Injection
$sql = "DELETE FROM company WHERE companyname = ?";

if($stmt = $conn->prepare($sql)){
    $stmt->bind_param("s", $company_name);
    
    if($stmt->execute()){
        // Set a success message to be displayed on the next page
        $_SESSION['flash_message'] = "Company '" . htmlspecialchars($company_name) . "' has been deleted.";
    }
    $stmt->close();
}
$conn->close();

// Redirect back to the company list to see the result
header("location: manage_companies.php");
exit;
?>