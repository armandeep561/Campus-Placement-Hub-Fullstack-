<?php
// edit_job.php

session_name("staff");
session_start();

// Ensure the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

require_once "config.php";

require_once "config.php";

$job_id = "";
$job_data = null;
$feedback_msg = "";
$feedback_class = "";

// --- Part 1: Handle form submission to UPDATE data ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['job_id'])) {
    $job_id = $_POST['job_id'];
    
    $sql = "UPDATE jobs SET job_title = ?, description = ?, package_lpa = ?, required_cgpa = ?, max_backlogs = ? WHERE job_id = ?";
    if($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssddii", 
            $_POST['job_title'],
            $_POST['description'],
            $_POST['package_lpa'],
            $_POST['required_cgpa'],
            $_POST['max_backlogs'],
            $job_id
        );
        if($stmt->execute()) {
            $feedback_msg = "Job posting updated successfully!";
            $feedback_class = "alert-success";
        } else {
            $feedback_msg = "Error updating record. Please try again.";
            $feedback_class = "alert-error";
        }
        $stmt->close();
    }
} 
// --- Part 2: Handle initial page load to GET data ---
else if (isset($_GET['id'])) {
    $job_id = $_GET['id'];
}

// --- Part 3: Fetch the job's data to display in the form ---
if (!empty($job_id)) {
    $sql_fetch = "SELECT * FROM jobs WHERE job_id = ?";
    if($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $job_id);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        if ($result->num_rows == 1) {
            $job_data = $result->fetch_assoc();
        } else {
            $feedback_msg = "Error: Job posting not found.";
            $feedback_class = "alert-error";
        }
        $stmt_fetch->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job Posting - Admin Dashboard</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/all.css">
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
        <div class="video-background">
        <video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video>
    </div>
        <div class="dashboard-card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header">
                <h2>Edit Job Posting for <?php echo htmlspecialchars($job_data['company_name'] ?? ''); ?></h2>
                <a href="manage_jobs.php" class="btn btn-secondary">← Back to Job List</a>
            </div>

            <?php if(!empty($feedback_msg)): ?>
                <div class="alert <?php echo $feedback_class; ?>"><?php echo $feedback_msg; ?></div>
            <?php endif; ?>

            <?php if($job_data): ?>
            <form action="edit_job.php" method="post">
                <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job_data['job_id']); ?>">

                <div class="form-group">
                    <label for="job_title">Job Title / Role</label>
                    <input type="text" id="job_title" name="job_title" value="<?php echo htmlspecialchars($job_data['job_title']); ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="package_lpa">Package (LPA)</label>
                        <input type="number" step="0.01" id="package_lpa" name="package_lpa" value="<?php echo htmlspecialchars($job_data['package_lpa']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="required_cgpa">Minimum CGPA Required</label>
                        <input type="number" step="0.01" id="required_cgpa" name="required_cgpa" value="<?php echo htmlspecialchars($job_data['required_cgpa']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="max_backlogs">Maximum Backlogs Allowed</label>
                        <input type="number" id="max_backlogs" name="max_backlogs" value="<?php echo htmlspecialchars($job_data['max_backlogs']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Job Description</label>
                    <textarea id="description" name="description" rows="6"><?php echo htmlspecialchars($job_data['description']); ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>