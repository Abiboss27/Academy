<?php
require_once __DIR__ . '/../database.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if (!$action) {
    echo json_encode(['success' => false, 'error' => 'Не указано действие']);
    exit;
}

switch ($action) {
    case 'add':
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            echo json_encode(['success' => false, 'error' => 'Название не может быть пустым']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO categories (name, date_added, last_modified) VALUES (?, CURDATE(), UNIX_TIMESTAMP())");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        break;

    case 'edit':
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id <= 0 || $name === '') {
            echo json_encode(['success' => false, 'error' => 'Некорректные данные']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE categories SET name = ?, last_modified = UNIX_TIMESTAMP() WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Некорректный ID']);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Неизвестное действие']);
        break;
}
