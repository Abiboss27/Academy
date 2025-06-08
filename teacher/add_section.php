<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    die("Пожалуйста, войдите в систему.");
}

require_once __DIR__ . '/../database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_course = intval($_POST['id_course']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    // Проверяем, что курс принадлежит текущему пользователю
    $checkCourseQuery = "SELECT id FROM courses WHERE id = $id_course AND id_users = " . $_SESSION['id_user'];
    $result = mysqli_query($conn, $checkCourseQuery);

    if (mysqli_num_rows($result) === 0) {
        die("Выбранный курс не найден или не принадлежит вам.");
    }

    // Вставляем новый раздел
    $stmt = $conn->prepare("INSERT INTO section (id_course, title, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id_course, $title, $description);

    if ($stmt->execute()) {
        // Перенаправляем обратно на страницу дашборда с сообщением об успехе
        header("Location: courses_table.php?msg=section_added");
        exit();
    } else {
        die("Ошибка при добавлении раздела: " . $stmt->error);
    }
} else {
    die("Неверный метод запроса.");
}
