<?php
include 'C:/xampp/htdocs/Academy/database.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id || !in_array($type, ['lesson', 'test'])) {
    echo json_encode(['error' => 'Неверные параметры']);
    exit;
}

if ($type === 'lesson') {
    $stmt = $conn->prepare("SELECT title, summary, video_url, attachment FROM lessons WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $lesson = $stmt->get_result()->fetch_assoc();
    if (!$lesson) {
        echo json_encode(['error' => 'Лекция не найдена']);
        exit;
    }
    echo json_encode([
        'type' => 'lesson',
        'title' => $lesson['title'],
        'summary' => $lesson['summary'],
        'video_url' => $lesson['video_url'],
        'attachment' => $lesson['attachment']
    ]);
} else {
    // test
    $stmt = $conn->prepare("SELECT title, duration FROM tests WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $test = $stmt->get_result()->fetch_assoc();
    if (!$test) {
        echo json_encode(['error' => 'Тест не найден']);
        exit;
    }
    echo json_encode([
        'type' => 'test',
        'title' => $test['title'],
        'duration' => $test['duration']
        // Можно добавить вопросы и т.п.
    ]);
}
