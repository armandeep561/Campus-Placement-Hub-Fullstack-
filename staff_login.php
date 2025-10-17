<?php
// staff_login.php (FINAL - SECURE & Admin-Only)
session_name("staff");
session_start();
require_once "config.php";

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: staff_access.php");
    exit;
}
$login_error = ""; 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])){
    $uname = trim($_POST['uname']);
    $password = trim($_POST['password']);
    if(empty($uname) || empty($password)){
        $login_error = "Please enter username and password.";
    } else {
        $sql = "SELECT staff_name, password, role FROM staff WHERE staff_name = ? AND role = 'Admin'";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $uname);
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){
                    $stmt->bind_result($staff_name, $hashed_password, $role);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            session_regenerate_id(true);
                            $_SESSION["loggedin"] = true;
                            $_SESSION["username"] = $staff_name;
                            $_SESSION["role"] = $role;
                            header("location: staff_access.php");
                            exit();
                        } else {
                            $login_error = "Invalid credentials.";
                        }
                    }
                } else {
                    $login_error = "Invalid credentials or not an Admin account.";
                }
            }
            $stmt->close();
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
    <title>Admin Login - Placement Portal</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="education.png">
</head>
<body class="login-page-body">
    <div class="video-background"><video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video></div>
    <div class="login-container">
        <div class="login-box">
            <div class="icon-container admin"><i class="fas fa-user-shield"></i></div>
            <h2>Admin Login</h2>
            <form action="staff_login.php" method="post">
                <?php if(!empty($login_error)){ echo '<div class="alert alert-error">' . $login_error . '</div>'; } ?>
                <div class="form-group"><label for="uname">Username:</label><input type="text" id="uname" name="uname" required></div>
                <div class="form-group"><label for="password">Password:</label><div class="password-wrapper"><input type="password" id="password" name="password" required><i class="fas fa-eye password-toggle-icon"></i></div></div>
                <div class="form-options"><a href="staff_forgot_password.php">Reset Password?</a></div>
                <button type="submit" name="submit" class="btn btn-primary full-width">Sign In</button>
            </form>
            <a href="mainpage.html" class="back-link">⇦ Go Back</a>
        </div>
    </div>
<script>
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