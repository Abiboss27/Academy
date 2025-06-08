<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'C:/xampp/htdocs/Academy/database.php';

// Your database connection file

// Check if user is logged in (you should implement your own auth system)
if (!isset($_SESSION['id_user'])) {
    header("Location:../../login.html");
    exit();
}



$test_id = $_GET['test_id'] ?? 0;

// Get test info
$test = [];
$stmt = $conn->prepare("SELECT t.*, s.title as section_title, c.title as course_title 
                       FROM tests t
                       JOIN section s ON t.id_section = s.id
                       JOIN courses c ON t.id_course = c.id
                       WHERE t.id = ?");
$stmt->bind_param("i", $test_id);
$stmt->execute();
$result = $stmt->get_result();
$test = $result->fetch_assoc();
$stmt->close();

// Handle question submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
   if ($action === 'add_question') {
    $title = $_POST['title'];
    $type = $_POST['type'];
    $options = $_POST['options'] ?? [];
    $options_json = json_encode($options);
    $number_of_options = count($options);
    $correct_answers = json_encode($_POST['correct_answers'] ?? []);

    $stmt = $conn->prepare("INSERT INTO question (id_test, title, type, number_of_options, options, correct_answers) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $test_id, $title, $type, $number_of_options, $options_json, $correct_answers);
    $stmt->execute();
    $stmt->close();
}
 elseif ($action === 'delete_question') {
        $question_id = $_POST['question_id'];
        $stmt = $conn->prepare("DELETE FROM question WHERE id_question = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: manage_questions.php?test_id=$test_id");
    exit();
}

// Get existing questions
$questions = [];
$stmt = $conn->prepare("SELECT * FROM question WHERE id_test = ?");
$stmt->bind_param("i", $test_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['options'] = json_decode($row['options'], true);
    $row['correct_answers'] = json_decode($row['correct_answers'], true);
    $questions[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <a href="../tests_table.php" class="back-button">← Назад</a>
    <title>Manage Questions</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
</head>
<body>
    <div class="container">
        <h1>Manage Questions</h1>
        <p>Course: <?= htmlspecialchars($test['course_title'] ?? '') ?></p>
        <p>Section: <?= htmlspecialchars($test['section_title'] ?? '') ?></p>
        <p>Test: <?= htmlspecialchars($test['title'] ?? '') ?></p>
        
        <div class="questions-list">
            <h2>Existing Questions</h2>
            <?php if (empty($questions)): ?>
                <p>No questions added yet.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($questions as $question): ?>
                        <li>
                            <div class="question">
                                <strong><?= htmlspecialchars($question['title']) ?></strong>
                                <span class="type">(<?= $question['type'] ?>)</span>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_question">
                                    <input type="hidden" name="question_id" value="<?= $question['id_question'] ?>">
                                    <button type="submit" class="btn delete">Delete</button>
                                </form>
                            </div>
                            <div class="options">
                                <?php foreach ($question['options'] as $index => $option): ?>
                                    <div class="option <?= in_array($index, $question['correct_answers']) ? 'correct' : '' ?>">
                                        <?= htmlspecialchars($option) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <div class="add-question">
            <h2>Add New Question</h2>
            <form id="question-form" method="POST">
                <input type="hidden" name="action" value="add_question">
                
                <div class="form-group">
                    <label for="question-text">Question Text:</label>
                    <textarea id="question-text" name="title" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="question-type">Question Type:</label>
                    <select id="question-type" name="type" required onchange="updateOptionsField()">
                        <option value="single">Single Correct Answer</option>
                        <option value="multiple">Multiple Correct Answers</option>
                        <option value="number">Numeric Answer</option>
                    </select>
                </div>
                
                <div id="options-container" class="form-group">
                    <label>Options:</label>
                    <div class="option-controls">
                        <input type="number" id="option-count" min="2" max="10" value="4" onchange="generateOptionFields()">
                        <button type="button" onclick="generateOptionFields()">Generate Fields</button>
                    </div>
                    <div id="option-fields"></div>
                </div>
                
                <button type="submit" class="btn">Add Question</button>
                <a href="index.php" class="btn cancel">Back to Tests</a>
            </form>
        </div>
    </div>
</body>
</html>