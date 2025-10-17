<?php
// edit_student.php (Version 3.1 - Complete and Corrected)

require_once "config.php";
session_name("staff");
session_start();
 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

$regdno = "";
$student_data = [];
$feedback_message = "";
$feedback_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['regdno'])) {
    $regdno = $_POST['regdno'];
    $conn->begin_transaction();
    try {
        $sql_student = "UPDATE student SET name = ?, department = ?, contact = ?, email = ?, dob = ?, placement_status = ?, last_updated_by = ?, last_updated_on = NOW() WHERE regdno = ?";
        $stmt_student = $conn->prepare($sql_student);
        $admin_username = $_SESSION["username"];
        $stmt_student->bind_param("ssssssss", $_POST['name'], $_POST['department'], $_POST['contact'], $_POST['email'], $_POST['dob'], $_POST['placement_status'], $admin_username, $regdno);
        $stmt_student->execute();
        $stmt_student->close();

        $sql_marks = "INSERT INTO marks (regdno, cgpa, backlogs) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE cgpa = VALUES(cgpa), backlogs = VALUES(backlogs)";
        $stmt_marks = $conn->prepare($sql_marks);
        $stmt_marks->bind_param("sdi", $regdno, $_POST['cgpa'], $_POST['backlogs']);
        $stmt_marks->execute();
        $stmt_marks->close();

        $conn->commit();
        $feedback_message = "Success! Student profile has been updated.";
        $feedback_class = "alert-success";
    } catch (Exception $e) {
        $conn->rollback();
        $feedback_message = "Error: Could not update the record.";
        $feedback_class = "alert-error";
    }
} 
else if (isset($_GET['regdno'])) {
    $regdno = $_GET['regdno'];
}

if (!empty($regdno)) {
    $sql_fetch = "SELECT s.*, m.cgpa, m.backlogs FROM student s LEFT JOIN marks m ON s.regdno = m.regdno WHERE s.regdno = ?";
    if($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("s", $regdno);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        if ($result->num_rows == 1) {
            $student_data = $result->fetch_assoc();
        } else {
            $feedback_message = "Error: No student found with that registration number.";
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
    <title>Edit Student Profile - Admin Dashboard</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="education.png">
</head>
<body class="dashboard-body admin-theme">

    <header class="dashboard-header">
        <div class="header-content"><i class="fas fa-university logo-icon"></i><h1 class="portal-title">Campus Placement Hub</h1></div>
        <div class="user-actions">
            <span class="welcome-user">Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong></span>
            <a href="staff_logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <main class="dashboard-main">
        <div class="video-background"><video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video></div>
        <div class="content-wrapper">
            <div class="dashboard-card" style="max-width: 800px; margin: 0 auto;">
                <div class="card-header">
                    <h2>Edit Student Profile</h2>
                    <a href="manage_students.php" class="btn btn-secondary">← Back to Student List</a>
                </div>
                <?php if(!empty($feedback_message)): ?>
                    <div class="alert <?php echo $feedback_class; ?>"><?php echo $feedback_message; ?></div>
                <?php endif; ?>

                <?php if($student_data): ?>
                <form action="edit_student.php" method="post">
                    <input type="hidden" name="regdno" value="<?php echo htmlspecialchars($student_data['regdno']); ?>">

                    <h3 class="form-section-header">Personal Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="regdno_display">Registration Number</label>
                            <input type="text" id="regdno_display" value="<?php echo htmlspecialchars($student_data['regdno']); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student_data['name']); ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <div class="select-wrapper">
    <select id="department" name="department" required>
        <option value="">-- Select Department --</option>
        <option value="CSE" <?php if(($student_data['department'] ?? '') == 'CSE') echo 'selected'; ?>>Computer Science (CSE)</option>
        <option value="ECE" <?php if(($student_data['department'] ?? '') == 'ECE') echo 'selected'; ?>>Electronics & Comm. (ECE)</option>
        <option value="ME" <?php if(($student_data['department'] ?? '') == 'ME') echo 'selected'; ?>>Mechanical (ME)</option>
        <option value="CE" <?php if(($student_data['department'] ?? '') == 'CE') echo 'selected'; ?>>Civil (CE)</option>
        <option value="EEE" <?php if(($student_data['department'] ?? '') == 'EEE') echo 'selected'; ?>>Electrical & Electronics (EEE)</option>
        <!-- CORRECTED VALUE -->
        <option value="BCA" <?php if(($student_data['department'] ?? '') == 'BCA') echo 'selected'; ?>>Bachelor of Computer Applications (BCA)</option>
    </select>
</div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="contact">Contact Number</label><input type="tel" id="contact" name="contact" value="<?php echo htmlspecialchars($student_data['contact'] ?? ''); ?>"></div>
                        <div class="form-group"><label for="email">Email Address</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student_data['email'] ?? ''); ?>"></div>
                    </div>
                    <div class="form-group"><label for="dob">Date of Birth</label><input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($student_data['dob'] ?? ''); ?>"></div>

                    <!-- THIS SECTION IS NOW RESTORED -->
                    <h3 class="form-section-header">Academic & Placement Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cgpa">CGPA</label>
                            <input type="number" step="0.01" id="cgpa" name="cgpa" value="<?php echo htmlspecialchars($student_data['cgpa'] ?? '0.0'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="backlogs">Active Backlogs</label>
                            <input type="number" id="backlogs" name="backlogs" value="<?php echo htmlspecialchars($student_data['backlogs'] ?? '0'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="placement_status">Placement Status</label>
                            <div class="select-wrapper">
                                <select id="placement_status" name="placement_status">
                                    <option value="Not Placed" <?php echo ($student_data['placement_status'] ?? '') == 'Not Placed' ? 'selected' : ''; ?>>Not Placed</option>
                                    <option value="Placed" <?php echo ($student_data['placement_status'] ?? '') == 'Placed' ? 'selected' : ''; ?>>Placed</option>
                                    <option value="Seeking Better Offer" <?php echo ($student_data['placement_status'] ?? '') == 'Seeking Better Offer' ? 'selected' : ''; ?>>Seeking Better Offer</option>
                                    <option value="Not Interested" <?php echo ($student_data['placement_status'] ?? '') == 'Not Interested' ? 'selected' : ''; ?>>Not Interested</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    </div>
                    
                    <?php if(!empty($student_data['last_updated_by'])): ?>
                    <div class="audit-trail">
                        Last updated by <strong><?php echo htmlspecialchars($student_data['last_updated_by']); ?></strong> on <?php echo date("d M Y, h:i A", strtotime($student_data['last_updated_on'])); ?>
                    </div>
                    <?php endif; ?>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>