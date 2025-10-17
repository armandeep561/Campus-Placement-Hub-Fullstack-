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

if(isset($_POST["submit"]))
{
    if(isset($_FILES['file']) && $_FILES['file']['error'] == 0)
    {
        $filename = explode(".", $_FILES['file']['name']);
        if(end($filename) == 'csv')
        {
            $handle = fopen($_FILES['file']['tmp_name'], "r");
            $updated_count = 0;
            $skipped_count = 0;

            // --- THE GUARANTEED BUG FIX ---
            // Prepare both a SELECT (to check) and an UPDATE statement once.
            $check_sql = "SELECT regdno FROM marks WHERE regdno = ?";
            $update_sql = "UPDATE marks SET cgpa = ?, backlogs = ? WHERE regdno = ?";
            
            $check_stmt = $conn->prepare($check_sql);
            $update_stmt = $conn->prepare($update_sql);

            // Optional: Uncomment the line below if your CSV file has a header row.
            // fgetcsv($handle);

            while($data = fgetcsv($handle))
            {
                // CRITICAL FIX: Ensure the row has valid, NUMERIC data for marks before proceeding.
                if (isset($data[0]) && !empty($data[0]) && is_numeric($data[1]) && is_numeric($data[2])) {
                    $regdno = $data[0];
                    $cgpa = $data[1];
                    $backlogs = $data[2];

                    // 1. First, CHECK if the student's marks record exists.
                    $check_stmt->bind_param("s", $regdno);
                    $check_stmt->execute();
                    $check_stmt->store_result();

                    // 2. Only if the record exists (num_rows == 1), do we perform the UPDATE.
                    if ($check_stmt->num_rows == 1) {
                        $update_stmt->bind_param("dis", $cgpa, $backlogs, $regdno);
                        $update_stmt->execute();
                        // We only count an update if the data was actually changed.
                        if ($update_stmt->affected_rows > 0) {
                            $updated_count++;
                        }
                    } else {
                        // If the record does not exist, we skip it.
                        $skipped_count++;
                    }
                } else {
                    // This row has invalid or empty data, so we skip it.
                    $skipped_count++;
                }
            }
            fclose($handle);
            $check_stmt->close();
            $update_stmt->close();

            // --- Clear and Accurate Feedback Logic ---
            if ($updated_count > 0 && $skipped_count > 0) {
                $feedback_message = "Processing complete. <strong>{$updated_count} records were updated successfully.</strong> <strong>{$skipped_count} records were skipped</strong> because they were not found or had invalid data.";
                $feedback_class = "alert-warning";
            } elseif ($updated_count > 0) {
                $feedback_message = "Success! All <strong>{$updated_count} updatable records</strong> from the CSV have been updated.";
                $feedback_class = "alert-success";
            } elseif ($skipped_count > 0) {
                $feedback_message = "No records were updated. All <strong>{$skipped_count} records</strong> in the CSV could not be found or contained invalid data.";
                $feedback_class = "alert-error";
            } else {
                $feedback_message = "The uploaded CSV file appears to be empty or contained no valid data to process.";
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
<html>  
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Student Data - Admin Dashboard</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="education.png">
</head>  
<body class="dashboard-body admin-theme">  

     <header class="dashboard-header">
        <div class="header-content"><i class="fas fa-university logo-icon"></i><h1 class="portal-title">Campus Placement hub</h1></div>
        <div class="user-actions">
            <span class="welcome-user">Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong></span>
            <a href="staff_logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                <h2>Update Student Marks via CSV</h2>
                <a href="staff_access.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
            
            <p>Upload a CSV file to update existing student marks. The file should contain three columns in this order: <strong>Registration Number, CGPA, Backlogs</strong>.</p>

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
                        <i class="fas fa-sync-alt"></i> Update Data
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