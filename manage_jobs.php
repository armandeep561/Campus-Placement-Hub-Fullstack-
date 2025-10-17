<?php
// manage_jobs.php

session_name("staff");
session_start();

// Ensure the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

require_once "config.php";

$feedback_msg = "";
$feedback_class = "";

// --- LOGIC TO ADD A NEW JOB POSTING ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_job'])) {
    // Basic validation
    if (empty(trim($_POST['company_name'])) || empty(trim($_POST['job_title']))) {
        $feedback_msg = "Company Name and Job Title are required.";
        $feedback_class = "alert-error";
    } else {
        $sql = "INSERT INTO jobs (company_name, job_title, description, package_lpa, required_cgpa, max_backlogs) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssddi", 
                $_POST['company_name'],
                $_POST['job_title'],
                $_POST['description'],
                $_POST['package_lpa'],
                $_POST['required_cgpa'],
                $_POST['max_backlogs']
            );
            
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "New job posting for '" . htmlspecialchars($_POST['company_name']) . "' added successfully!";
                header("location: manage_jobs.php");
                exit;
            } else {
                $feedback_msg = "Error: Could not add job posting. Please ensure the company exists.";
                $feedback_class = "alert-error";
            }
            $stmt->close();
        }
    }
}

// Check for a flash message from a previous action
if (isset($_SESSION['flash_message'])) {
    $feedback_msg = $_SESSION['flash_message'];
    $feedback_class = "alert-success";
    unset($_SESSION['flash_message']);
}

// --- DATA FETCHING FOR THE PAGE ---
// 1. Fetch all job postings
$jobs = [];
$sql_fetch_jobs = "SELECT * FROM jobs ORDER BY created_at DESC";
if ($result = $conn->query($sql_fetch_jobs)) {
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
    $result->free();
}

// 2. Fetch all company names for the dropdown menu
$companies = [];
$sql_fetch_companies = "SELECT companyname FROM company ORDER BY companyname ASC";
if ($result = $conn->query($sql_fetch_companies)) {
    while ($row = $result->fetch_assoc()) {
        $companies[] = $row;
    }
    $result->free();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Job Postings - Admin Dashboard</title>
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

        <!-- Feedback Message Area -->
        <div style="max-width: 1200px; margin: 0 auto 2rem auto;">
            <?php if(!empty($feedback_msg) && $feedback_class !== 'alert-success'): ?>
                <div class="alert <?php echo $feedback_class; ?>"><?php echo $feedback_msg; ?></div>
            <?php endif; ?>
        </div>

        <div class="dashboard-grid" style="grid-template-columns: 1fr 2fr; max-width: 1200px; margin: 0 auto;">
            <!-- SECTION 1: ADD NEW JOB -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-briefcase"></i> Add New Job Posting</h2>
                </div>
                <form action="manage_jobs.php" method="post">
                    <div class="form-group">
                        <label for="company_name">Company</label>
                        <div class="select-wrapper">
                            <select id="company_name" name="company_name" required>
                                <option value="">-- Select a Company --</option>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?php echo htmlspecialchars($company['companyname']); ?>"><?php echo htmlspecialchars($company['companyname']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="job_title">Job Title / Role</label>
                        <input type="text" id="job_title" name="job_title" placeholder="e.g., Software Engineer Trainee" required>
                    </div>
                    <div class="form-group">
                        <label for="package_lpa">Package (LPA)</label>
                        <input type="number" step="0.01" id="package_lpa" name="package_lpa" placeholder="e.g., 4.5">
                    </div>
                    <div class="form-group">
                        <label for="required_cgpa">Minimum CGPA Required</label>
                        <input type="number" step="0.01" id="required_cgpa" name="required_cgpa" value="6.0">
                    </div>
                    <div class="form-group">
                        <label for="max_backlogs">Maximum Backlogs Allowed</label>
                        <input type="number" id="max_backlogs" name="max_backlogs" value="0">
                    </div>
                    <div class="form-group">
                        <label for="description">Job Description (Optional)</label>
                        <textarea id="description" name="description" rows="5"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="add_job" class="btn btn-primary full-width">
                            <i class="fas fa-plus"></i> Add Job Posting
                        </button>
                    </div>
                </form>
            </div>

            <!-- SECTION 2: VIEW ALL JOBS -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-list-ul"></i> Current Job Postings (<?php echo count($jobs); ?>)</h2>
                    <a href="staff_access.php" class="btn btn-secondary">← Back to Dashboard</a>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Job Title</th>
                                <th>Package (LPA)</th>
                                <th>Min. CGPA</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jobs)): ?>
                                <tr><td colspan="5">No job postings found. Add one using the form.</td></tr>
                            <?php else: ?>
                                <?php foreach ($jobs as $job): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($job['company_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                                        <td><?php echo htmlspecialchars($job['package_lpa'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($job['required_cgpa']); ?></td>
                                        <td class="actions">
                                            <!-- These will be our next step -->
                                            <a href="edit_job.php?id=<?php echo $job['job_id']; ?>" class="btn btn-icon" title="Edit Job"><i class="fas fa-edit"></i></a>
                                            <a href="delete_job.php?id=<?php echo $job['job_id']; ?>" class="btn btn-icon btn-danger" title="Delete Job" onclick="return confirm('Are you sure you want to delete this job posting?');"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Toast Notification for flash messages -->
    <div id="toast-notification" class="toast"><i class="fas fa-check-circle"></i><span id="toast-message"></span></div>
    <script>
        <?php if (isset($feedback_msg) && $feedback_class === 'alert-success'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('toast-notification');
                const toastMessage = document.getElementById('toast-message');
                if (toast && toastMessage) {
                    toastMessage.textContent = '<?php echo addslashes($feedback_msg); ?>';
                    toast.classList.add('show');
                    setTimeout(() => toast.classList.remove('show'), 4000);
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>