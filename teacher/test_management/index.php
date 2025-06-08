<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'C:/xampp/htdocs/Academy/database.php';

// Your database connection file

// Check if user is logged in (you should implement your own auth system)
if (!isset($_SESSION['id_user'])) {
    header("Location:../../login.html");
    exit();
}

$user_id = (int)$_SESSION['id_user'];

// Get courses created by this user
$courses = [];
$stmt = $conn->prepare("SELECT id, title FROM courses WHERE id_users = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $courses[$row['id']] = $row['title'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Test Management</h1>
        
        <!-- Course Selection -->
        <div class="course-selector">
            <label for="course-select">Select Course:</label>
            <select id="course-select" onchange="loadSections(this.value)">
                <option value="">-- Select Course --</option>
                <?php foreach ($courses as $id => $title): ?>
                    <option value="<?= $id ?>"><?= htmlspecialchars($title) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Sections will be loaded here -->
        <div id="sections-container" class="sections-container"></div>
        
        <!-- Tests will be loaded when section is selected -->
        <div id="tests-container" class="tests-container"></div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>