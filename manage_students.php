<?php
// manage_students.php (Version 3.0 - With HOD Filtering)

session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'Admin'){
    header("location: staff_login.php");
    exit;
}
require_once "config.php";

$add_error = "";
$add_success = "";
$is_hod = (isset($_SESSION['role']) && $_SESSION['role'] === 'Hod');
$department = $_SESSION['department'] ?? '';

// --- Logic to add a student ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_student'])) {
    // Admins can add students to any department. HODs can only add to their own.
    $regdno = trim($_POST['regdno']);
    $name = trim($_POST['name']);
    $student_department = $is_hod ? $department : trim($_POST['department']); // HODs are forced to their dept
    $password = trim($_POST['password']);
    
    // (Validation logic remains the same)
    if (empty($regdno) || empty($name) || empty($student_department) || empty($password)) {
        $add_error = "Please fill in all fields.";
    } else {
        $plain_password = $password; 
        $sql = "INSERT INTO student (regdno, name, department, password) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssss", $regdno, $name, $student_department, $plain_password);
            if ($stmt->execute()) {
                $add_success = "Student added successfully!";
            } else {
                if ($conn->errno == 1062) { $add_error = "Error: A student with this registration number already exists."; } 
                else { $add_error = "Oops! Something went wrong."; }
            }
            $stmt->close();
        }
    }
}

// --- NEW: ROLE-AWARE LOGIC TO FETCH STUDENTS ---
$students = [];
$sql_fetch = "SELECT regdno, name, department FROM student";
$params = [];
$types = "";

if ($is_hod) {
    // If the user is an HOD, add a WHERE clause to the query
    $sql_fetch .= " WHERE department = ?";
    $params[] = $department;
    $types .= "s";
}
$sql_fetch .= " ORDER BY name ASC";

if ($conn->connect_error) { require "config.php"; }

$stmt_fetch = $conn->prepare($sql_fetch);
if (!empty($types)) {
    $stmt_fetch->bind_param($types, ...$params);
}
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt_fetch->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - <?php echo $is_hod ? htmlspecialchars($department) : 'Admin'; ?> Dashboard</title>
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
        <div class="video-background"><video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video></div>
        <div class="content-wrapper">
            <div class="dashboard-card" style="max-width: 900px; margin: 0 auto 2rem auto;">
                <div class="card-header"><h2><i class="fas fa-user-plus"></i> Add New Student</h2>
                <a href="<?php echo $is_hod ? 'hod_dashboard.php' : 'staff_access.php'; ?>" class="btn btn-secondary">← Back to Dashboard</a></div>
                <form action="manage_students.php" method="post">
                    <?php if(!empty($add_error)){ echo '<div class="alert alert-error">' . $add_error . '</div>'; } ?>
                    <?php if(!empty($add_success)){ echo '<div class="alert alert-success">' . $add_success . '</div>'; } ?>
                    <div class="form-row" style="grid-template-columns: 1fr 1fr;">
                        <div class="form-group"><label for="regdno">Registration No:</label><input type="text" id="regdno" name="regdno" required pattern="[0-9]+" inputmode="numeric" title="Numbers only."></div>
                        <div class="form-group"><label for="name">Full Name:</label><input type="text" id="name" name="name" required></div>
                    </div>
                    <div class="form-row" style="grid-template-columns: 1fr 1fr;">
                        <div class="form-group">
                            <label for="department">Department</label>
                            <?php if($is_hod): ?>
                                <!-- HOD sees their own department, but it's hidden as they can't change it -->
                                <input type="hidden" name="department" value="<?php echo htmlspecialchars($department); ?>">
                                <input type="text" value="<?php echo htmlspecialchars($department); ?>" disabled>
                            <?php else: ?>
                                <!-- Admin sees a dropdown to choose any department -->
                                <div class="select-wrapper">
                                    <select id="department" name="department" required>
                                        <option value="">-- Select Department --</option>
                                        <option value="CSE">Computer Science (CSE)</option>
                                        <option value="ECE">Electronics & Comm. (ECE)</option>
                                        <option value="ME">Mechanical (ME)</option>
                                        <option value="CE">Civil (CE)</option>
                                        <option value="EEE">Electrical & Electronics (EEE)</option>
                                        <option value="BCA">Bachelor of Computer Applications (BCA)</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group"><label for="password">Password:</label><div class="password-wrapper"><input type="password" id="password" name="password" required><i class="fas fa-eye password-toggle-icon"></i></div></div>
                    </div>
                    <div class="form-actions"><button type="submit" name="add_student" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Student</button></div>
                </form>
            </div>
            <div class="dashboard-card" style="max-width: 900px; margin: 0 auto;">
                <div class="card-header"><h2>Existing Students <?php if($is_hod) echo " in " . htmlspecialchars($department); ?> (<?php echo count($students); ?>)</h2></div>
                <div class="table-container">
                    <table>
                        <thead><tr><th>Reg. No</th><th>Name</th><th>Department</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['regdno']); ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['department'] ?? 'N/A'); ?></td>
                                    <td class="actions">
                                        <a href="edit_student.php?regdno=<?php echo urlencode($student['regdno']); ?>" class="btn btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-icon btn-danger open-delete-modal" data-regdno="<?php echo htmlspecialchars($student['regdno']); ?>" title="Delete"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <div id="custom-confirm-modal" class="modal-overlay" style="display: none;"><!-- COMPLETE MODAL HTML -->
    <div id="custom-confirm-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <h3 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
            <p id="modal-text">Are you sure you want to delete this student? This will remove all their associated records and cannot be undone.</p>
            <div class="modal-actions">
                <button id="modal-btn-cancel" class="btn btn-secondary">Cancel</button>
                <a id="modal-btn-confirm" href="#" class="btn btn-danger">Yes, Delete</a>
            </div>
        </div>
    </div></div>
    <script>document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. LOGIC FOR CUSTOM DELETE MODAL ---
    const modal = document.getElementById('custom-confirm-modal');
    if (modal) {
        const confirmBtn = document.getElementById('modal-btn-confirm');
        const cancelBtn = document.getElementById('modal-btn-cancel');
        const deleteButtons = document.querySelectorAll('.open-delete-modal');

        const openModal = (regdno) => {
            const deleteUrl = `delete_student.php?regdno=${encodeURIComponent(regdno)}`;
            confirmBtn.setAttribute('href', deleteUrl);
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        };

        const closeModal = () => {
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
        };

        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                openModal(this.dataset.regdno);
            });
        });

        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    // --- 2. LOGIC FOR PASSWORD VISIBILITY TOGGLE ---
    const passwordToggleIcons = document.querySelectorAll('.password-toggle-icon');
    passwordToggleIcons.forEach(icon => {
        icon.addEventListener('click', function () {
            const passwordInput = this.previousElementSibling;
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    });

});</script>
</body>
</html>