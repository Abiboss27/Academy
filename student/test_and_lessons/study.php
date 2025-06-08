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

$currentUserId = (int)$_SESSION['id_user'];
// Получение ID курса
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($course_id <= 0) {
    die('Неверный ID курса');
}

// Получение информации о курсе
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param('i', $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    die('Курс не найден');
}

// Получение разделов курса
$sections = [];
$stmt = $conn->prepare("SELECT * FROM section WHERE id_course = ? ORDER BY id");
$stmt->bind_param('i', $course_id);
$stmt->execute();
$sections_result = $stmt->get_result();

while ($section = $sections_result->fetch_assoc()) {
    $sections[$section['id']] = $section;
    
    // Получение уроков для раздела
    $stmt_lessons = $conn->prepare("SELECT * FROM lessons WHERE id_section = ? ORDER BY id");
    $stmt_lessons->bind_param('i', $section['id']);
    $stmt_lessons->execute();
    $sections[$section['id']]['lessons'] = $stmt_lessons->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Получение тестов для раздела
    $stmt_tests = $conn->prepare("SELECT * FROM tests WHERE id_section = ? ORDER BY id");
    $stmt_tests->bind_param('i', $section['id']);
    $stmt_tests->execute();
    $sections[$section['id']]['tests'] = $stmt_tests->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Получение прогресса курса
        $progress = 0;
 // Получаем общее количество разделов в курсе
        $sectionStmt = $conn->prepare("SELECT COUNT(*) as total FROM section WHERE id_course = ?");
        $sectionStmt->bind_param('i', $course['id']);
        $sectionStmt->execute();
        $sectionResult = $sectionStmt->get_result();
        $totalSections = $sectionResult->fetch_assoc()['total'];
        $sectionStmt->close();
        
        // Получаем количество завершенных разделов (оценка >=75)
        $completedStmt = $conn->prepare("
            SELECT COUNT(DISTINCT s.id) as completed 
            FROM scores sc
            JOIN section s ON sc.section_id = s.id
            WHERE sc.id_users = ? 
            AND sc.course_id = ? 
            AND sc.score >= 75
        ");
        $completedStmt->bind_param('ii', $currentUserId, $course_id);
        $completedStmt->execute();
        $completedResult = $completedStmt->get_result();
        $completedSections = $completedResult->fetch_assoc()['completed'];
        $completedStmt->close();
        
        // Рассчитываем процент завершения
        $progress = $totalSections > 0 ? round(($completedSections / $totalSections) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['title']) ?> - Изучение курса</title>
    <link rel="stylesheet" href="styles/course_styles.css">
</head>
<body>
    <div class="course-container">
      <header class="course-header">
    <a href="/Academy/student/courses/my_courses.php" class="back-button">← Назад</a>
    <h1><?= htmlspecialchars($course['title']) ?></h1>
    <div class="course-progress">
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= $progress ?>%"></div>
        </div>
        <span class="progress-text"><?= round($progress) ?>% завершено</span>
    </div>
</header>


        <div class="course-description">
            <p><?= htmlspecialchars($course['description']) ?></p>
        </div>

        <div class="course-sections">
            <?php foreach ($sections as $section): ?>
            <div class="section">
                <h2 class="section-title"><?= htmlspecialchars($section['title']) ?></h2>
                <p class="section-description"><?= htmlspecialchars($section['description']) ?></p>
                
                <div class="section-content">
                    <?php foreach ($section['lessons'] as $lesson): ?>
                    <div class="lesson-item">
                        <a href="lesson.php?id=<?= $lesson['id'] ?>&course_id=<?= $course_id ?>" class="lesson-link">
                            <span class="lesson-icon">📹</span>
                            <span class="lesson-title"><?= htmlspecialchars($lesson['title']) ?></span>
                        </a>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php foreach ($section['tests'] as $test): ?>
                    <div class="test-item">
                        <a href="test.php?id=<?= $test['id'] ?>&course_id=<?= $course_id ?>" class="test-link">
                            <span class="test-icon">📝</span>
                            <span class="test-title"><?= htmlspecialchars($test['title']) ?></span>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- <script src="/Academy/student/courses/js/course_scripts.js"></script> -->
</body>
</html>