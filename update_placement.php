<?php
session_start();
 
// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: student_login.php");
    exit;
}
 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Placement Details - Placement Portal</title>
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
        <video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video>
    </div>
    </div>
        <div class="dashboard-card" style="max-width: 700px; margin: 0 auto;">
            <div class="card-header">
                <h2>Upload New Placement Details</h2>
                <a href="student_profile.php" class="btn btn-secondary">← Back to Profile</a>
            </div>
            
            <p>Fill out the form below to add a new placement offer to your profile.</p>

            <form action="upload.php" method='post' enctype="multipart/form-data">    
                <div class="form-group">
                    <label for="title">Company Name</label>
                    <input type="text" id="title" name='title' placeholder="e.g., Infosys" required>
                </div>

                <div class="form-group">
                    <label for="pac">Package (in LPA)</label>
                    <input type="number" id="pac" name='pac' step="0.01" min="0" max="99" placeholder="e.g., 10.5" required>
                </div>

                <div class="form-group">
                    <label>Offer Letter (PDF or Image)</label>
                    <div class="custom-file-input">
                        <!-- This is the actual file input, but it's hidden -->
                        <input type="file" name='file' id="file-upload" required>
                        <!-- This is the button the user sees and clicks -->
                        <label for="file-upload" class="file-upload-label">
                            <i class="fas fa-paperclip"></i> Choose File
                        </label>
                        <!-- This span will display the selected filename -->
                        <span id="file-name-display">No file chosen</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">Reset</button>
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Details
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- JavaScript to display the selected filename -->
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