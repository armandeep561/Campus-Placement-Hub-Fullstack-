<?php
// student_profile.php (Version 3.1 - FINAL)

require_once 'config.php';
session_start();
 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["num"])){
    header("location: student_login.php");
    exit;
}
$regdno = $_SESSION["num"];

$student_data = null;
$placement_data = null;
$sql_student = "SELECT s.*, m.cgpa, m.backlogs FROM student s LEFT JOIN marks m ON s.regdno = m.regdno WHERE s.regdno = ?";
if ($stmt_student = $conn->prepare($sql_student)) {
    $stmt_student->bind_param("s", $regdno);
    $stmt_student->execute();
    $result = $stmt_student->get_result();
    $student_data = $result->fetch_assoc();
    $stmt_student->close();
}
if (!$student_data) { echo "Error: Student data not found."; exit; }
if ($student_data['placement_status'] == 'Placed') {
    $sql_placement = "SELECT p.package_lpa, p.placement_date, j.company_name, j.job_title FROM placements p LEFT JOIN jobs j ON p.job_id = j.job_id WHERE p.student_regdno = ?";
    if($stmt_placement = $conn->prepare($sql_placement)){
        $stmt_placement->bind_param("s", $regdno);
        $stmt_placement->execute();
        $result = $stmt_placement->get_result();
        $placement_data = $result->fetch_assoc();
        $stmt_placement->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - <?php echo htmlspecialchars($student_data['name']); ?></title>
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
            <span class="welcome-user">Welcome, <strong><?php echo htmlspecialchars($student_data['name']); ?></strong></span>
            <a href="student_logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <main class="dashboard-main">
        <div class="video-background"><video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video></div>
        <div class="content-wrapper">
            <div class="dashboard-grid-profile">
                <!-- Left Column: Profile Pane -->
                <aside class="profile-pane">
                    <div class="profile-header">
                        <div class="profile-avatar"><i class="fas fa-user-graduate"></i></div>
                        <h2 class="profile-name"><?php echo htmlspecialchars($student_data['name']); ?></h2>
                        <span class="profile-regdno"><?php echo htmlspecialchars($student_data['regdno']); ?></span>
                    </div>
                    <div class="profile-contact-list">
    <div class="contact-item">
        <label><i class="fas fa-envelope"></i> Email</label>
        <span><?php echo htmlspecialchars($student_data['email'] ?? 'N/A'); ?></span>
    </div>
    <div class="contact-item">
        <label><i class="fas fa-phone"></i> Contact</label>
        <span><?php echo htmlspecialchars($student_data['contact'] ?? 'N/A'); ?></span>
    </div>
    <div class="contact-item">
        <label><i class="fas fa-calendar-alt"></i> Date of Birth</label>
        <span><?php echo date("d M, Y", strtotime($student_data['dob'])); ?></span>
    </div>
</div>
                </aside>

                <!-- Right Column: Details Pane -->
                <div class="details-pane">
                    <div class="dashboard-card cta-card-redesigned">
                        <div class="cta-text"><h2>Your Placement Journey</h2><p>Find new jobs you're eligible for, or track your application status.</p></div>
                        <div class="cta-actions">
                            <a href="eligible_jobs_student.php" class="btn btn-primary"><i class="fas fa-search"></i> Find Jobs</a>
                            <a href="my_applications.php" class="btn btn-secondary"><i class="fas fa-file-signature"></i> My Applications</a>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <h2>Academic & Placement Status</h2>
                        <div class="profile-details-grid">
                            <div class="profile-detail-item"><label>Current CGPA</label><span class="highlight-value"><?php echo htmlspecialchars(number_format($student_data['cgpa'] ?? 0, 2)); ?></span></div>
                            <div class="profile-detail-item"><label>Active Backlogs</label><span class="highlight-value"><?php echo htmlspecialchars($student_data['backlogs'] ?? 0); ?></span></div>
                            <div class="profile-detail-item"><label>Placement Status</label><span class="status-badge <?php echo str_replace(' ', '-', strtolower($student_data['placement_status'])); ?>"><?php echo htmlspecialchars($student_data['placement_status']); ?></span></div>
                        </div>
                    </div>
                    <?php if ($placement_data): ?>
                    <div class="dashboard-card congrats-card">
                        <div class="congrats-icon"><i class="fas fa-award"></i></div>
                        <div class="congrats-text">
                            <h4>Congratulations on your placement!</h4>
                            <p><strong><?php echo htmlspecialchars($placement_data['company_name'] ?? ''); ?></strong> - <?php echo htmlspecialchars($placement_data['job_title'] ?? ''); ?> (<?php echo htmlspecialchars($placement_data['package_lpa'] ?? 'N/A'); ?> LPA)</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>