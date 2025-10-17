<?php
// delete_placement.php

session_name("staff");
session_start();

// Ensure the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

require_once "config.php";

// Security Check: Placement ID must be provided and must be a number
if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
    header("location: manage_placements.php");
    exit;
}

require_once "config.php";

$placement_id = $_GET["id"];

// --- Use a Transaction for Data Integrity ---
$conn->begin_transaction();

try {
    // First, get the student's registration number from the placement record before we delete it.
    $student_regdno = null;
    $sql_get_student = "SELECT student_regdno FROM placements WHERE placement_id = ?";
    if($stmt_get = $conn->prepare($sql_get_student)){
        $stmt_get->bind_param("i", $placement_id);
        $stmt_get->execute();
        $result = $stmt_get->get_result();
        if($row = $result->fetch_assoc()){
            $student_regdno = $row['student_regdno'];
        }
        $stmt_get->close();
    }

    // If we found the student, proceed with the deletion and update
    if($student_regdno){
        // 1. Delete the record from the placements table
        $sql_delete = "DELETE FROM placements WHERE placement_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $placement_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        // 2. Update the student's status back to 'Not Placed'
        $sql_update = "UPDATE student SET placement_status = 'Not Placed' WHERE regdno = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("s", $student_regdno);
        $stmt_update->execute();
        $stmt_update->close();

        // If everything was successful, commit the transaction
        $conn->commit();
        $_SESSION['flash_message'] = "Placement record has been removed. Student status is now 'Not Placed'.";
    } else {
        // If the placement ID was not found, do nothing and roll back
        throw new Exception("Placement record not found.");
    }

} catch (Exception $e) {
    // If any step failed, undo all changes
    $conn->rollback();
    $_SESSION['flash_message'] = "Error: Could not remove the placement record."; // You can set an error flash message
}

$conn->close();

// Redirect back to the placements list
header("location: manage_placements.php");
exit;
?>