<?php
// my_applications.php

require_once 'config.php';
session_start();
 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["num"])){
    header("location: student_login.php");
    exit;
}
$regdno = $_SESSION["num"];

// --- Fetch all applications for the logged-in student ---
$applications = [];
$student_name = $_SESSION["username"] ?? "Student";

$sql = "SELECT a.application_date, a.status, j.company_name, j.job_title, j.package_lpa
        FROM applications a
        JOIN jobs j ON a.job_id = j.job_id
        WHERE a.student_regdno = ?
        ORDER BY a.application_date DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $regdno);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $applications[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Student Portal</title>
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
                <h2><i class="fas fa-file-signature"></i> My Job Applications</h2>
                <a href="student_profile.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Job Title</th>
                            <th>Package (LPA)</th>
                            <th>Applied On</th>
                            <th>Current Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="5">You have not applied for any jobs yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($app['company_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                                    <td><?php echo htmlspecialchars($app['package_lpa'] ?? 'N/A'); ?></td>
                                    <td><?php echo date("d M, Y", strtotime($app['application_date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $app['status'])); ?>">
                                            <?php echo htmlspecialchars($app['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>