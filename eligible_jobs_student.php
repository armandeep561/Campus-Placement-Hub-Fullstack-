<?php
// eligible_jobs_student.php (Version 2.0 - With Application System)

require_once 'config.php';
session_start();
 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["num"])){
    header("location: student_login.php");
    exit;
}
$regdno = $_SESSION["num"];

// --- NEW: LOGIC TO HANDLE JOB APPLICATION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apply_for_job'])) {
    $job_id_to_apply = $_POST['job_id'];
    
    $sql_apply = "INSERT INTO applications (job_id, student_regdno) VALUES (?, ?)";
    if($stmt_apply = $conn->prepare($sql_apply)) {
        $stmt_apply->bind_param("is", $job_id_to_apply, $regdno);
        
        try {
            if ($stmt_apply->execute()) {
                $_SESSION['flash_message'] = "Application submitted successfully!";
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // 1062 = Duplicate entry
                $_SESSION['flash_message_error'] = "You have already applied for this job.";
            } else {
                $_SESSION['flash_message_error'] = "An error occurred. Please try again.";
            }
        }
        $stmt_apply->close();
        
        header("location: eligible_jobs_student.php");
        exit;
    }
}

// --- 1. Fetch the logged-in student's academic data ---
$student_cgpa = 0;
$student_backlogs = 0;
$student_name = "Student";

$sql_student = "SELECT s.name, m.cgpa, m.backlogs 
                FROM student s
                LEFT JOIN marks m ON s.regdno = m.regdno
                WHERE s.regdno = ?";
if ($stmt_student = $conn->prepare($sql_student)) {
    $stmt_student->bind_param("s", $regdno);
    $stmt_student->execute();
    $result = $stmt_student->get_result();
    if($student_data = $result->fetch_assoc()) {
        $student_name = $student_data['name'];
        $student_cgpa = $student_data['cgpa'] ?? 0;
        $student_backlogs = $student_data['backlogs'] ?? 0;
    }
    $stmt_student->close();
}

// --- 2. Fetch all jobs the student is eligible for ---
$eligible_jobs = [];
$sql_jobs = "SELECT * FROM jobs WHERE required_cgpa <= ? AND max_backlogs >= ? ORDER BY package_lpa DESC";
if ($stmt_jobs = $conn->prepare($sql_jobs)) {
    $stmt_jobs->bind_param("di", $student_cgpa, $student_backlogs);
    $stmt_jobs->execute();
    $result_jobs = $stmt_jobs->get_result();
    while($row = $result_jobs->fetch_assoc()){
        $eligible_jobs[] = $row;
    }
    $stmt_jobs->close();
}

// --- 3. Fetch IDs of jobs the student has already applied for ---
$applied_job_ids = [];
$sql_applied = "SELECT job_id FROM applications WHERE student_regdno = ?";
if($stmt_applied = $conn->prepare($sql_applied)){
    $stmt_applied->bind_param("s", $regdno);
    $stmt_applied->execute();
    $result_applied = $stmt_applied->get_result();
    while($row = $result_applied->fetch_assoc()){
        $applied_job_ids[] = $row['job_id'];
    }
    $stmt_applied->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eligible Jobs - Student Portal</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/all.css">
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
            <span class="welcome-user">Welcome, <strong><?php echo htmlspecialchars($student_name); ?></strong></span>
            <a href="student_logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>
    

    <main class="dashboard-main">
        <div class="video-background"><video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video></div>
        
        <div class="dashboard-card" style="max-width: 1000px; margin: 0 auto;">
            <div class="card-header">
                <h2><i class="fas fa-briefcase"></i> Job Opportunities You're Eligible For</h2>
                <a href="student_profile.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>
            
            <!-- Display for flash messages -->
            <div class="flash-message-container">
                <?php 
                    if (isset($_SESSION['flash_message'])) {
                        echo '<div class="alert alert-success">' . $_SESSION['flash_message'] . '</div>';
                        unset($_SESSION['flash_message']);
                    }
                    if (isset($_SESSION['flash_message_error'])) {
                        echo '<div class="alert alert-error">' . $_SESSION['flash_message_error'] . '</div>';
                        unset($_SESSION['flash_message_error']);
                    }
                ?>
            </div>

            <div class="eligibility-summary">
                Showing jobs based on your profile: 
                <strong>CGPA: <?php echo number_format($student_cgpa, 2); ?></strong> & 
                <strong>Backlogs: <?php echo $student_backlogs; ?></strong>
            </div>

            <div class="job-listings">
                <?php if (empty($eligible_jobs)): ?>
                    <div class="alert alert-warning" style="text-align: center;">
                        <strong>No job opportunities match your current profile.</strong>
                        <p style="margin-top: 0.5rem;">Keep improving your score! New companies are added regularly.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($eligible_jobs as $job): ?>
                        <div class="job-card">
                            <div class="job-card-header">
                                <h3 class="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                                <h4 class="company-name"><?php echo htmlspecialchars($job['company_name']); ?></h4>
                            </div>
                            <div class="job-card-details">
                                <div class="detail-item"><i class="fas fa-coins"></i> Package: <strong><?php echo htmlspecialchars($job['package_lpa'] ?? 'N/A'); ?> LPA</strong></div>
                                <div class="detail-item"><i class="fas fa-star"></i> Min. CGPA: <strong><?php echo htmlspecialchars($job['required_cgpa']); ?></strong></div>
                                <div class="detail-item"><i class="fas fa-times-circle"></i> Max Backlogs: <strong><?php echo htmlspecialchars($job['max_backlogs']); ?></strong></div>
                            </div>
                            <div class="job-card-description">
                                <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                            </div>
                            <div class="job-card-actions">
                                <?php if (in_array($job['job_id'], $applied_job_ids)): ?>
                                    <button class="btn btn-success" disabled><i class="fas fa-check"></i> Applied</button>
                                <?php else: ?>
                                    <form action="eligible_jobs_student.php" method="post" style="margin:0;">
                                        <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                        <button type="submit" name="apply_for_job" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Apply Now
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>