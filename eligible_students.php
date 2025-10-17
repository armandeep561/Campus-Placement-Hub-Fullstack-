<?php
// eligible_students.php

session_name("staff");
session_start();

// Ensure the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: staff_login.php");
    exit;
}

require_once "config.php";

// --- Initialize Search Parameters & Results ---
$min_cgpa = $_GET['min_cgpa'] ?? '';
$max_backlogs = $_GET['max_backlogs'] ?? '';
$placement_status = $_GET['placement_status'] ?? '';
$student_name = $_GET['student_name'] ?? '';
$results = [];
$search_performed = !empty($min_cgpa) || ($max_backlogs !== '') || !empty($placement_status) || !empty($student_name);

// --- Build and Execute the Search Query ---
if ($search_performed) {
    // Base query with a LEFT JOIN to include marks data for all students
    $sql = "SELECT s.regdno, s.name, s.email, m.cgpa, m.backlogs, s.placement_status
            FROM student s
            LEFT JOIN marks m ON s.regdno = m.regdno
            WHERE 1=1"; // Start with a true condition to easily append filters

    $params = [];
    $types = '';

    // Append filters based on user input
    if (!empty($student_name)) {
        $sql .= " AND s.name LIKE ?";
        $types .= 's';
        $params[] = '%' . $student_name . '%';
    }
    if (!empty($min_cgpa)) {
        $sql .= " AND m.cgpa >= ?";
        $types .= 'd';
        $params[] = $min_cgpa;
    }
    if ($max_backlogs !== '' && is_numeric($max_backlogs)) {
        $sql .= " AND m.backlogs <= ?";
        $types .= 'i';
        $params[] = $max_backlogs;
    }
    if (!empty($placement_status)) {
        $sql .= " AND s.placement_status = ?";
        $types .= 's';
        $params[] = $placement_status;
    }

    $sql .= " ORDER BY m.cgpa DESC, s.name ASC";

    if($stmt = $conn->prepare($sql)) {
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Eligible Students - Admin Dashboard</title>
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
        <!-- UPDATED HEADER -->
        <div class="user-actions">
            <span class="welcome-user">Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong></span>
            <a href="staff_logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>
    </header>

    <main class="dashboard-main">
        <div class="video-background">
            <video autoplay muted loop playsinline><source src="bg.mp4" type="video/mp4"></video>
        </div>

        <div class="dashboard-card" style="max-width: 1200px; margin: 0 auto;">
            <div class="card-header">
                <h2>Find Eligible Students</h2>
                <a href="staff_access.php" class="btn btn-secondary">← Back to Dashboard</a>
            </div>

            <!-- Search & Filter Form -->
            <form action="eligible_students.php" method="get" class="filter-form">
                <div class="form-group">
                    <label for="student_name">Student Name</label>
                    <input type="text" name="student_name" id="student_name" placeholder="Search by name..." value="<?php echo htmlspecialchars($student_name); ?>">
                </div>
                <div class="form-group">
                    <label for="min_cgpa">Minimum CGPA</label>
                    <input type="number" step="0.1" name="min_cgpa" id="min_cgpa" placeholder="e.g., 7.5" value="<?php echo htmlspecialchars($min_cgpa); ?>">
                </div>
                <div class="form-group">
                    <label for="max_backlogs">Max Backlogs</label>
                    <input type="number" name="max_backlogs" id="max_backlogs" placeholder="e.g., 0" value="<?php echo htmlspecialchars($max_backlogs); ?>">
                </div>
                <div class="form-group">
                    <label for="placement_status">Placement Status</label>
                    <div class="select-wrapper">
                        <select name="placement_status" id="placement_status">
                            <option value="">Any Status</option>
                            <option value="Not Placed" <?php echo ($placement_status == 'Not Placed') ? 'selected' : ''; ?>>Not Placed</option>
                            <option value="Placed" <?php echo ($placement_status == 'Placed') ? 'selected' : ''; ?>>Placed</option>
                            <option value="Seeking Better Offer" <?php echo ($placement_status == 'Seeking Better Offer') ? 'selected' : ''; ?>>Seeking Better Offer</option>
                            <option value="Not Interested" <?php echo ($placement_status == 'Not Interested') ? 'selected' : ''; ?>>Not Interested</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="eligible_students.php" class="btn">Clear</a>
                </div>
            </form>

            <!-- Results Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Reg. No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>CGPA</th>
                            <th>Backlogs</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($search_performed && empty($results)): ?>
                            <tr>
                                <td colspan="7">No students found matching your criteria. Please adjust your filters.</td>
                            </tr>
                        <?php elseif (!empty($results)): ?>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['regdno']); ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['cgpa'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['backlogs'] ?? 'N/A'); ?></td>
                                    <td><span class="status-badge <?php echo str_replace(' ', '-', strtolower($row['placement_status'] ?? '')); ?>"><?php echo htmlspecialchars($row['placement_status'] ?? 'Unknown'); ?></span></td>
                                    <td class="actions">
                                        <a href="edit_student.php?regdno=<?php echo urlencode($row['regdno']); ?>" class="btn btn-icon" title="Edit Profile"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                             <tr>
                                <td colspan="7">Use the filters above to search for eligible students.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>```

