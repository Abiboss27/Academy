<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../database.php';

// Проверка авторизации
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php'); // Перенаправление на страницу входа
    exit;
}

$currentUserId = (int)$_SESSION['id_user'];
$error = '';
$success = '';

// Получение данных для выпадающих списков
try {
    $categories = $conn->query("SELECT id, name FROM categories");
    $statuts = $conn->query("SELECT id, name FROM statuts");
    $levels = $conn->query("SELECT id, name FROM levels");
    
    // Проверка ошибок запросов
    if (!$categories || !$statuts || !$levels) {
        throw new Exception('Ошибка загрузки справочных данных');
    }
} catch (Exception $e) {
    die($e->getMessage());
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Валидация данных
        $requiredFields = ['title', 'language', 'price', 'discount', 'id_category', 'id_statut', 'id_level'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Поле " . ucfirst($field) . " обязательно для заполнения");
            }
        }

        $title = trim($_POST['title']);
        $language = trim($_POST['language']);
        $price = (float)$_POST['price'];
        $discount = (float)$_POST['discount'];
        $video_url = trim($_POST['video_url']);
        $id_category = (int)$_POST['id_category'];
        $id_statut = (int)$_POST['id_statut'];
        $id_level = (int)$_POST['id_level'];

        // Дополнительные проверки
        if ($price < 0) throw new Exception("Цена не может быть отрицательной");
        if ($discount < 0 || $discount > 100) throw new Exception("Скидка должна быть между 0 и 100%");
        if (!filter_var($video_url, FILTER_VALIDATE_URL) && !empty($video_url)) {
            throw new Exception("Некорректный URL видео");
        }

        // SQL-запрос
        $stmt = $conn->prepare("INSERT INTO courses 
            (title, language, price, discount, video_url, 
             id_category, id_statut, id_level, id_users, last_modified) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param("ssddsiiii", 
            $title, 
            $language, 
            $price, 
            $discount, 
            $video_url, 
            $id_category, 
            $id_statut, 
            $id_level, 
            $currentUserId
        );

        if (!$stmt->execute()) {
            throw new Exception("Ошибка сохранения: " . $stmt->error);
        }

        // Успешное сохранение
        $_SESSION['success_message'] = 'Курс успешно добавлен!';
        header('Location: teacher_home.php');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавление курса</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container { max-width: 800px; margin: 20px auto; }
        .alert { margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="my-4">Добавление нового курса</h1>
    <a href="courses_table.php" class="btn btn-secondary mb-4">← К списку курсов</a>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>Название курса *</label>
            <input type="text" name="title" class="form-control" required 
                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Язык курса *</label>
            <input type="text" name="language" class="form-control" required 
                   value="<?= htmlspecialchars($_POST['language'] ?? '') ?>">
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Цена (РУБ) *</label>
                <input type="number" step="0.01" min="0" name="price" class="form-control" required 
                       value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label>Скидка (%) *</label>
                <input type="number" step="0.01" min="0" max="100" name="discount" class="form-control" required 
                       value="<?= htmlspecialchars($_POST['discount'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Видео URL (необязательно)</label>
            <input type="url" name="video_url" class="form-control" 
                   placeholder="https://example.com/video.mp4"
                   value="<?= htmlspecialchars($_POST['video_url'] ?? '') ?>">
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Категория *</label>
                <select name="id_category" class="form-control" required>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>" 
                            <?= ($id_category ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group col-md-4">
                <label>Статус *</label>
                <select name="id_statut" class="form-control" required>
                    <?php while ($st = $statuts->fetch_assoc()): ?>
                        <option value="<?= $st['id'] ?>">
                            <?= htmlspecialchars($st['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group col-md-4">
                <label>Уровень *</label>
                <select name="id_level" class="form-control" required>
                    <?php while ($lvl = $levels->fetch_assoc()): ?>
                        <option value="<?= $lvl['id'] ?>">
                            <?= htmlspecialchars($lvl['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-success btn-lg">Добавить курс</button>
            <a href="teacher_home.php" class="btn btn-outline-secondary">Отмена</a>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
