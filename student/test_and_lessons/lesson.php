<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'C:/xampp/htdocs/Academy/database.php';

// Проверка авторизации
if (!isset($_SESSION['id_user'])) {
    header('Location: /Academy/login.php');
    exit();
}

$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if ($lesson_id <= 0 || $course_id <= 0) {
    die('Неверные параметры');
}

$stmt = $conn->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->bind_param('i', $lesson_id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();

if (!$lesson) {
    die('Урок не найден');
}

$stmt = $conn->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param('i', $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

function getVideoUrl($url) {
    if (preg_match('/rutube\.ru\/video\/([a-z0-9\-]+)/i', $url, $matches)) {
        return $url;
    }
    if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $matches)) {
        return 'https://www.youtube.com/watch?v=' . $matches[1];
    }
    if (preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $matches)) {
        return 'https://www.youtube.com/watch?v=' . $matches[1];
    }
    if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
        return $url;
    }
    return $url;
}

function getLessonVideoUrl($conn, $lesson_id) {
    $stmt = $conn->prepare("SELECT video_url FROM lessons WHERE id = ?");
    $stmt->bind_param('i', $lesson_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result['video_url'] : null;
}

$stmt = $conn->prepare("
    SELECT id, title FROM lessons 
    WHERE id_section = ? AND id > ? 
    ORDER BY id ASC 
    LIMIT 1
");
$stmt->bind_param('ii', $lesson['id_section'], $lesson['id']);
$stmt->execute();
$next_lesson = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("
    SELECT id, title FROM lessons 
    WHERE id_section = ? AND id < ? 
    ORDER BY id DESC 
    LIMIT 1
");
$stmt->bind_param('ii', $lesson['id_section'], $lesson['id']);
$stmt->execute();
$prev_lesson = $stmt->get_result()->fetch_assoc();

$next_video_url = $next_lesson ? getVideoUrl(getLessonVideoUrl($conn, $next_lesson['id'])) : null;
$prev_video_url = $prev_lesson ? getVideoUrl(getLessonVideoUrl($conn, $prev_lesson['id'])) : null;
$current_video_url = getVideoUrl($lesson['video_url']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($lesson['title']) ?> - Урок</title>
    <link rel="stylesheet" href="styles/course_styles.css" />
</head>
<body>
    <div class="lesson-container">
        <header class="lesson-header">
            <a href="study.php?id=<?= $course_id ?>" class="back-link">← Назад к курсу</a>
            <h1><?= htmlspecialchars($course['title']) ?></h1>
            <h2><?= htmlspecialchars($lesson['title']) ?></h2>
        </header>

        <div class="lesson-content">
            <?php if ($current_video_url): ?>
                <p>Смотреть видео урок: <a href="<?= htmlspecialchars($current_video_url) ?>" target="_blank" rel="noopener noreferrer">Перейти к видео</a></p>
            <?php else: ?>
                <p>Видео недоступно.</p>
            <?php endif; ?>

            <div class="lesson-summary">
                <?= nl2br(htmlspecialchars($lesson['summary'])) ?>
            </div>

            <?php if ($lesson['attachment']): 
                // basename для безопасности, чтобы не передавать полный путь
                $attachmentFile = basename($lesson['attachment']);
            ?>
            <div class="lesson-attachment">
                <h3>Материалы для скачивания:</h3>
                <a href="download.php?file=<?= urlencode($attachmentFile) ?>" class="download-btn">Скачать</a>
            </div>
            <?php endif; ?>
        </div>

        <div class="lesson-navigation">
            <?php if ($prev_video_url): ?>
                <a href="<?= htmlspecialchars($prev_video_url) ?>" target="_blank" rel="noopener noreferrer" class="nav-btn prev-btn">← Предыдущий урок (видео)</a>
            <?php endif; ?>

            <?php if ($next_video_url): ?>
                <a href="<?= htmlspecialchars($next_video_url) ?>" target="_blank" rel="noopener noreferrer" class="nav-btn next-btn">Следующий урок (видео) →</a>
            <?php else: ?>
                <a href="study.php?id=<?= $course_id ?>" class="nav-btn back-to-course">Вернуться к курсу</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
