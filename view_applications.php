<?php
// view_applications.php (Version 2.1 - Complete & Final)

session_name("staff");
session_start();

// Ensure the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

require_once "config.php";

require_once "config.php";

$selected_job_id = $_GET['job_id'] ?? null;

// --- LOGIC TO HANDLE STATUS UPDATE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['application_id'])) {
    $application_id = $_POST['application_id'];
    $new_status = $_POST['status'];
    
    $sql_update = "UPDATE applications SET status = ? WHERE application_id = ?";
    if($stmt_update = $conn->prepare($sql_update)) {
        $stmt_update->bind_param("si", $new_status, $application_id);
        if($stmt_update->execute()){
            $_SESSION['flash_message'] = "Application status updated successfully.";
        }
        $stmt_update->close();
    }
    // Redirect back to the same view to see the change
    header("location: view_applications.php?job_id=" . $selected_job_id);
    exit;
}

// Check for a flash message
$feedback_msg = null;
if (isset($_SESSION['flash_message'])) {
    $feedback_msg = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// --- Query 1: Fetch all jobs and their application counts ---
$jobs_with_app_counts = [];
$sql_jobs = "SELECT j.job_id, j.job_title, j.company_name, COUNT(a.application_id) as application_count
             FROM jobs j
             LEFT JOIN applications a ON j.job_id = a.job_id
             GROUP BY j.job_id
             ORDER BY j.company_name, j.job_title";
if ($result = $conn->query($sql_jobs)) {
    while ($row = $result->fetch_assoc()) {
        $jobs_with_app_counts[] = $row;
    }
    $result->free();
}

// --- Query 2: If a job is selected, fetch its applicants ---
$applicants = [];
$selected_job_title = "";
if ($selected_job_id && is_numeric($selected_job_id)) {
    // UPDATED QUERY: Now fetches application_id and status
    $sql_applicants = "SELECT s.regdno, s.name, s.email, s.contact, m.cgpa, m.backlogs, a.application_id, a.status, a.application_date
                       FROM applications a
                       JOIN student s ON a.student_regdno = s.regdno
                       LEFT JOIN marks m ON s.regdno = m.regdno
                       WHERE a.job_id = ?
                       ORDER BY FIELD(a.status, 'Shortlisted', 'Interviewing', 'Applied', 'Rejected'), m.cgpa DESC";

    if ($stmt = $conn->prepare($sql_applicants)) {
        $stmt->bind_param("i", $selected_job_id);
        $stmt->execute();
        $result_applicants = $stmt->get_result();
        while ($row = $result_applicants->fetch_assoc()) {
            $applicants[] = $row;
        }
        $stmt->close();
    }
    // Get the job title for the header
    foreach($jobs_with_app_counts as $job) {
        if ($job['job_id'] == $selected_job_id) {
            $selected_job_title = $job['company_name'] . ' - ' . $job['job_title'];
            break;
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications - Admin Dashboard</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="education.png">
</head>
<body class="dashboard-body admin-theme">

    <header class="dashboard-header">
        <div class="header-content">
            <i class="fas fa-university logo-icon"></i>
            <h1 class="portal-title">Campus Placement Hub</h1>
        </div>
        <div class="user-actions">
            <span class="welcome-user">Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong></span>
            <a href="staff_logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <main class="dashboard-main">
        <div class="video-background"><video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video></div>

        <div class="dashboard-card" style="max-width: 1300px; margin: 0 auto;">
            <div class="card-header">
                <h2><i class="fas fa-file-alt"></i> View Applications by Job Posting</h2>
                <a href="staff_access.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
            
            <div class="flash-message-container" style="padding: 0 1.5rem;">
                <?php if(!empty($feedback_msg)): ?>
                    <div class="alert alert-success"><?php echo $feedback_msg; ?></div>
                <?php endif; ?>
            </div>

            <div class="dashboard-grid" style="grid-template-columns: 1fr 3fr; align-items: start; padding: 1.5rem;">
                <!-- Left Column: List of Jobs -->
                <div class="job-list-container">
                    <h4>Select a Job</h4>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <input type="text" id="job-search-input" placeholder="Search jobs or companies...">
                    </div>
                    <ul class="job-list">
                        <?php foreach($jobs_with_app_counts as $job): ?>
                            <li class="<?php echo ($selected_job_id == $job['job_id']) ? 'active' : ''; ?>" 
                                data-search="<?php echo htmlspecialchars(strtolower($job['company_name'] . ' ' . $job['job_title'])); ?>">
                                <a href="view_applications.php?job_id=<?php echo $job['job_id']; ?>">
                                    <span class="job-list-title"><?php echo htmlspecialchars($job['company_name']); ?></span>
                                    <span class="job-list-subtitle"><?php echo htmlspecialchars($job['job_title']); ?></span>
                                    <span class="app-count-badge"><?php echo $job['application_count']; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Right Column: Applicant Details -->
                <div class="applicant-details-container">
                    <?php if ($selected_job_id): ?>
                        <h3>Applicants for "<?php echo htmlspecialchars($selected_job_title); ?>"</h3>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>CGPA</th>
                                        <th>Backlogs</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($applicants)): ?>
                                        <tr><td colspan="6">No students have applied for this job yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($applicants as $applicant): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($applicant['name']); ?></strong><br><small><?php echo htmlspecialchars($applicant['regdno']); ?></small></td>
                                                <td><?php echo htmlspecialchars(number_format($applicant['cgpa'] ?? 0, 2)); ?></td>
                                                <td><?php echo htmlspecialchars($applicant['backlogs'] ?? 0); ?></td>
                                                <td><?php echo htmlspecialchars($applicant['email']); ?></td>
                                                <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $applicant['status'])); ?>"><?php echo htmlspecialchars($applicant['status']); ?></span></td>
                                                <td>
                                                    <form action="view_applications.php?job_id=<?php echo $selected_job_id; ?>" method="post" class="status-update-form">
                                                        <input type="hidden" name="application_id" value="<?php echo $applicant['application_id']; ?>">
                                                        <div class="select-wrapper">
                                                            <select name="status" onchange="this.form.submit();">
                                                                <option value="Applied" <?php echo ($applicant['status'] == 'Applied') ? 'selected' : ''; ?>>Applied</option>
                                                                <option value="Shortlisted" <?php echo ($applicant['status'] == 'Shortlisted') ? 'selected' : ''; ?>>Shortlisted</option>
                                                                <option value="Interviewing" <?php echo ($applicant['status'] == 'Interviewing') ? 'selected' : ''; ?>>Interviewing</option>
                                                                <option value="Rejected" <?php echo ($applicant['status'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                                            </select>
                                                        </div>
                                                        <!-- This button is a fallback for if JavaScript is disabled -->
                                                       <noscript><button type="submit" name="update_status" class="btn btn-primary">Update</button></noscript>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="placeholder-text">
                            <i class="fas fa-arrow-left"></i>
                            <h3>Select a job posting from the left to view the list of applicants.</h3>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript for Live Search Functionality -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('job-search-input');
        const jobListItems = document.querySelectorAll('.job-list li');

        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = searchInput.value.toLowerCase();
                jobListItems.forEach(item => {
                    const searchableText = item.dataset.search;
                    if (searchableText.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    });
    </script>

</body>
</html>