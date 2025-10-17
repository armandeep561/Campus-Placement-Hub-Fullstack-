<?php


// manage_companies.php (FINAL - UNIFIED SESSION)
session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'Admin'){
    header("location: staff_login.php");
    exit;
}
require_once "config.php";

// ... the rest of your original PHP code for this page is completely fine ...
// I will include it all here for a full replacement.

$feedback_msg = "";
$feedback_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_company'])) {
    if (empty(trim($_POST['company_name']))) {
        $feedback_msg = "Company name is required.";
        $feedback_class = "alert-error";
    } else {
        $sql = "INSERT INTO company (companyname, website, description) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $_POST['company_name'], $_POST['website'], $_POST['description']);
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "Company '" . htmlspecialchars($_POST['company_name']) . "' added successfully!";
                header("location: manage_companies.php");
                exit;
            } else {
                if ($conn->errno == 1062) { $feedback_msg = "Error: A company with this name already exists."; } 
                else { $feedback_msg = "Oops! Something went wrong."; }
                $feedback_class = "alert-error";
            }
            $stmt->close();
        }
    }
}

if (isset($_SESSION['flash_message'])) {
    $feedback_msg = $_SESSION['flash_message'];
    $feedback_class = "alert-success";
    unset($_SESSION['flash_message']);
}

$companies = [];
$sql_fetch = "SELECT companyname, companyname AS id, website, created_at FROM company ORDER BY companyname ASC";
if ($result = $conn->query($sql_fetch)) {
    while ($row = $result->fetch_assoc()) { $companies[] = $row; }
    $result->free();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Companies - Admin Dashboard</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.0/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="education.png">
</head>
<body class="dashboard-body admin-theme">
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
    <main class="dashboard-main">
        <div class="video-background"><video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video></div>
        <div class="content-wrapper">
            <div style="max-width: 1200px; margin: 0 auto 2rem auto;">
                <?php if(!empty($feedback_msg) && $feedback_class !== 'alert-success'){ echo '<div class="alert ' . $feedback_class . '">' . $feedback_msg . '</div>'; } ?>
            </div>
            <div class="dashboard-grid" style="grid-template-columns: 1fr 2fr; max-width: 1200px; margin: 0 auto;">
                <div class="dashboard-card">
                    <div class="card-header"><h2><i class="fas fa-plus-circle"></i> Add New Company</h2></div>
                    <form action="manage_companies.php" method="post">
                        <div class="form-group"><label for="company_name">Company Name</label><input type="text" id="company_name" name="company_name" required></div>
                        <div class="form-group"><label for="website">Website</label><input type="url" id="website" name="website" placeholder="https://example.com"></div>
                        <div class="form-group"><label for="description">Description</label><textarea id="description" name="description" rows="4"></textarea></div>
                        <div class="form-actions"><button type="submit" name="add_company" class="btn btn-primary full-width"><i class="fas fa-building"></i> Add Company</button></div>
                    </form>
                </div>
                <div class="dashboard-card">
                    <div class="card-header"><h2><i class="fas fa-list-ul"></i> Companies (<?php echo count($companies); ?>)</h2><a href="staff_access.php" class="btn btn-secondary">← Back</a></div>
                    <div class="table-container">
                        <table>
                            <thead><tr><th>Name</th><th>Website</th><th>Added</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($companies as $company): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($company['companyname']); ?></strong></td>
                                        <td><a href="<?php echo htmlspecialchars($company['website']); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($company['website']); ?></a></td>
                                        <td><?php echo date("d M, Y", strtotime($company['created_at'])); ?></td>
                                        <td class="actions">
                                            <a href="edit_company.php?id=<?php echo urlencode($company['id']); ?>" class="btn btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="delete_company.php?id=<?php echo urlencode($company['id']); ?>" class="btn btn-icon btn-danger" title="Delete" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div id="toast-notification" class="toast"><i class="fas fa-check-circle"></i><span id="toast-message"></span></div>
    <script>
        <?php if (isset($feedback_msg) && $feedback_class === 'alert-success'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const toast=document.getElementById('toast-notification'),toastMessage=document.getElementById('toast-message');
                if(toast&&toastMessage){toastMessage.textContent='<?php echo addslashes($feedback_msg); ?>';toast.classList.add('show');setTimeout(()=>toast.classList.remove('show'),4000);}
            });
        <?php endif; ?>
    </script>
</body>
</html>