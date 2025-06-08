<?php
session_start();
include '../../database.php';

if (!isset($_SESSION['user_id']) || empty($_POST['course_id']) || empty($_POST['rating'])) {
    die(json_encode(['error' => 'Недостаточно данных']));
}

$stmt = $conn->prepare("UPDATE enrol SET rating=? WHERE id_user=? AND id_course=?");
$stmt->bind_param('iii', $_POST['rating'], $_SESSION['user_id'], $_POST['course_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Ошибка обновления']);
}
