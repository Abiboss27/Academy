<?php
include 'C:/xampp/htdocs/Academy/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверяем авторизацию
if (!isset($_SESSION['id_user'])) {
    header('Location: /login.php');
    exit();
}

$userId = (int)$_SESSION['id_user'];

// Проверяем, что форма отправлена методом POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /profile.php');
    exit();
}

// Получаем и валидируем данные из формы
$fullName = trim($_POST['FullName'] ?? '');
$email = trim($_POST['email'] ?? '');

// Простая валидация
$errors = [];
if (empty($fullName)) {
    $errors[] = 'ФИО не может быть пустым';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Введите корректный email';
}

if (!empty($errors)) {
    // Можно передать ошибки в сессию или показать тут
    die('Ошибка: ' . implode(', ', $errors));
}

// Обработка загрузки файла (если есть)
$pictureLink = null;
if (isset($_FILES['Picture_Link']) && $_FILES['Picture_Link']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['Picture_Link'];

    // Проверяем ошибки загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die('Ошибка загрузки файла');
    }

    // Проверяем тип файла (разрешаем только изображения)
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        die('Разрешены только изображения JPG, PNG, GIF, WEBP');
    }

    // Создаём уникальное имя файла
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = 'user_' . $userId . '_' . time() . '.' . $ext;

    // Путь для сохранения файла (создайте папку, если её нет)
    $uploadDir = __DIR__ . '/../student/assets/images/users/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        die('Не удалось сохранить загруженный файл');
    }

    // Относительный путь для сохранения в базе (от корня сайта)
    $pictureLink = '/Academy/student/assets/images/users/' . $newFileName;
}

// Формируем запрос на обновление
if ($pictureLink !== null) {
    $sql = "UPDATE users SET FullName = ?, email = ?, Picture_Link = ? WHERE id = ?";
} else {
    $sql = "UPDATE users SET FullName = ?, email = ? WHERE id = ?";
}

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Ошибка подготовки запроса: ' . $conn->error);
}

if ($pictureLink !== null) {
    $stmt->bind_param('sssi', $fullName, $email, $pictureLink, $userId);
} else {
    $stmt->bind_param('ssi', $fullName, $email, $userId);
}

if ($stmt->execute()) {
    // Успешно обновлено
    header('Location: profile.php?updated=1');
    exit();
} else {
    die('Ошибка обновления профиля: ' . $stmt->error);
}
