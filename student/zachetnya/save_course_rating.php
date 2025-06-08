<?php
session_start();
require 'C:/xampp/htdocs/Academy/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$user_id = $_SESSION['id_user'];
$course_id = intval($_POST['course_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($course_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Некорректные данные']);
    exit;
}

$stmt = $conn->prepare("UPDATE enrol SET rating = ?, comments = ?, comment_date = NOW() WHERE id_user = ? AND id_course = ?");
$stmt->bind_param('isii', $rating, $comment, $user_id, $course_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
