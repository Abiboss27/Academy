<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'C:/xampp/htdocs/Academy/database.php';

// Проверяем авторизацию
if (!isset($_SESSION['id_user'])) {
    die('Пользователь не авторизован');
}

// Проверяем наличие ID курса
if (!isset($_GET['id'])) {
    die('ID курса не указан');
}

$courseId = (int)$_GET['id'];
$course = null; // Инициализируем переменную

// Получаем данные курса
$sqlCourse = "SELECT c.*, cat.name AS category_name, u.FullName AS provider_name 
              FROM courses c
              LEFT JOIN categories cat ON c.id_category = cat.id
              LEFT JOIN users u ON c.id_users = u.id
              WHERE c.id = ?";
$stmt = $conn->prepare($sqlCourse);
$stmt->bind_param('i', $courseId);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

// Если курс не найден - завершаем с сообщением
if (!$course) {
    die('Курс не найден или у вас нет доступа');
}

// Получаем количество записанных пользователей
$sqlEnrollCount = "SELECT COUNT(*) AS enrolled_count FROM enrol WHERE id_course = ?";
$stmt = $conn->prepare($sqlEnrollCount);
$stmt->bind_param('i', $courseId);
$stmt->execute();
$enrollCount = $stmt->get_result()->fetch_assoc()['enrolled_count'] ?? 0;

// Получаем разделы курса
$sqlSections = "SELECT * FROM section WHERE id_course = ? ORDER BY id ASC";
$stmt = $conn->prepare($sqlSections);
$stmt->bind_param('i', $courseId);
$stmt->execute();
$sectionsResult = $stmt->get_result();

$sections = [];
while ($section = $sectionsResult->fetch_assoc()) {
    $sectionId = $section['id'];

    // Получаем лекции раздела
    $stmtLessons = $conn->prepare("SELECT id, title, summary FROM lessons WHERE id_section = ? ORDER BY id ASC");
    $stmtLessons->bind_param('i', $sectionId);
    $stmtLessons->execute();
    $lessons = $stmtLessons->get_result()->fetch_all(MYSQLI_ASSOC);

    // Получаем тесты раздела
    $stmtTests = $conn->prepare("SELECT id, title, duration FROM tests WHERE id_section = ? ORDER BY id ASC");
    $stmtTests->bind_param('i', $sectionId);
    $stmtTests->execute();
    $tests = $stmtTests->get_result()->fetch_all(MYSQLI_ASSOC);

    $sections[] = [
        'section' => $section,
        'lessons' => $lessons,
        'tests' => $tests,
    ];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['title']) ?> - Подробности курса</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --text: #1f2937;
            --text-light: #6b7280;
            --bg: #f9fafb;
            --card-bg: #ffffff;
            --border: #e5e7eb;
            --success: #10b981;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text);
            background-color: var(--bg);
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .course-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 3rem 2rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .course-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .meta-item i {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .course-description {
            background-color: var(--card-bg);
            border-radius: 0.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .description-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        .section {
            background-color: var(--card-bg);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text);
        }
        
        .section-description {
            color: var(--text-light);
            margin-bottom: 1.5rem;
        }
        
        .lessons-list, .tests-list {
            margin-left: 1rem;
        }
        
        .lesson-item, .test-item {
            padding: 1rem;
            margin-bottom: 0.75rem;
            background-color: var(--bg);
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }
        
        .lesson-item:hover, .test-item:hover {
            transform: translateX(4px);
            background-color: #f0f0ff;
        }
        
        .lesson-title, .test-title {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .lesson-title i {
            color: var(--primary);
        }
        
        .test-title i {
            color: var(--success);
        }
        
        .lesson-summary {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-left: 1.75rem;
        }
   
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-light);
            background-color: var(--bg);
            border-radius: 0.5rem;
            border: 1px dashed var(--border);
        }
        
        .badge {
            display: inline-block;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            color: white;
            background-color: var(--primary);
            border-radius: 9999px;
            margin-right: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .course-title {
                font-size: 1.8rem;
            }
            
            .course-meta {
                flex-direction: column;
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="course-header">
            <h1 class="course-title"><?= htmlspecialchars($course['title']) ?></h1>
            <div class="course-meta">
                <div class="meta-item">
                    <i class="fas fa-layer-group"></i>
                    <span><?= htmlspecialchars($course['category_name'] ?? 'Без категории') ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-user-tie"></i>
                    <span><?= htmlspecialchars($course['provider_name'] ?? 'Автор не указан') ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-users"></i>
                    <span><?= $enrollCount ?> участников</span>
                </div>
            </div>
        </div>
        
        <div class="course-description">
            <h3 class="description-title">Описание курса</h3>
            <p><?= nl2br(htmlspecialchars($course['short_description'])) ?></p>
            
            <?php if (!empty($course['description'])): ?>
                <h3 class="description-title" style="margin-top: 1.5rem;">Подробная программа</h3>
                <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
            <?php endif; ?>
        </div>
        
        <h2 style="font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 600;">Структура курса</h2>
        
        <?php if (count($sections) === 0): ?>
            <div class="empty-state">
                <i class="fas fa-book-open" style="font-size: 2rem; margin-bottom: 1rem; color: var(--text-light);"></i>
                <p>В этом курсе пока нет разделов</p>
            </div>
        <?php else: ?>
            <?php foreach ($sections as $sec): 
                $section = $sec['section'];
                $lessons = $sec['lessons'];
                $tests = $sec['tests'];
            ?>
                <div class="section">
                    <h3 class="section-title">
                        <span class="badge">Раздел <?= $section['id'] ?></span>
                        <?= htmlspecialchars($section['title']) ?>
                    </h3>
                    
                    <?php if (!empty($section['description'])): ?>
                        <p class="section-description"><?= nl2br(htmlspecialchars($section['description'])) ?></p>
                    <?php endif; ?>
                    
                    <div class="lessons-list">
                        <h4 style="font-size: 1.1rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-play-circle"></i>
                            Лекции (<?= count($lessons) ?>)
                        </h4>
                        
                        <?php if (count($lessons) === 0): ?>
                            <p style="color: var(--text-light); font-style: italic;">Лекций пока нет</p>
                        <?php else: ?>
                            <?php foreach ($lessons as $lesson): ?>
                                <div class="lesson-item">
                                    <div class="lesson-title">
                                        <i class="fas fa-play"></i>
                                        <?= htmlspecialchars($lesson['title']) ?>
                                    </div>
                                    <?php if (!empty($lesson['summary'])): ?>
                                        <p class="lesson-summary"><?= nl2br(htmlspecialchars($lesson['summary'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="tests-list" style="margin-top: 1.5rem;">
                        <h4 style="font-size: 1.1rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-clipboard-check"></i>
                            Тесты (<?= count($tests) ?>)
                        </h4>
                        
                        <?php if (count($tests) === 0): ?>
                            <p style="color: var(--text-light); font-style: italic;">Тестов пока нет</p>
                        <?php else: ?>
                            <?php foreach ($tests as $test): ?>
                                <div class="test-item">
                                    <div style="display: flex; align-items: center;">
                                        <div class="test-title">
                                            <i class="fas fa-question-circle"></i>
                                            <?= htmlspecialchars($test['title']) ?>
                                        </div>
                                        
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>