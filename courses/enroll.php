<?php
session_start();
include '../database.php';

// Проверка авторизации с перенаправлением
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('Location: ../login.html?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Только для авторизованных пользователей:
header('Content-Type: application/json'); // Устанавливаем заголовок для JSON

$course_id = (int)$_GET['course_id'];
$user_id = $_SESSION['id_user'];

// Проверка существующей записи
$check = $conn->prepare("SELECT id FROM enrol WHERE id_user=? AND id_course=?");
$check->bind_param('ii', $user_id, $course_id);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    echo json_encode(['error' => 'Вы уже записаны на курс']);
    exit;
}

// Добавление записи
$stmt = $conn->prepare("INSERT INTO enrol (id_user, id_course, date_added) VALUES (?, ?, NOW())");
$stmt->bind_param('ii', $user_id, $course_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Ошибка записи']);
}