<?php
    // BEST PRACTICE: Start the session at the very beginning of the script.
    session_start();

    // Now, include the database configuration file.
    require_once "config.php";
    
    // If the user is already logged in, redirect them immediately.
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
        header("location: student_profile.php");
        exit;
    }

    $login_error = "";

    // Check if the form was submitted.
    if (isset($_POST['submit'])){
        $uname = $_POST['uname'];
        $password = $_POST['password'];
        $regdno = $_POST['regdno'];

        // Prepare the SQL statement to prevent SQL Injection.
        $sql = "SELECT regdno, name, password FROM student WHERE regdno = ? AND name = ?";
        
        if($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $regdno, $uname);
            $stmt->execute();
            $stmt->store_result();
            
            // Check if exactly one user was found.
            if($stmt->num_rows == 1) {
                // Bind the result from the database to PHP variables.
                // We rename $db_password to $hashed_password for clarity.
                $stmt->bind_result($id, $name, $hashed_password);
                
                if($stmt->fetch()) {
                    // *** THE CRITICAL SECURITY UPGRADE ***
                    // Verify the submitted password against the hash stored in the database.
                    if(password_verify($password, $hashed_password)) {
                        
                        // Password is correct!
                        
                        // SECURITY FIX: Regenerate the session ID to prevent session fixation attacks.
                        session_regenerate_id(true);
                        
                        // Store user data in the session. (No need for a second session_start() call).
                        $_SESSION["loggedin"] = true;
                        $_SESSION["username"] = $name; 
                        $_SESSION["num"] = $id; 

                        // Redirect to the student's profile page.
                        header("location: student_profile.php");
                        exit(); 

                    } else {
                        // The password did not match the hash.
                        $login_error = "Invalid credentials. Please try again.";
                    }
                }
            } else {
                // No user was found with that registration number and name.
                $login_error = "Invalid credentials. Please try again.";
            }
            $stmt->close();
        } else {
            // This error happens if the database query itself fails.
            $login_error = "Oops! Something went wrong. Please try again later.";
        }
        $conn->close();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Campus Placement Hub</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="education.png">
</head>
<body class="login-page-body student-theme">

    <div class="video-background">
        <video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video>
    </div>

    <div class="login-container">
        <div class="login-box">
            <div class="icon-container student">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h2>Student Login</h2>
            
            <form action="student_login.php" method="post">
                <?php 
                    if(!empty($login_error)){
                        echo '<div class="alert alert-error">' . $login_error . '</div>';
                    }        
                ?>
                <div class="form-group">
                    <label for="regdno">Registration No:</label>
                    <input type="text" id="regdno" name="regdno" required>
                </div>
                <div class="form-group">
                    <label for="uname">Name:</label>
                    <input type="text" id="uname" name="uname" required>
                </div>
                
                <!-- ... other form groups ... -->
<div class="form-group">
    <label for="password">Password:</label>
    <div class="password-wrapper">
        <input type="password" id="password" name="password" required>
        <i class="fas fa-eye password-toggle-icon"></i>
    </div>
</div>

<!-- NEW: "Forgot Password?" Link -->
<div class="form-options">
    <a href="student_forgot_password.php">Reset Password?</a>
</div>

<button type="submit" name="submit" class="btn btn-secondary full-width">Sign In</button>
</form>
            <a href="mainpage.html" class="back-link">⇦ Go Back</a>
        </div>
    </div>
    
    <!-- NEW: JavaScript to handle the show/hide functionality -->
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