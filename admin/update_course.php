<?php
require_once __DIR__ . '/../database.php';

header('Content-Type: application/json');

$id_course = intval($_POST['id_course'] ?? 0);
$title = trim($_POST['title'] ?? '');
$id_statut = intval($_POST['id_statut'] ?? 0);

if ($id_course <= 0 || $title === '' || $id_statut <= 0) {
    echo json_encode(['success' => false, 'error' => 'Некорректные данные']);
    exit;
}

$stmt = $conn->prepare("UPDATE courses SET title = ?, id_statut = ? WHERE id = ?");
$stmt->bind_param("sii", $title, $id_statut, $id_course);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
