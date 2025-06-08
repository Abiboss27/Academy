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

// Получение ID теста и курса
$test_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if ($test_id <= 0 || $course_id <= 0) {
    die('Неверные параметры');
}

// Получение информации о тесте
$stmt = $conn->prepare("SELECT * FROM tests WHERE id = ?");
$stmt->bind_param('i', $test_id);
$stmt->execute();
$test = $stmt->get_result()->fetch_assoc();

if (!$test) {
    die('Тест не найден');
}

// Получение вопросов теста
$stmt = $conn->prepare("SELECT * FROM question WHERE id_test = ?");
$stmt->bind_param('i', $test_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Получение информации о курсе
$stmt = $conn->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param('i', $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

// Проверка, был ли уже пройден тест
$stmt = $conn->prepare("
    SELECT COUNT(*) as attempts, MAX(score) as best_score 
    FROM scores 
    WHERE id_users = ? AND id_test = ?
");
$stmt->bind_param('ii', $_SESSION['id_user'], $test_id);
$stmt->execute();
$attempts_info = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($test['title']) ?> - Тест</title>
    <link rel="stylesheet" href="styles/course_styles.css">
</head>
<body>
    <div class="test-container">
        <header class="test-header">
            <a href="study.php?id=<?= $course_id ?>" class="back-link">← Назад к курсу</a>
            <h1><?= htmlspecialchars($course['title']) ?></h1>
            <h2><?= htmlspecialchars($test['title']) ?></h2>
            <?php if ($attempts_info['attempts'] > 0): ?>
            <div class="test-info">
                <p>Попыток: <?= $attempts_info['attempts'] ?></p>
                <p>Лучший результат: <?= $attempts_info['best_score'] ?>%</p>
            </div>
            <?php endif; ?>
        </header>

        <form id="test-form" action="test_result.php" method="post">
            <input type="hidden" name="test_id" value="<?= $test_id ?>">
            <input type="hidden" name="course_id" value="<?= $course_id ?>">
            
            <div class="questions-container">
                <?php foreach ($questions as $index => $question): ?>
                <div class="question" data-type="<?= $question['type'] ?>">
                    <h3 class="question-title"><?= ($index + 1) ?>. <?= htmlspecialchars($question['title']) ?></h3>
                    
                    <?php if ($question['type'] === 'single' || $question['type'] === 'multiple'): 
                        $options = json_decode($question['options'], true);
                    ?>
                        <div class="options">
                            <?php foreach ($options as $key => $option): ?>
                            <div class="option">
                                <?php if ($question['type'] === 'single'): ?>
                                <input type="radio" name="answers[<?= $question['id_question'] ?>]" id="option_<?= $question['id_question'] ?>_<?= $key ?>" value="<?= $key ?>">
                                <?php else: ?>
                                <input type="checkbox" name="answers[<?= $question['id_question'] ?>][]" id="option_<?= $question['id_question'] ?>_<?= $key ?>" value="<?= $key ?>">
                                <?php endif; ?>
                                <label for="option_<?= $question['id_question'] ?>_<?= $key ?>"><?= htmlspecialchars($option) ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($question['type'] === 'number'): ?>
                        <div class="number-answer">
                            <input type="number" name="answers[<?= $question['id_question'] ?>]" step="any">
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="test-controls">
                <button type="submit" class="submit-test">Отправить ответы</button>
                <div class="timer" data-duration=""> <span id="time-display"></span>
                </div>
            </div>
        </form>
    </div>

    <script src="/Academy/student/courses/js/course_scripts.js"></script>
</body>
</html>