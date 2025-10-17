<?php
// staff_forgot_password.php (FINAL - SECURE)

require_once "config.php";
session_name("staff");
session_start();

$feedback_msg = "";
$feedback_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['uname'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if(empty($username) || empty($current_password) || empty($new_password) || empty($confirm_password)){
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
        // 1. Get the user's current HASHED password from the DB
        $sql = "SELECT password FROM staff WHERE staff_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // 2. SECURELY VERIFY if the "current password" matches the hash
        if ($user && password_verify($current_password, $user['password'])) {
            // 3. If it matches, HASH the new password and update the database
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $sql_update = "UPDATE staff SET password = ? WHERE staff_name = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ss", $new_hashed_password, $username);
            
            if($stmt_update->execute()){
                $feedback_msg = "Password changed successfully! You can now log in with your new password.";
                $feedback_class = "alert-success";
            } else {
                $feedback_msg = "Oops! Something went wrong. Please try again.";
                $feedback_class = "alert-error";
            }
            $stmt_update->close();
        } else {
            $feedback_msg = "Incorrect username or current password.";
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
    <title>Change Staff Password</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="education.png">
</head>
<body class="login-page-body">

    <div class="video-background">
        <video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video>
    </div>

    <div class="login-container">
        <div class="login-box">
            <div class="icon-container admin"><i class="fas fa-key"></i></div>
            <h2>Change Admin Password</h2>

            <?php if(!empty($feedback_msg)): ?>
                <div class="alert <?php echo $feedback_class; ?>"><?php echo $feedback_msg; ?></div>
            <?php endif; ?>

            <form action="staff_forgot_password.php" method="post">
    <div class="form-group">
        <label for="uname">Your Username</label>
        <input type="text" name="uname" id="uname" required>
    </div>

    <!-- UPDATED: Current Password Field -->
    <div class="form-group">
        <label for="current_password">Current Password</label>
        <div class="password-wrapper">
            <input type="password" name="current_password" id="current_password" required>
            <i class="fas fa-eye password-toggle-icon"></i>
        </div>
    </div>

    <!-- UPDATED: New Password Field -->
    <div class="form-group">
        <label for="new_password">New Password</label>
        <div class="password-wrapper">
            <input type="password" name="new_password" id="new_password" required>
            <i class="fas fa-eye password-toggle-icon"></i>
        </div>
    </div>

    <!-- UPDATED: Confirm New Password Field -->
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
<a href="staff_login.php" class="back-link">⇦ Back to Login</a>
        </div>
    </div>
    <script>
    const toggleIcons = document.querySelectorAll('.password-toggle-icon');

    toggleIcons.forEach(icon => {
        icon.addEventListener('click', function () {
            // Find the input field right before the icon inside the wrapper
            const passwordInput = this.previousElementSibling;
            
            // Toggle the input type between 'password' and 'text'
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle the icon's appearance between 'fa-eye' and 'fa-eye-slash'
            this.classList.toggle('fa-eye-slash');
        });
    });
</script>
</body>
</html>