<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'C:/xampp/htdocs/Academy/database.php';

header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['id_user'])) {
    echo json_encode(['error' => 'Не авторизован']);
    exit();
}

// Получение ID курса
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
if ($course_id <= 0) {
    echo json_encode(['error' => 'Неверный ID курса']);
    exit();
}

// Получение прогресса курса
$stmt = $conn->prepare("
    SELECT AVG(score) as progress 
    FROM scores 
    WHERE id_users = ? AND course_id = ?
");
$stmt->bind_param('ii', $_SESSION['id_user'], $course_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'progress' => round($result['progress'] ?? 0),
    'course_id' => $course_id
]);
?>