<?php

include 'C:/xampp/htdocs/Academy/database.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section_id = $_POST['section_id'];
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $duration = $_POST['duration'];
    
    // Insert test
    $stmt = $conn->prepare("INSERT INTO tests (id_section, id_course, title, duration) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $section_id, $course_id, $title, $duration);
    $stmt->execute();
    $test_id = $stmt->insert_id;
    $stmt->close();
    
    header("Location: manage_questions.php?test_id=$test_id");
    exit();
}

// Get section and course info
$section_id = $_GET['section_id'] ?? 0;
$course_id = $_GET['course_id'] ?? 0;

$section_title = '';
$course_title = '';

if ($section_id && $course_id) {
    $stmt = $conn->prepare("SELECT s.title as section_title, c.title as course_title 
                           FROM section s 
                           JOIN courses c ON s.id_course = c.id 
                           WHERE s.id = ? AND s.id_course = ?");
    $stmt->bind_param("ii", $section_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $section_title = $row['section_title'];
        $course_title = $row['course_title'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Test</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Add New Test</h1>
        <p>Course: <?= htmlspecialchars($course_title) ?></p>
        <p>Section: <?= htmlspecialchars($section_title) ?></p>
        
        <form method="POST" action="add_test.php">
            <input type="hidden" name="section_id" value="<?= $section_id ?>">
            <input type="hidden" name="course_id" value="<?= $course_id ?>">
            
            <div class="form-group">
                <label for="title">Test Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="duration">Duration (HH:MM:SS):</label>
                <input type="text" id="duration" name="duration" value="00:30:00" required>
            </div>
            
            <button type="submit" class="btn">Create Test</button>
            <a href="javascript:history.back()" class="btn cancel">Cancel</a>
        </form>
    </div>
</body>
</html>