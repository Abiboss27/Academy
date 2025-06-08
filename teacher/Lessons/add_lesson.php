<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'C:/xampp/htdocs/Academy/database.php';

// Предполагается, что ID пользователя хранится в сессии
if (!isset($_SESSION['id_user'])) {
    die('Пользователь не авторизован');
}
$currentUserId = (int)$_SESSION['id_user'];

// Получение списка курсов пользователя
$courses = [];
$stmt = $conn->prepare("SELECT id, id_users, title FROM courses WHERE id_users = ?");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_course = intval($_POST['id_course']);
    $id_section = intval($_POST['id_section']);
    $title = $conn->real_escape_string($_POST['title']);
    $summary = $conn->real_escape_string($_POST['summary']);
    $video_url = $conn->real_escape_string($_POST['video_url']);
    $date_added = date('Y-m-d');
    $last_modified = date('Y-m-d');

    // --- Загрузка файла ---
    $attachment_path = '';
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
    // Supposons que ce script est dans le dossier 'Academy'
    $upload_dir = 'C:/xampp/htdocs/Academy/uploads/'; // chemin absolu vers uploads
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $filename = basename($_FILES['attachment']['name']);
    $target_file = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
        // Chemin relatif à la racine web pour accès via URL
        $attachment_path = '/Academy/uploads/' . $filename;
    } else {
        $error = 'Ошибка при загрузке файла.';
    }
}


    if (!$error) {
        $sql = "INSERT INTO lessons (id_course, id_section, title, summary, video_url, attachment, date_added, last_modified)
                VALUES ('$id_course', '$id_section', '$title', '$summary', '$video_url', '$attachment_path', '$date_added', '$last_modified')";
        if ($conn->query($sql) === TRUE) {
            $success = true;
        } else {
            $error = "Ошибка: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить лекцию | Академия</title>
    <a href="../lessons_table.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Назад к списку лекций
    </a>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2>Добавить новую лекцию</h2>
    <?php if ($success): ?>
        <div class="alert success">Лекция успешно добавлена!</div>
    <?php elseif (!empty($error)): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="lessonForm" enctype="multipart/form-data">
        <div class="form-group">
            <label for="id_course">Курс</label>
            <div class="select-arrow">
                <select name="id_course" id="id_course" required>
                    <option value="">Выберите курс</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="id_section">Раздел</label>
            <div class="select-arrow">
                <select name="id_section" id="id_section" required>
                    <option value="">Сначала выберите курс</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="title">Название лекции</label>
            <input type="text" name="title" id="title" required placeholder="Введите название лекции">
        </div>

        <div class="form-group">
            <label for="summary">Краткое описание</label>
            <textarea name="summary" id="summary" placeholder="Опишите содержание лекции"></textarea>
        </div>

        <div class="form-group">
            <label for="video_url">Ссылка на видео</label>
            <input type="text" name="video_url" id="video_url" placeholder="https://example.com/video">
        </div>

        <div class="form-group">
            <label>Прикрепить файл</label>
            <div class="file-input">
                <label class="file-input-label" for="attachment">
                    <i>📎</i>
                    <span id="file-name">Выберите файл или перетащите его сюда</span>
                </label>
                <input type="file" name="attachment" id="attachment">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Добавить лекцию
        </button>
    </form>
</div>

<script>
document.getElementById('id_course').addEventListener('change', function() {
    var courseId = this.value;
    var sectionSelect = document.getElementById('id_section');
    sectionSelect.innerHTML = '<option>Загрузка...</option>';

    if (!courseId) {
        sectionSelect.innerHTML = '<option value="">Сначала выберите курс</option>';
        return;
    }

    fetch('get_sections.php?course_id=' + courseId)
        .then(response => response.json())
        .then(data => {
            var options = '<option value="">Выберите раздел</option>';
            data.forEach(function(section) {
                options += `<option value="${section.id}">${section.title}</option>`;
            });
            sectionSelect.innerHTML = options;
        })
        .catch(error => {
            sectionSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
            console.error('Error:', error);
        });
});

// Показываем имя выбранного файла
document.getElementById('attachment').addEventListener('change', function(e) {
    var fileName = e.target.files[0] ? e.target.files[0].name : 'Выберите файл или перетащите его сюда';
    document.getElementById('file-name').textContent = fileName;
});
</script>
</body>
</html>
