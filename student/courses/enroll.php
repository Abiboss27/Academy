<?php
session_start();
include 'C:/xampp/htdocs/Academy/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['error' => 'Пользователь не авторизован']);
    exit;
}

$userId = (int)$_SESSION['id_user'];
$courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;

if ($courseId <= 0) {
    echo json_encode(['error' => 'Неверный ID курса']);
    exit;
}

// Проверяем, не записан ли уже пользователь на курс
$stmt = $conn->prepare("SELECT id FROM enrol WHERE id_user = ? AND id_course = ?");
$stmt->bind_param('ii', $userId, $courseId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['error' => 'Вы уже записаны на этот курс']);
    exit;
}

// Добавляем запись
$stmt = $conn->prepare("INSERT INTO enrol (id_user, id_course, date_added, rating, comments) VALUES (?, ?, NOW(), 0, '')");
$stmt->bind_param('ii', $userId, $courseId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Ошибка при записи на курс']);
}
?>
