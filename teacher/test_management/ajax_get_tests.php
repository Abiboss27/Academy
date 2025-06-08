<?php
include 'C:/xampp/htdocs/Academy/database.php';

$section_id = $_GET['section_id'] ?? 0;
$course_id = $_GET['course_id'] ?? 0;

$tests = [];
$stmt = $conn->prepare("SELECT id, title FROM tests WHERE id_section = ? AND id_course = ?");
$stmt->bind_param("ii", $section_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

echo '<h2>Tests</h2>';
if ($result->num_rows > 0) {
    echo '<ul>';
    while ($row = $result->fetch_assoc()) {
        echo '<li class="test">';
        echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
        echo '<a href="manage_questions.php?test_id=' . $row['id'] . '" class="btn">Manage Questions</a> ';
        echo '<a href="edit_test.php?test_id=' . $row['id'] . '" class="btn">Edit Test</a> ';
        echo '<a href="delete_test.php?test_id=' . $row['id'] . '" class="btn delete" onclick="return confirm(\'Are you sure you want to delete this test and all its questions?\')">Delete Test</a>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>No tests found for this section.</p>';
}

$stmt->close();
?>