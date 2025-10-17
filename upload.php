<?php 
// 1. Setup and Session Check
include 'config.php';
session_start();
 
// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: student_login.php");
    exit;
}

// Initialize feedback variables for the page
$page_title = "Processing...";
$feedback_icon = "fas fa-spinner fa-spin";
$feedback_heading = "Processing Your Upload...";
$feedback_message = "Please wait while we process your submission.";
$feedback_class = "alert-info"; // A new class for neutral/processing state

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- File Handling & Validation ---
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        
        $companyName = $_POST["title"] ?? null; 
        $packageAmount = $_POST["pac"] ?? null;
        $regdno = $_SESSION["num"] ?? null;
        
        if (empty($companyName) || empty($packageAmount) || empty($regdno)) {
             $page_title = "Error";
             $feedback_icon = "fas fa-times-circle";
             $feedback_heading = "Form Data Error";
             $feedback_message = "Required form fields or session data are missing. Please go back and try again.";
             $feedback_class = "alert-error";
        } else {
            $originalFileName = basename($_FILES["file"]["name"]);
            $pname = rand(1000, 10000) . "-" . $originalFileName;
            $uploadDirectory = "uploaded_files/";
            $targetFilePath = $uploadDirectory . $pname;

            if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
                
                // --- Database Insertion (Using SECURE Prepared Statements) ---
                $sql = "INSERT INTO package (regdno, companyname, package, file) VALUES (?, ?, ?, ?)";
                
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ssds", $regdno, $companyName, $packageAmount, $pname);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $page_title = "Success!";
                        $feedback_icon = "fas fa-check-circle";
                        $feedback_heading = "Upload Successful!";
                        $feedback_message = "Your offer letter details have been successfully added to your profile.";
                        $feedback_class = "alert-success";
                    } else {
                        $page_title = "Error";
                        $feedback_icon = "fas fa-times-circle";
                        $feedback_heading = "Database Error";
                        $feedback_message = "Something went wrong during the database operation. Please try again later.";
                        $feedback_class = "alert-error";
                    }
                    mysqli_stmt_close($stmt);
                } else {
                     $page_title = "Error";
                     $feedback_icon = "fas fa-times-circle";
                     $feedback_heading = "Database Error";
                     $feedback_message = "Could not prepare the database statement. Please contact an administrator.";
                     $feedback_class = "alert-error";
                }
            } else {
                $page_title = "Error";
                $feedback_icon = "fas fa-times-circle";
                $feedback_heading = "File Move Error";
                $feedback_message = "Sorry, there was an error moving your file to the server. Please check server permissions.";
                $feedback_class = "alert-error";
            }
        }
    } else {
        $page_title = "Error";
        $feedback_icon = "fas fa-times-circle";
        $feedback_heading = "Upload Error";
        $feedback_message = "No file was selected or an error occurred during upload. Please go back and try again.";
        $feedback_class = "alert-error";
    }
    
    mysqli_close($conn);

} else {
    // Redirect if the page was accessed directly
    header("location: update_placement.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Placement Portal</title>
    <!-- Link to the SAME stylesheet to reuse the modern design -->
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="education.png">
</head>
<body class="dashboard-body">

    <header class="dashboard-header">
        <div class="header-content">
            <i class="fas fa-university logo-icon"></i>
            <h1 class="portal-title">Campus Placement Hub</h1>
        </div>
        <div class="user-actions">
            <span class="welcome-user">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
            <a href="student_logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </header>

    <main class="dashboard-main">
        <!-- Video Background (optional) -->
    <div class="video-background">
        <video autoplay muted loop playsinline>
            <source src="bg.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
        <div class="dashboard-card feedback-card <?php echo $feedback_class; ?>">
            <i class="<?php echo $feedback_icon; ?> feedback-icon"></i>
            <h2 class="feedback-heading"><?php echo $feedback_heading; ?></h2>
            <p class="feedback-message"><?php echo $feedback_message; ?></p>
            <div class="form-actions">
                <a href="student_profile.php" class="btn btn-primary">Return to Profile</a>
            </div>
        </div>
    </main>

</body>
</html>