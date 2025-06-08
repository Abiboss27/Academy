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

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: study.php');
    exit();
}

// Получение данных из формы
$test_id = isset($_POST['test_id']) ? (int)$_POST['test_id'] : 0;
$course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
$user_answers = $_POST['answers'] ?? [];

if ($test_id <= 0 || $course_id <= 0) {
    die('Неверные параметры');
}

// Получение вопросов теста
$stmt = $conn->prepare("SELECT * FROM question WHERE id_test = ?");
$stmt->bind_param('i', $test_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


// Получение section_id из базы данных
$stmt = $conn->prepare("SELECT id_section FROM tests WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $test_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();


// Проверка ответов
$correct_answers_count = 0;
$total_questions = count($questions);
$results = [];

foreach ($questions as $question) {
    $correct_answers = json_decode($question['correct_answers'], true);
    $question_id = $question['id_question'];
    
    if (!isset($user_answers[$question_id])) {
        $results[$question_id] = [
            'correct' => false,
            'user_answer' => null,
            'correct_answer' => $correct_answers
        ];
        continue;
    }
    
    $user_answer = $user_answers[$question_id];
    
    if ($question['type'] === 'single') {
        $is_correct = in_array($user_answer, $correct_answers);
    } elseif ($question['type'] === 'multiple') {
        $is_correct = empty(array_diff($user_answer, $correct_answers)) && 
                      empty(array_diff($correct_answers, $user_answer));
    } elseif ($question['type'] === 'number') {
        $is_correct = abs((float)$user_answer - (float)$correct_answers[0]) < 0.0001;
    }
    
    if ($is_correct) {
        $correct_answers_count++;
    }
    
    $results[$question_id] = [
        'correct' => $is_correct,
        'user_answer' => $user_answer,
        'correct_answer' => $correct_answers
    ];
}

// Расчет результата в процентах (защита от деления на ноль)
$score = $total_questions > 0 ? round(($correct_answers_count / $total_questions) * 100) : 0;

// Сохранение результата в базу данных
$user_id = $_SESSION['id_user'];
$section_id = $result['id_section'] ?? 0;

$stmt = $conn->prepare("
    INSERT INTO scores 
    (id_users, id_test, course_id, section_id, score, attempt_count) 
    VALUES (?, ?, ?, ?, ?, 1)
    ON DUPLICATE KEY UPDATE 
    score = IF(VALUES(score) > score, VALUES(score), score),
    attempt_count = attempt_count + 1
");
$stmt->bind_param('iiiii', 
    $user_id, 
    $test_id, 
    $course_id, 
    $section_id, 
    $score
);
$stmt->execute();
// Получение информации о тесте и курсе
$stmt = $conn->prepare("SELECT title FROM tests WHERE id = ?");
$stmt->bind_param('i', $test_id);
$stmt->execute();
$test = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param('i', $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты теста</title>
    <link rel="stylesheet" href="styles/course_styles.css">
</head>
<body>
    <div class="test-result-container">
        <header class="result-header">
            <a href="study.php?id=<?= $course_id ?>" class="back-link">← Назад к курсу</a>
            <h1><?= htmlspecialchars($course['title']) ?></h1>
            <h2>Результаты теста: <?= htmlspecialchars($test['title']) ?></h2>
        </header>

        <div class="result-summary">
            <div class="score-circle" data-score="<?= $score ?>">
                <svg class="circle-chart" viewBox="0 0 36 36">
                    <path class="circle-bg"
                        d="M18 2.0845
                          a 15.9155 15.9155 0 0 1 0 31.831
                          a 15.9155 15.9155 0 0 1 0 -31.831"
                    />
                    <path class="circle-fill"
                        stroke-dasharray="<?= $score ?>, 100"
                        d="M18 2.0845
                          a 15.9155 15.9155 0 0 1 0 31.831
                          a 15.9155 15.9155 0 0 1 0 -31.831"
                    />
                </svg>
                <div class="score-text"><?= $score ?>%</div>
            </div>
            <p class="score-description">
                Вы ответили правильно на <?= $correct_answers_count ?> из <?= $total_questions ?> вопросов.
            </p>
        </div>

        <div class="detailed-results">
            <h3>Подробные результаты:</h3>
            <?php foreach ($questions as $index => $question): 
                $result = $results[$question['id_question']];
                $options = json_decode($question['options'], true);
            ?>
            <div class="question-result <?= $result['correct'] ? 'correct' : 'incorrect' ?>">
                <h4><?= ($index + 1) ?>. <?= htmlspecialchars($question['title']) ?></h4>
                
                <?php if ($result['correct']): ?>
                <p class="result-status correct">✓ Верно</p>
                <?php else: ?>
                <p class="result-status incorrect">✗ Неверно</p>
                <?php endif; ?>
                
                <?php if ($question['type'] === 'single' || $question['type'] === 'multiple'): ?>
                    <div class="user-answer">
                        <p>Ваш ответ:</p>
                        <?php if (is_array($result['user_answer'])): ?>
                            <?php foreach ($result['user_answer'] as $answer): ?>
                            <p><?= htmlspecialchars($options[$answer] ?? '') ?></p>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p><?= htmlspecialchars($options[$result['user_answer']] ?? '') ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="correct-answer">
                        <p>Правильный ответ:</p>
                        <?php foreach ($result['correct_answer'] as $answer): ?>
                        <p><?= htmlspecialchars($options[$answer] ?? '') ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($question['type'] === 'number'): ?>
                    <div class="user-answer">
                        <p>Ваш ответ: <?= htmlspecialchars($result['user_answer']) ?></p>
                    </div>
                    <div class="correct-answer">
                        <p>Правильный ответ: <?= htmlspecialchars($result['correct_answer'][0]) ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="result-actions">
            <a href="study.php?id=<?= $course_id ?>" class="btn-back">Вернуться к курсу</a>
            <a href="test.php?id=<?= $test_id ?>&course_id=<?= $course_id ?>" class="btn-retry">Попробовать снова</a>
        </div>
    </div>
</body>
</html>