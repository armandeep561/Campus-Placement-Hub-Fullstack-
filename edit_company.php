<?php
// edit_company.php

session_name("staff");
session_start();

// Ensure the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

require_once "config.php";

require_once "config.php";

$company_name = "";
$company_data = null;
$feedback_msg = "";
$feedback_class = "";

// --- Part 1: Handle form submission to UPDATE data ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['original_company_name'])) {
    $original_company_name = $_POST['original_company_name'];
    $new_company_name = $_POST['company_name'];
    $website = $_POST['website'];
    $description = $_POST['description'];

    $sql = "UPDATE company SET companyname = ?, website = ?, description = ? WHERE companyname = ?";
    if($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $new_company_name, $website, $description, $original_company_name);
        if($stmt->execute()) {
            $feedback_msg = "Company details updated successfully!";
            $feedback_class = "alert-success";
        } else {
            $feedback_msg = "Error updating record. Please check for duplicate names.";
            $feedback_class = "alert-error";
        }
        $stmt->close();
    }
    // Use the NEW name to re-fetch the data for display
    $company_name = $new_company_name;
} 
// --- Part 2: Handle initial page load to GET data ---
else if (isset($_GET['id'])) {
    $company_name = $_GET['id'];
}

// --- Part 3: Fetch the company's data to display in the form ---
if (!empty($company_name)) {
    $sql_fetch = "SELECT * FROM company WHERE companyname = ?";
    if($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("s", $company_name);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        if ($result->num_rows == 1) {
            $company_data = $result->fetch_assoc();
        } else {
            $feedback_msg = "Error: Company not found.";
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
    <title>Edit Company - Admin Dashboard</title>
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
        <video autoplay muted loop playsinline>
            <source src="bg.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
        <div class="dashboard-card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header">
                <h2>Edit Company Details</h2>
                <a href="manage_companies.php" class="btn btn-secondary">← Back to Company List</a>
            </div>

            <?php if(!empty($feedback_msg)): ?>
                <div class="alert <?php echo $feedback_class; ?>"><?php echo $feedback_msg; ?></div>
            <?php endif; ?>

            <?php if($company_data): ?>
            <form action="edit_company.php" method="post">
                <!-- Hidden field to track the original name in case it's changed -->
                <input type="hidden" name="original_company_name" value="<?php echo htmlspecialchars($company_data['companyname']); ?>">

                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company_data['companyname']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="website">Company Website</label>
                    <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($company_data['website']); ?>" placeholder="https://example.com">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($company_data['description']); ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>