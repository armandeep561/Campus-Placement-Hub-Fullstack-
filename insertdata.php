<?php  
require_once "config.php";
session_name("staff");
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

// Initialize feedback variables
$feedback_message = "";
$feedback_class = "";

// Set mysqli to throw exceptions on error
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if(isset($_POST["submit"]))
{
    if(isset($_FILES['file']) && $_FILES['file']['error'] == 0)
    {
        $filename = explode(".", $_FILES['file']['name']);
        if(end($filename) == 'csv')
        {
            $handle = fopen($_FILES['file']['tmp_name'], "r");
            $error_count = 0;
            $success_count = 0;
            
            // Optional: Uncomment the line below if your CSV file has a header row to skip.
            // fgetcsv($handle); 

            while($data = fgetcsv($handle))
            {
                // Ensure the row is not empty and has the correct number of columns
                if (isset($data[0]) && isset($data[1]) && isset($data[2])) {
                    $item1 = mysqli_real_escape_string($conn, $data[0]);  
                    $item2 = mysqli_real_escape_string($conn, $data[1]);
                    $item3 = mysqli_real_escape_string($conn, $data[2]);
                    
                    $query = "INSERT into marks(regdno, cgpa, backlogs) values('$item1','$item2','$item3')";
                    
                    // --- THE GUARANTEED FIX: Using a try...catch block ---
                    try {
                        mysqli_query($conn, $query);
                        $success_count++;
                    } catch (mysqli_sql_exception $e) {
                        // This block runs ONLY when the query fails (e.g., foreign key constraint)
                        $error_count++;
                    }
                }
            }
            fclose($handle);

            // Feedback Logic (unchanged, but will now work correctly)
            if ($success_count > 0 && $error_count > 0) {
                $feedback_message = "Processing complete. <strong>{$success_count} records were inserted successfully.</strong> However, <strong>{$error_count} records failed</strong> because the student registration number does not exist in the main student list.";
                $feedback_class = "alert-warning";
            } elseif ($success_count > 0) {
                $feedback_message = "Success! All <strong>{$success_count} records</strong> from the CSV have been inserted.";
                $feedback_class = "alert-success";
            } elseif ($error_count > 0) {
                $feedback_message = "Error: No new records were inserted. All <strong>{$error_count} records failed.</strong> Please ensure the students exist in the system before adding marks.";
                $feedback_class = "alert-error";
            } else {
                $feedback_message = "The uploaded CSV file appears to be empty or improperly formatted. Please check the file and try again.";
                $feedback_class = "alert-warning";
            }

        } else {
            $feedback_message = "Error: Please upload a valid CSV file.";
            $feedback_class = "alert-error";
        }
    } else {
        $feedback_message = "Error: No file was uploaded. Please choose a file.";
        $feedback_class = "alert-error";
    }
}
?>  
<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Student Data - Admin Dashboard</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="education.png">
</head>  
<body class="dashboard-body">  

     <header class="dashboard-header">
        <div class="header-content"><i class="fas fa-university logo-icon"></i><h1 class="portal-title">Campus Placement hub</h1></div>
        <div class="user-actions">
            <span class="welcome-user">Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong></span>
            <a href="staff_logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>
        <div class="user-actions">
            <span class="welcome-user">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
            <a href="staff_reset_password.php" title="Reset Password"><i class="fas fa-key"></i></a>
            <a href="staff_logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
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
        <div class="dashboard-card" style="max-width: 700px; margin: 0 auto;">
            <div class="card-header">
                <h2>Insert Student Marks via CSV</h2>
                <a href="staff_access.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
            
            <p>Upload a CSV file with student data. The file should contain three columns in this order: <strong>Registration Number, CGPA, Backlogs</strong>. Ensure students already exist in the system.</p>

            <form method="post" enctype="multipart/form-data">
                
                <?php 
                    if(!empty($feedback_message)){
                        echo '<div class="alert ' . $feedback_class . '">' . $feedback_message . '</div>';
                    }        
                ?>

                <div class="form-group">
                    <label>Select CSV File</label>
                    <div class="custom-file-input">
                        <input type="file" name='file' id="file-upload" required accept=".csv">
                        <label for="file-upload" class="file-upload-label">
                            <i class="fas fa-file-csv"></i> Choose CSV File
                        </label>
                        <span id="file-name-display">No file chosen</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-database"></i> Import Data
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        const fileUpload = document.getElementById('file-upload');
        const fileNameDisplay = document.getElementById('file-name-display');
        
        fileUpload.addEventListener('change', () => {
            if (fileUpload.files.length > 0) {
                fileNameDisplay.textContent = fileUpload.files[0].name;
            } else {
                fileNameDisplay.textContent = 'No file chosen';
            }
        });
    </script>
</body>  
</html>