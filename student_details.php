<?php
   include 'config.php';
   session_name("staff");
   session_start();
 
// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}
 
    // Get filter criteria from the form submission
    $backlog = $_POST['backlogs'];
    $cgpa = $_POST['cgpa'];    
    $company = $_POST['company'];

    // Store criteria in session for the download link
    $_SESSION["cgpa"] = $cgpa;
    $_SESSION["backlog"] = $backlog;
    $_SESSION["company"] = $company;

    // --- MAJOR IMPROVEMENT: A single, efficient, and secure query to get all data ---
    $sql = "SELECT s.regdno, s.name, s.contact, s.email, s.dob, m.backlogs, m.cgpa 
            FROM student s
            JOIN marks m ON s.regdno = m.regdno
            WHERE m.cgpa >= ? AND m.backlogs <= ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $cgpa, $backlog); // 'd' for double (cgpa), 'i' for integer (backlog)
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all results into an array
    $student_list = $result->fetch_all(MYSQLI_ASSOC);  
?>
  
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filtered Student List - Admin Dashboard</title>
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
            <a href="stud_logout.php" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </header>

    <main class="dashboard-main">
        <!-- Video Background (optional) -->
    <div class="video-background">
        <video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video>
    </div>
        <div class="dashboard-card">
            <div class="card-header">
                <h2>Filtered Student List</h2>
                <a href="staff_access.php" class="btn btn-secondary">← Back to Filters</a>
            </div>
            <p class="filter-criteria">
                Showing students eligible for <strong><?php echo htmlspecialchars($company); ?></strong> with a minimum CGPA of <strong><?php echo htmlspecialchars($cgpa); ?></strong> and a maximum of <strong><?php echo htmlspecialchars($backlog); ?></strong> backlogs.
            </p>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Regd. No</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>D.O.B</th>
                            <th>Backlogs</th>
                            <th>CGPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($student_list)): ?>
                            <?php foreach($student_list as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($company);?></td>
                                <td><?php echo htmlspecialchars($student['regdno']);?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['contact']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['dob']); ?></td>
                                <td><?php echo htmlspecialchars($student['backlogs']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($student['cgpa'], 2)); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="no-records">No students found matching the selected criteria.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(!empty($student_list)): ?>
            <div class="form-actions" style="margin-top: 2rem;">
                <a href="generatefile.php" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download as CSV
                </a>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
  
<?php   
    $stmt->close();
    mysqli_close($conn);
?>