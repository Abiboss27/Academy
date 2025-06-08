<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'C:/xampp/htdocs/Academy/database.php';

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
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

$lessonId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($lessonId <= 0) {
    die('Некорректный ID лекции');
}

// Инициализация переменных для формы
$lesson = [
    'id_course' => '',
    'id_section' => '',
    'title' => '',
    'summary' => '',
    'video_url' => '',
    'attachment' => '',
];

$error = '';
$success = false;

// Загрузка данных лекции для редактирования
$stmt = $conn->prepare("SELECT * FROM lessons WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $lessonId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die('Лекция не найдена');
}
$lesson = $result->fetch_assoc();

// Проверка, что курс принадлежит текущему пользователю (безопасность)
$courseIds = array_column($courses, 'id');
if (!in_array($lesson['id_course'], $courseIds)) {
    die('Доступ запрещен: курс не принадлежит пользователю');
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_course = intval($_POST['id_course']);
    $id_section = intval($_POST['id_section']);
    $title = $conn->real_escape_string($_POST['title']);
    $summary = $conn->real_escape_string($_POST['summary']);
    $video_url = $conn->real_escape_string($_POST['video_url']);
    $date_added = $lesson['date_added']; // дата добавления не меняется
    $last_modified = date('Y-m-d');

    // Проверка, что курс выбран и принадлежит пользователю
    if (!in_array($id_course, $courseIds)) {
        $error = 'Выбранный курс недоступен';
    }

    // --- Загрузка нового файла, если есть ---
    $attachment_path = $lesson['attachment']; // сохраняем старый путь по умолчанию
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = basename($_FILES['attachment']['name']);
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
            $attachment_path = 'uploads/' . $filename;
            // Можно добавить удаление старого файла, если нужно
        } else {
            $error = 'Ошибка при загрузке файла.';
        }
    }

    if (!$error) {
        $sql = "UPDATE lessons SET 
                    id_course = ?, 
                    id_section = ?, 
                    title = ?, 
                    summary = ?, 
                    video_url = ?, 
                    attachment = ?, 
                    last_modified = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssssi", $id_course, $id_section, $title, $summary, $video_url, $attachment_path, $last_modified, $lessonId);
        if ($stmt->execute()) {
            $success = true;
            // Обновляем данные для отображения в форме
            $lesson['id_course'] = $id_course;
            $lesson['id_section'] = $id_section;
            $lesson['title'] = $title;
            $lesson['summary'] = $summary;
            $lesson['video_url'] = $video_url;
            $lesson['attachment'] = $attachment_path;
            $lesson['last_modified'] = $last_modified;
        } else {
            $error = "Ошибка при обновлении: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать лекцию | Академия</title>
    <a href="../lessons_table.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Назад к списку лекций
    </a>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2>Редактировать лекцию</h2>
    <?php if ($success): ?>
        <div class="alert success">Лекция успешно обновлена!</div>
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
                        <option value="<?= $course['id'] ?>" <?= ($lesson['id_course'] == $course['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($course['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="id_section">Раздел</label>
            <div class="select-arrow">
                <select name="id_section" id="id_section" required>
                    <option value="<?= $lesson['id_section'] ?>">Загрузка...</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="title">Название лекции</label>
            <input type="text" name="title" id="title" required placeholder="Введите название лекции" value="<?= htmlspecialchars($lesson['title']) ?>">
        </div>

        <div class="form-group">
            <label for="summary">Краткое описание</label>
            <textarea name="summary" id="summary" placeholder="Опишите содержание лекции"><?= htmlspecialchars($lesson['summary']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="video_url">Ссылка на видео</label>
            <input type="text" name="video_url" id="video_url" placeholder="https://example.com/video" value="<?= htmlspecialchars($lesson['video_url']) ?>">
        </div>

        <div class="form-group">
            <label>Прикрепить файл</label>
            <?php if (!empty($lesson['attachment'])): ?>
                <p>Текущий файл: <a href="<?= htmlspecialchars($lesson['attachment']) ?>" target="_blank"><?= basename($lesson['attachment']) ?></a></p>
            <?php endif; ?>
            <div class="file-input">
                <label class="file-input-label" for="attachment">
                    <i>📎</i>
                    <span id="file-name">Выберите файл или перетащите его сюда</span>
                </label>
                <input type="file" name="attachment" id="attachment">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Сохранить изменения
        </button>
    </form>
</div>

<script>
function loadSections(courseId, selectedSectionId = null) {
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
                var selected = (section.id == selectedSectionId) ? 'selected' : '';
                options += `<option value="${section.id}" ${selected}>${section.title}</option>`;
            });
            sectionSelect.innerHTML = options;
        })
        .catch(error => {
            sectionSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
            console.error('Error:', error);
        });
}

document.getElementById('id_course').addEventListener('change', function() {
    loadSections(this.value);
});

// Загрузка разделов при загрузке страницы с выбранным курсом и разделом
window.addEventListener('DOMContentLoaded', function() {
    var courseId = document.getElementById('id_course').value;
    var sectionId = <?= json_encode($lesson['id_section']) ?>;
    if (courseId) {
        loadSections(courseId, sectionId);
    }
});

// Показываем имя выбранного файла
document.getElementById('attachment').addEventListener('change', function(e) {
    var fileName = e.target.files[0] ? e.target.files[0].name : 'Выберите файл или перетащите его сюда';
    document.getElementById('file-name').textContent = fileName;
});
</script>
</body>
</html>
