<?php
session_start();
require_once __DIR__ . '/../database.php'; // Подключение к базе данных (замените на ваш файл подключения)

// Проверяем, что пользователь авторизован
if (!isset($_SESSION['id_user'])) {
    die('Пользователь не авторизован');
}

// Проверяем, что передан ID курса
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Неверный ID курса');
}

$id_course = (int)$_GET['id'];

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы и валидируем
    $title = trim($_POST['title']);
    $language = trim($_POST['language']);
    $price = floatval($_POST['price']);
    $discount = floatval($_POST['discount']);
    $video_url = trim($_POST['video_url']);
    $id_category = (int)$_POST['id_category'];
    $id_statut = (int)$_POST['id_statut'];
    $id_level = (int)$_POST['id_level'];

    // Здесь можно добавить дополнительную валидацию данных

    // Обновляем данные курса в базе
    $stmt = $conn->prepare("UPDATE courses SET title=?, language=?, price=?, discount=?, video_url=?, id_category=?, id_statut=?, id_level=?, last_modified=NOW() WHERE id=? AND id_users=?");
    $stmt->bind_param("ssddiiiiii", $title, $language, $price, $discount, $video_url, $id_category, $id_statut, $id_level, $id_course, $_SESSION['id_user']);
    $stmt->execute();
}

// Получаем текущие данные курса для отображения в форме
$stmt = $conn->prepare("SELECT * FROM courses WHERE id=? AND id_users=?");
$stmt->bind_param("ii", $id_course, $_SESSION['id_user']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Курс не найден или у вас нет прав на редактирование');
}

$course = $result->fetch_assoc();

// Получаем категории, статусы и уровни для выпадающих списков
$categories = $conn->query("SELECT id, name FROM categories");
$statuts = $conn->query("SELECT id, name FROM statuts");
$levels = $conn->query("SELECT id, name FROM levels");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование курса</title>
    <a href="courses_table.php" class="btn btn-secondary"> К курсам</a>
    <link rel="stylesheet" href="src/css/style_nav_cours.css">
</head>
<body>
<div class="container">
    <h1>Редактирование курса</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="http://localhost:80/Academy/teacher/edit_cours.php?id=<?= $id_course ?>">
        <div class="form-group">
            <label>Название курса</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($course['title']) ?>" required>
        </div>

        <div class="form-group">
            <label>Язык курса</label>
            <input type="text" name="language" class="form-control" value="<?= htmlspecialchars($course['language']) ?>" required>
        </div>

        <div class="form-group">
            <label>Цена курса (РУБ)</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($course['price']) ?>" required>
        </div>

        <div class="form-group">
            <label>Скидка (%)</label>
            <input type="number" step="0.01" name="discount" class="form-control" value="<?= htmlspecialchars($course['discount']) ?>" required>
        </div>

        <div class="form-group">
            <label>Видео URL</label>
            <input type="url" name="video_url" class="form-control" value="<?= htmlspecialchars($course['video_url']) ?>">
        </div>

        <div class="form-group">
            <label>Категория</label>
            <select name="id_category" class="form-control" required>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $course['id_category']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Статус</label>
            <select name="id_statut" class="form-control" required>
                <?php while ($st = $statuts->fetch_assoc()): ?>
                    <option value="<?= $st['id'] ?>" <?= ($st['id'] == $course['id_statut']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($st['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Уровень</label>
            <select name="id_level" class="form-control" required>
                <?php while ($lvl = $levels->fetch_assoc()): ?>
                    <option value="<?= $lvl['id'] ?>" <?= ($lvl['id'] == $course['id_level']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lvl['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Сохранить</button>
        <a href="teacher_home.php" class="btn btn-secondary">Отмена</a>
    </form>
</div>
</body>
</html>
