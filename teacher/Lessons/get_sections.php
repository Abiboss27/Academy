<?php
header('Content-Type: application/json');
include 'C:/xampp/htdocs/Academy/database.php';

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$sections = [];
if ($course_id) {
    $sql = "SELECT id, title FROM section WHERE id_course = $course_id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $sections[] = $row;
        }
    }
}
echo json_encode($sections);
