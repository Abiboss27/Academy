<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'C:/xampp/htdocs/Academy/database.php';

// Проверяем авторизацию
if (!isset($_SESSION['id_user'])) {
    die('Пользователь не авторизован');
}
$currentUserId = (int)$_SESSION['id_user'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="/Academy/student/assets/css/mycourses.css" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini">

<div class="wrapper">
    <?php include '../header.php'; ?>

    <section class="content">
          <div class="courses-grid">
            <?php
            // Формируем условия и параметры для запроса
            $where = ["e.id_user = ?"];
            $params = [$currentUserId];
            $types = 'i';

            if (!empty($_GET['category'])) {
                $where[] = "c.id_category = ?";
                $params[] = (int)$_GET['category'];
                $types .= 'i';
            }

            if (!empty($_GET['price_type'])) {
                if ($_GET['price_type'] === 'free') {
                    $where[] = "c.price = 0";
                } elseif ($_GET['price_type'] === 'paid') {
                    $where[] = "c.price > 0";
                }
            }

            if (!empty($_GET['search'])) {
                $where[] = "(c.title LIKE ? OR c.short_description LIKE ?)";
                $search = "%{$_GET['search']}%";
                $params[] = $search;
                $params[] = $search;
                $types .= 'ss';
            }

            $where[] = "c.id_statut = 1";

            $sql = "SELECT c.*, cat.name AS category_name, AVG(e.rating) AS avg_rating, lv.name AS lvl
                    FROM courses c
                    INNER JOIN enrol e ON c.id = e.id_course
                    LEFT JOIN categories cat ON c.id_category = cat.id
                    LEFT JOIN levels lv ON c.id_level = lv.id
                    WHERE " . implode(' AND ', $where) . "
                    GROUP BY c.id";

            // Добавляем HAVING, если фильтр по рейтингу есть
            if (!empty($_GET['rating'])) {
                $sql .= " HAVING AVG(e.rating) >= ?";
                $params[] = (int)$_GET['rating'];
                $types .= 'i';
            }

            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die('Ошибка подготовки запроса: ' . $conn->error);
            }

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            while ($course = $result->fetch_assoc()):
                // Цена с учетом скидки
                if ($course['price'] == 0) {
                    $price = 'Бесплатно';
                } else {
                    $price = $course['discount'] > 0
                        ? "<s>{$course['price']} ₽</s> " . ($course['price'] - $course['discount']) . " ₽"
                        : $course['price'] . ' ₽';
                }

                // Получаем количество разделов
                $sectionStmt = $conn->prepare("SELECT COUNT(*) as total FROM section WHERE id_course = ?");
                $sectionStmt->bind_param('i', $course['id']);
                $sectionStmt->execute();
                $sectionResult = $sectionStmt->get_result();
                $totalSections = $sectionResult->fetch_assoc()['total'];
                $sectionStmt->close();

                // Количество завершенных разделов (оценка >= 75)
                $completedStmt = $conn->prepare("
                    SELECT COUNT(DISTINCT s.id) as completed 
                    FROM scores sc
                    JOIN section s ON sc.section_id = s.id
                    WHERE sc.id_users = ? 
                    AND sc.course_id =  ?
                    AND sc.score >= 75
                ");
                $completedStmt->bind_param('ii', $currentUserId, $course['id']);
                $completedStmt->execute();
                $completedResult = $completedStmt->get_result();
                $completedSections = $completedResult->fetch_assoc()['completed'];
                $completedStmt->close();

                $progress = $totalSections > 0 ? round(($completedSections / $totalSections) * 100) : 0;
                ?>

                <div class="course-card">
                    <img src="<?= htmlspecialchars($course['Picture_Link']) ?>" alt="Обложка курса">
                    <h3><?= htmlspecialchars($course['title']) ?></h3>
                    <div class="category"><?= htmlspecialchars($course['category_name']) ?></div>
                    <p class="description"><?= htmlspecialchars($course['short_description']) ?></p>

                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= $progress ?>%">
                            <?= $progress ?>%
                        </div>
                    </div>

                    <div class="details">
                        <span>Уровень: <?= htmlspecialchars($course['lvl']) ?></span>
                        <span>Язык: <?= htmlspecialchars($course['language']) ?></span>
                       
                    </div>

                    <div class="course-buttons">
                        <a href="http://localhost:80/Academy/student/courses/details.php?id=<?= $course['id'] ?>" class="btn btn-details">Подробнее</a>
                        <a href="http://localhost:80/Academy/student/test_and_lessons/study.php?id=<?= $course['id'] ?>" class="btn btn-start">Начать</a>
                    </div>
                </div>

            <?php endwhile; ?>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
