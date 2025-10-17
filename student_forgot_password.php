<?php
// student_forgot_password.php - Standalone Password Change Page for Students

require_once "config.php";
session_start();

$feedback_msg = "";
$feedback_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $regdno = $_POST['regdno'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Validation ---
    if(empty($regdno) || empty($current_password) || empty($new_password) || empty($confirm_password)){
        $feedback_msg = "Please fill in all fields.";
        $feedback_class = "alert-error";
    } elseif ($new_password !== $confirm_password) {
        $feedback_msg = "New password and confirm password do not match.";
        $feedback_class = "alert-error";
    } elseif (strlen($new_password) < 6) {
        $feedback_msg = "New password must be at least 6 characters long.";
        $feedback_class = "alert-error";
    } else {
        // --- SECURE Logic to Change Password ---
        // 1. Get the student's current hashed password from the DB
        $sql = "SELECT password FROM student WHERE regdno = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $regdno);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // 2. Verify if the "current password" they typed matches the hash in the DB
        if ($user && password_verify($current_password, $user['password'])) {
            // 3. If it matches, hash the new password and update the database
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $sql_update = "UPDATE student SET password = ? WHERE regdno = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ss", $new_hashed_password, $regdno);
            
            if($stmt_update->execute()){
                $feedback_msg = "Password changed successfully! You can now log in with your new password.";
                $feedback_class = "alert-success";
            } else {
                $feedback_msg = "Oops! Something went wrong. Please try again.";
                $feedback_class = "alert-error";
            }
            $stmt_update->close();
        } else {
            $feedback_msg = "Incorrect Registration Number or Current Password.";
            $feedback_class = "alert-error";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Student Password</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="education.png">
</head>
<body class="login-page-body student-theme">

    <div class="video-background">
        <video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video>
    </div>

    <div class="login-container">
        <div class="login-box">
            <div class="icon-container student"><i class="fas fa-key"></i></div>
            <h2>Change Student Password</h2>

            <?php if(!empty($feedback_msg)): ?>
                <div class="alert <?php echo $feedback_class; ?>"><?php echo $feedback_msg; ?></div>
            <?php endif; ?>

            <form action="student_forgot_password.php" method="post">
                <div class="form-group">
                    <label for="regdno">Your Registration Number</label>
                    <input type="text" name="regdno" id="regdno" required>
                </div>
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="current_password" id="current_password" required>
                        <i class="fas fa-eye password-toggle-icon"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="new_password" id="new_password" required>
                        <i class="fas fa-eye password-toggle-icon"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password" required>
                        <i class="fas fa-eye password-toggle-icon"></i>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary full-width">Update Password</button>
                </div>
            </form>
            <a href="student_login.php" class="back-link">⇦ Back to Login</a>
        </div>
    </div>

<script>
    // This script is identical to the staff version and will work perfectly
    const toggleIcons = document.querySelectorAll('.password-toggle-icon');
    toggleIcons.forEach(icon => {
        icon.addEventListener('click', function () {
            const passwordInput = this.previousElementSibling;
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    });
</script>
</body>
</html>