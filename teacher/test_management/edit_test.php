<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'C:/xampp/htdocs/Academy/database.php';


if (!isset($_SESSION['id_user'])) {
    header("Location:../../login.html");
    exit();
}

$user_id = $_SESSION['id_user'];

$test_id = $_GET['test_id'] ?? 0;

// Get test info
$test = [];
$stmt = $conn->prepare("SELECT * FROM tests WHERE id = ?");
$stmt->bind_param("i", $test_id);
$stmt->execute();
$result = $stmt->get_result();
$test = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $duration = $_POST['duration'];
    
    $stmt = $conn->prepare("UPDATE tests SET title = ?, duration = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $duration, $test_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: manage_questions.php?test_id=$test_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Test</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Test</h1>
        
        <form method="POST" action="edit_test.php?test_id=<?= $test_id ?>">
            <div class="form-group">
                <label for="title">Test Title:</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($test['title'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="duration">Duration (HH:MM:SS):</label>
                <input type="text" id="duration" name="duration" value="<?= htmlspecialchars($test['duration'] ?? '') ?>" required>
            </div>
            
            <button type="submit" class="btn">Save Changes</button>
            <a href="manage_questions.php?test_id=<?= $test_id ?>" class="btn cancel">Cancel</a>
        </form>
    </div>
</body>
</html>