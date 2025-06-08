<?php
  if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
require_once __DIR__ . '/../database.php';

if (!isset($_SESSION['id_user'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID не передан']);
    exit;
}

$id_enrol = intval($_POST['id']);

$query = "SELECT c.id_users FROM enrol e JOIN courses c ON e.id_course = c.id WHERE e.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_enrol);
$stmt->execute();
$stmt->bind_result($course_owner);
$stmt->fetch();
$stmt->close();

if ($course_owner != $_SESSION['id_user']) {
    http_response_code(403);
    echo json_encode(['error' => 'Нет прав для удаления']);
    exit;
}

// Удаляем запись
$delete_stmt = $conn->prepare("DELETE FROM enrol WHERE id = ?");
$delete_stmt->bind_param("i", $id_enrol);
if ($delete_stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка при удалении']);
}
$delete_stmt->close();
?>
