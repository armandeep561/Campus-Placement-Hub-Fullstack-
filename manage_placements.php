<?php
// manage_placements.php

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

// --- LOGIC TO RECORD A NEW PLACEMENT ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_placement'])) {
    $student_regdno = $_POST['student_regdno'];
    $job_id = $_POST['job_id'];

    if (empty($student_regdno) || empty($job_id)) {
        $feedback_msg = "Please select both a student and a job.";
        $feedback_class = "alert-error";
    } else {
        // Use a transaction to ensure both operations succeed or fail together
        $conn->begin_transaction();
        try {
            // 1. Insert the new record into the placements table
            $sql_insert = "INSERT INTO placements (student_regdno, job_id, package_lpa) SELECT ?, ?, package_lpa FROM jobs WHERE job_id = ?";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sii", $student_regdno, $job_id, $job_id);
            $stmt_insert->execute();
            $stmt_insert->close();

            // 2. Update the student's status in the student table
            $sql_update = "UPDATE student SET placement_status = 'Placed' WHERE regdno = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("s", $student_regdno);
            $stmt_update->execute();
            $stmt_update->close();

            // If both queries succeed, commit the transaction
            $conn->commit();
            $_SESSION['flash_message'] = "Placement recorded successfully! Student status has been updated to 'Placed'.";
            header("location: manage_placements.php");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            // Check for duplicate entry error (student already placed)
            if ($conn->errno == 1062) {
                $feedback_msg = "Error: This student has already been recorded as placed.";
            } else {
                $feedback_msg = "An error occurred. Please try again. " . $e->getMessage();
            }
            $feedback_class = "alert-error";
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
// 1. Fetch all existing placements for the view table
$placements = [];
$sql_fetch_placements = "SELECT p.placement_id, s.name, j.company_name, j.job_title, p.package_lpa, p.placement_date
                        FROM placements p
                        JOIN student s ON p.student_regdno = s.regdno
                        LEFT JOIN jobs j ON p.job_id = j.job_id
                        ORDER BY p.placement_date DESC";
if($result = $conn->query($sql_fetch_placements)){
    while($row = $result->fetch_assoc()){
        $placements[] = $row;
    }
    $result->free();
}

// 2. Fetch "Not Placed" students for the dropdown
$students = [];
$sql_fetch_students = "SELECT regdno, name FROM student WHERE placement_status = 'Not Placed' ORDER BY name ASC";
if($result = $conn->query($sql_fetch_students)){
    while($row = $result->fetch_assoc()){
        $students[] = $row;
    }
    $result->free();
}

// 3. Fetch all jobs for the dropdown
$jobs = [];
$sql_fetch_jobs = "SELECT job_id, job_title, company_name FROM jobs ORDER BY company_name, job_title ASC";
if($result = $conn->query($sql_fetch_jobs)){
    while($row = $result->fetch_assoc()){
        $jobs[] = $row;
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
    <title>Manage Placements - Admin Dashboard</title>
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
            <!-- SECTION 1: RECORD NEW PLACEMENT -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-handshake"></i> Record a New Placement</h2>
                </div>
                <form action="manage_placements.php" method="post">
                    <div class="form-group">
                        <label for="student_regdno">Select Student (Not Placed)</label>
                        <div class="select-wrapper">
                            <select id="student_regdno" name="student_regdno" required>
                                <option value="">-- Select a Student --</option>
                                <?php foreach($students as $student): ?>
                                    <option value="<?php echo htmlspecialchars($student['regdno']); ?>"><?php echo htmlspecialchars($student['name']) . ' (' . htmlspecialchars($student['regdno']) . ')'; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="job_id">Select Job Posting</label>
                        <div class="select-wrapper">
                            <select id="job_id" name="job_id" required>
                                <option value="">-- Select a Job --</option>
                                <?php foreach($jobs as $job): ?>
                                    <option value="<?php echo $job['job_id']; ?>"><?php echo htmlspecialchars($job['company_name']) . ' - ' . htmlspecialchars($job['job_title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="add_placement" class="btn btn-primary full-width">
                            <i class="fas fa-check-circle"></i> Confirm Placement
                        </button>
                    </div>
                </form>
            </div>

            <!-- SECTION 2: VIEW ALL PLACEMENTS -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-award"></i> Confirmed Placements (<?php echo count($placements); ?>)</h2>
                    <a href="staff_access.php" class="btn btn-secondary">← Back to Dashboard</a>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Company</th>
                                <th>Job Title</th>
                                <th>Package (LPA)</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($placements)): ?>
                                <tr><td colspan="6">No placements recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($placements as $placement): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($placement['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($placement['company_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($placement['job_title'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($placement['package_lpa'] ?? 'N/A'); ?></td>
                                        <td><?php echo date("d M, Y", strtotime($placement['placement_date'])); ?></td>
                                        <td class="actions">
                                            <a href="delete_placement.php?id=<?php echo $placement['placement_id']; ?>" class="btn btn-icon btn-danger" title="Remove Placement" onclick="return confirm('Are you sure you want to remove this placement record? This will also set the student\'s status back to Not Placed.');"><i class="fas fa-trash-alt"></i></a>
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