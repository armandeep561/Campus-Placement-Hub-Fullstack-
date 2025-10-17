<?php
// staff_access.php (Version 5.1 - Final Redesign, All Code Included)

session_name("staff");
session_start();
 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

require_once "config.php";

// --- STATISTICS PHP LOGIC ---
$total_students = $conn->query("SELECT COUNT(regdno) as count FROM student")->fetch_assoc()['count'] ?? 0;
$total_placed = $conn->query("SELECT COUNT(regdno) as count FROM student WHERE placement_status = 'Placed'")->fetch_assoc()['count'] ?? 0;
$companies_registered = $conn->query("SELECT COUNT(*) as count FROM company")->fetch_assoc()['count'] ?? 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Placement Portal</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="education.png">
</head>
<body class="dashboard-body">

    <div class="video-background">
        <video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video>
    </div>

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

    <div class="content-wrapper">
        <main class="dashboard-main">
            <!-- Statistics Row -->
            <div class="stat-card-row">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $total_students; ?></span>
                        <span class="stat-label">Total Students</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-check"></i>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $total_placed; ?></span>
                        <span class="stat-label">Students Placed</span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-building"></i>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $companies_registered; ?></span>
                        <span class="stat-label">Companies Registered</span>
                    </div>
                </div>
            </div>

            <!-- Single "Command Center" Card -->
            <div class="dashboard-card command-center-card">
                <div class="card-grid-internal">
                    <!-- Left Side: Quick Find -->
                    <div class="grid-col-left">
                        <h2><i class="fas fa-search"></i> Quick Find Student</h2>
                        <p>Instantly find and edit a student's profile by their registration number.</p>
                        <form action="edit_student.php" method="get" class="quick-search-form">
                            <div class="form-group">
                                <label for="regdno">Registration Number</label>
                                <input type="text" id="regdno" name="regdno" placeholder="Enter registration no..." required>
                            </div>
                            <button type="submit" class="btn btn-primary full-width">
                                <i class="fas fa-arrow-right"></i> Go to Profile
                            </button>
                        </form>
                    </div>

                    <!-- Right Side: Action Links -->
                    <div class="grid-col-right">
                        <div class="action-group">
                            <h4><i class="fas fa-users"></i> Student Tools</h4>
                            <div class="action-links">
                                <a href="manage_students.php">Manage All Students</a>
                                <a href="eligible_students.php">Advanced Student Search</a>
                            </div>
                        </div>
                        <div class="action-group">
                            <h4><i class="fas fa-briefcase"></i> Placement Tools</h4>
                            <div class="action-links">
                                <a href="manage_companies.php">Manage Companies</a>
                                <a href="manage_jobs.php">Manage Job Postings</a>
                                <a href="view_applications.php">View Job Applications</a>
                                <a href="manage_placements.php">Record & View Placements</a>
                            </div>
                        </div>
                        <div class="action-group">
                            <h4><i class="fas fa-database"></i> Data Tools</h4>
                            <div class="action-links">
                                <a href="insertdata.php">Bulk Import Marks (CSV)</a>
                                <a href="updatedata.php">Update Student Data</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>