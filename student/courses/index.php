<?php
session_start();
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/CourseManager.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: /Academy/student/login.html");
    exit();
}

$userId = (int)$_SESSION['id_user'];
$pageTitle = "Каталог курсов";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="/Academy/student/assets/css/styleInd.css" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini">

<div class="wrapper">
    <?php include '../header.php'; ?>
<div class="wrapper">
    <section class="content">
        <?php
        $courseManager = new CourseManager($conn, $userId);
        $enrolledCourses = $courseManager->getEnrolledCourses();

        $filters = [
            'category' => $_GET['category'] ?? null,
            'price_type' => $_GET['price_type'] ?? null,
            'search' => $_GET['search'] ?? null,
            'rating' => $_GET['rating'] ?? null,
        ];

        $result = $courseManager->getCourses($filters);
        $categories = $courseManager->getCategories();
        ?>
        
        <!-- Фильтры -->
        <form method="GET" class="filters">
            <div>
                <label for="search">Поиск курсов</label>
                <input type="text" id="search" name="search" placeholder="Введите название или описание..."
                    value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>

            <div>
                <label for="category">Категория</label>
                <select id="category" name="category">
                    <option value="">Все категории</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= ($_GET['category'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label for="rating">Рейтинг</label>
                <select id="rating" name="rating">
                    <option value="">Любой рейтинг</option>
                    <option value="4" <?= ($_GET['rating'] ?? '') == 4 ? 'selected' : '' ?>>4+ ★</option>
                    <option value="3" <?= ($_GET['rating'] ?? '') == 3 ? 'selected' : '' ?>>3+ ★</option>
                </select>
            </div>

            <div class="price-filter">
                <label><input type="radio" name="price_type" value="all" <?= empty($_GET['price_type']) ? 'checked' : '' ?>> Все</label>
                <label><input type="radio" name="price_type" value="free" <?= ($_GET['price_type'] ?? '') === 'free' ? 'checked' : '' ?>> Бесплатные</label>
                <label><input type="radio" name="price_type" value="paid" <?= ($_GET['price_type'] ?? '') === 'paid' ? 'checked' : '' ?>> Платные</label>
            </div>

            <button type="submit"><i class="fas fa-filter"></i> Применить</button>
        </form>

        <div class="courses-grid">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($course = $result->fetch_assoc()):
                    $courseId = (int)$course['id'];
                    $price = (float)$course['price'];
                    $discount = (float)$course['discount'];

                    $finalPrice = $price > 0 ? ($discount > 0 ? "<s>{$price} ₽</s> " . ($price - $discount) . ' ₽' : $price . ' ₽') : 'Бесплатно';
                    $isEnrolled = in_array($courseId, $enrolledCourses);
                    ?>
                    <div class="course-card">
                        <img src="<?= htmlspecialchars($course['Picture_Link'] ?: 'https://via.placeholder.com/300x180?text=No+Image') ?>" alt="Обложка курса">
                        <div class="course-card-content">
                            <h3><?= htmlspecialchars($course['title']) ?></h3>
                            <div class="category"><?= htmlspecialchars($course['category_name']) ?></div>
                            <p class="description"><?= htmlspecialchars($course['short_description']) ?></p>

                            <div class="details">
                                <span>Уровень: <?= htmlspecialchars($course['lvl']) ?></span><br>
                                <span>Язык: <?= htmlspecialchars($course['language']) ?></span><br>
                                <div class="rating">
                                    <?= str_repeat('★', round($course['avg_rating'])) ?>
                                    <?= str_repeat('☆', 5 - round($course['avg_rating'])) ?>
                                    <span>(<?= round($course['avg_rating'], 1) ?>)</span>
                                </div>
                                <div class="price"><?= $finalPrice ?></div>
                            </div>

                            <?php if ($isEnrolled): ?>
                                <button class="enrolled-btn" disabled><i class="fas fa-check-circle"></i> Вы записаны</button>
                            <?php else: ?>
                                <button class="enroll-btn" data-course-id="<?= $courseId ?>" data-course-price="<?= $price ?>">
                                    <?= $price > 0 ? '<i class="fas fa-shopping-cart"></i> Купить' : '<i class="fas fa-user-plus"></i> Записаться' ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Курсы не найдены по заданным критериям.</p>
            <?php endif; ?>
        </div>
    </section>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.enroll-btn').forEach(button => {
        button.addEventListener('click', () => {
            const courseId = button.dataset.courseId;
            const coursePrice = parseFloat(button.dataset.coursePrice);

            if (!courseId) return;

            if (coursePrice > 0) {
                window.location.href = `/Academy/student/courses/payment_form.php?course_id=${encodeURIComponent(courseId)}`;
            } else {
                Swal.fire({
                    title: 'Записаться на курс?',
                    text: 'Вы уверены, что хотите записаться на этот курс?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4e73df',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Да, записаться',
                    cancelButtonText: 'Отмена'
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch('/Academy/student/courses/enroll.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'Accept': 'application/json'
                            },
                            body: `course_id=${encodeURIComponent(courseId)}`
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Успешно!',
                                    text: 'Вы успешно записаны на курс.',
                                    icon: 'success',
                                    confirmButtonColor: '#4e73df'
                                }).then(() => {
                                    button.disabled = true;
                                    button.innerHTML = '<i class="fas fa-check-circle"></i> Вы записаны';
                                    button.classList.remove('enroll-btn');
                                    button.classList.add('enrolled-btn');
                                });
                            } else {
                                Swal.fire('Ошибка!', data.error || 'Не удалось записаться на курс.', 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Ошибка!', 'Не удалось подключиться к серверу.', 'error');
                        });
                    }
                });
            }
        });
    });
});
</script>
</body>
</html>
