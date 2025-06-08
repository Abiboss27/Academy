<?php

include 'C:/xampp/htdocs/Academy/database.php';

// Your database connection file

$course_id = $_GET['course_id'] ?? 0;

$sections = [];
$stmt = $conn->prepare("SELECT id, title FROM section WHERE id_course = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

echo '<h2>Sections</h2>';
if ($result->num_rows > 0) {
    echo '<ul>';
    while ($row = $result->fetch_assoc()) {
        echo '<li class="section">';
        echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
        echo '<a href="add_test.php?section_id=' . $row['id'] . '&course_id=' . $course_id . '" class="btn">Add Test</a> ';
        echo '<button onclick="loadTests(' . $row['id'] . ', ' . $course_id . ')" class="btn">View Tests</button>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>No sections found for this course.</p>';
}

$stmt->close();
?>