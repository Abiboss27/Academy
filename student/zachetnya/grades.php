<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'C:/xampp/htdocs/Academy/database.php';

// id пользователя из сессии
$userId = $_SESSION['id_user'] ?? 1;

// Функции для курсов, разделов, тестов, оценок
function getStudyingCourses($conn, $userId) {
    $sql = "SELECT c.id, c.title FROM enrol e JOIN courses c ON e.id_course = c.id WHERE e.id_user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[$row['id']] = ['title' => $row['title']];
    }
    $stmt->close();
    return $courses;
}
function getCourseSections($conn, $courseId) {
    $sql = "SELECT id, title FROM section WHERE id_course = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $sections = [];
    while ($row = $result->fetch_assoc()) {
        $sections[$row['id']] = $row['title'];
    }
    $stmt->close();
    return $sections;
}
function getSectionTests($conn, $sectionId) {
    $sql = "SELECT id, title FROM tests WHERE id_section = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sectionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $tests = [];
    while ($row = $result->fetch_assoc()) {
        $tests[$row['id']] = $row['title'];
    }
    $stmt->close();
    return $tests;
}
function getUserScore($conn, $userId, $testId) {
    $sql = "SELECT MAX(score) as max_score FROM scores WHERE id_users = ? AND id_test = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $testId);
    $stmt->execute();
    $result = $stmt->get_result();
    $score = null;
    if ($row = $result->fetch_assoc()) {
        $score = $row['max_score'];
    }
    $stmt->close();
    return $score;
}

function getalltest($conn, $userId, $courseId) {
    $sql = "SELECT count(id_test) as num_test FROM scores WHERE id_users = ? AND course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $numtest = 0;
    if ($row = $result->fetch_assoc()) {
        $numtest = $row['num_test'];
       
    }
    $stmt->close();
    return $numtest;
}

// Получаем все курсы пользователя
$courses = getStudyingCourses($conn, $userId);

// Разделяем курсы на изучаемые и завершенные
$studyingCourses = [];
$completedCourses = [];

foreach ($courses as $courseId => $courseData) {
    $sections = getCourseSections($conn, $courseId);
    $numtests = getalltest($conn, $userId, $courseId);
    $allTestsPassed = true;
    foreach ($sections as $sectionId => $sectionTitle) {
        $tests = getSectionTests($conn, $sectionId);
        
        foreach ($tests as $testId => $testTitle) {
            $score = getUserScore($conn, $userId, $testId);
            if ($score === null || $score < 75) {
                $allTestsPassed = false;
                break 2;
            }
        }
    }
    if ($allTestsPassed &&  $numtests != 0) {
        $completedCourses[$courseId] = $courseData;
    } else {
        $studyingCourses[$courseId] = $courseData;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/Academy/student/assets/css/zach.css">
</head>
<body class="hold-transition sidebar-mini">

<div class="wrapper">
    <?php include '../header.php'; ?>

    <section class="content">
       <h1>Мои курсы</h1>
        <section>
            <h2>Изучаемые курсы</h2>
            <?php if (empty($studyingCourses)): ?>
                <div class="empty-state">
                    <img src="https://cdn-icons-png.flaticon.com/512/4076/4076478.png" alt="Нет курсов">
                    <p>Нет изучаемых курсов.</p>
                </div>
            <?php else: ?>
                <?php foreach ($studyingCourses as $courseId => $course):
                    $sectionStmt = $conn->prepare("SELECT COUNT(*) as total FROM section WHERE id_course = ?");
                    $sectionStmt->bind_param('i', $courseId);
                    $sectionStmt->execute();
                    $sectionResult = $sectionStmt->get_result();
                    $totalSections = $sectionResult->fetch_assoc()['total'];
                    $sectionStmt->close();

                    $completedStmt = $conn->prepare("
                        SELECT COUNT(DISTINCT s.id) as completed 
                        FROM scores sc
                        JOIN section s ON sc.section_id = s.id
                        WHERE sc.id_users = ? 
                        AND sc.course_id = ? 
                        AND sc.score >= 75
                    ");
                    $completedStmt->bind_param('ii', $userId,$courseId);
                    $completedStmt->execute();
                    $completedResult = $completedStmt->get_result();
                    $completedSections = $completedResult->fetch_assoc()['completed'];
                    $completedStmt->close();

                    $progress = $totalSections > 0 ? round(($completedSections / $totalSections) * 100) : 0;
                    ?>
                    <div class="course">
                        <div class="section-header">
                            <h3><?= htmlspecialchars($course['title']) ?></h3>
                            <div class="progress-text">Прогресс: <?= $progress ?>%</div>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress" style="width: <?= $progress ?>%"></div>
                            </div>
                        </div>
                        <?php
                        $sections = getCourseSections($conn, $courseId);
                        $totalTests = 0;
                        $passedTests = 0;
                        ?>
                        <ul>
                            <?php foreach ($sections as $sectionId => $sectionTitle): ?>
                                <li>
                                    <strong><?= htmlspecialchars($sectionTitle) ?></strong>
                                    <ul>
                                        <?php
                                        $tests = getSectionTests($conn, $sectionId);
                                        foreach ($tests as $testId => $testTitle):
                                            $score = getUserScore($conn, $userId, $testId);
                                            $totalTests++;
                                            if ($score !== null && $score >= 75) $passedTests++;
                                            $scoreClass = ($score === null) ? 'not-taken' : ($score >= 75 ? 'passed' : 'failed');
                                        ?>
                                            <li class="test-item">
                                                <span class="test-name"><?= htmlspecialchars($testTitle) ?></span>
                                                <span class="test-score <?= $scoreClass ?>">
                                                    <?= $score !== null ? $score . '%' : 'не пройден' ?>
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const progress = <?= $totalTests > 0 ? round(($passedTests / $totalTests) * 100) : 0 ?>;
                                const progressElement = document.querySelector('.course:last-child .progress');
                                const progressText = document.querySelector('.course:last-child .progress-text');
                                if (progressElement && progressText) {
                                    progressElement.style.width = progress + '%';
                                    progressText.textContent = 'Прогресс: ' + progress + '%';
                                }
                            });
                        </script>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
        <section>
            <h2>Завершённые курсы</h2>
            <?php if (empty($completedCourses)): ?>
                <div class="empty-state">
                    <img src="https://cdn-icons-png.flaticon.com/512/4076/4076471.png" alt="Нет курсов">
                    <p>Нет завершённых курсов.</p>
                </div>
            <?php else: ?>
                <?php foreach ($completedCourses as $courseId => $course): ?>
                    <div class="course completed">
                        <h3><?= htmlspecialchars($course['title']) ?></h3>
                        <?php $sections = getCourseSections($conn, $courseId); ?>
                        <ul>
                            <?php foreach ($sections as $sectionId => $sectionTitle): ?>
                                <li>
                                    <strong><?= htmlspecialchars($sectionTitle) ?></strong>
                                    <ul>
                                        <?php
                                        $tests = getSectionTests($conn, $sectionId);
                                        foreach ($tests as $testId => $testTitle):
                                            $score = getUserScore($conn, $userId, $testId);
                                            $scoreClass = ($score === null) ? 'not-taken' : ($score >= 75 ? 'passed' : 'failed');
                                        ?>
                                            <li class="test-item">
                                                <span class="test-name"><?= htmlspecialchars($testTitle) ?></span>
                                                <span class="test-score <?= $scoreClass ?>">
                                                    <?= $score !== null ? $score . '%' : 'не пройден' ?>
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <!-- Кнопка для оценки курса -->
                        <button class="btn-rate-course" data-course="<?= $courseId ?> ">Оценить курс</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>

    <!-- Модальное окно для оценки курса -->
    <div id="rateCourseModal">
      <div class="modal-content">
        <h3>Оцените курс</h3>
        <form id="rateCourseForm">
          <input type="hidden" name="course_id" id="modalCourseId">
          <div class="form-group">
            <label for="rating">Ваша оценка:</label>
            <select name="rating" id="rating" class="form-control" required>
              <option value="">Выберите...</option>
              <option value="5">Отлично (5)</option>
              <option value="4">Хорошо (4)</option>
              <option value="3">Удовлетворительно (3)</option>
              <option value="2">Плохо (2)</option>
              <option value="1">Очень плохо (1)</option>
            </select>
          </div>
          <div class="form-group">
            <label for="comment">Комментарий:</label>
            <textarea name="comment" id="comment" class="form-control" rows="3" placeholder="Ваши замечания..."></textarea>
          </div>
          <div style="text-align:right;">
            <button type="button" onclick="closeRateModal()" class="btn btn-secondary">Отмена</button>
            <button type="submit" class="btn btn-primary">Отправить</button>
          </div>
        </form>
      </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 <script>
function closeRateModal() {
        document.getElementById('rateCourseModal').style.display = 'none';
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-rate-course').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('modalCourseId').value = this.dataset.course;
                document.getElementById('rateCourseModal').style.display = 'flex';
            });
        });
        document.getElementById('rateCourseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('save_course_rating.php', {
                method: 'POST',
                body: formData
            }).then(resp => resp.json())
            .then(data => {
                if (data.success) {
                    alert('Спасибо за вашу оценку!');
                    closeRateModal();
                } else {
                    alert('Ошибка: ' + data.error);
                }
            }).catch(() => alert('Ошибка отправки'));
        });
    });
    </script>
</body>
</html>
