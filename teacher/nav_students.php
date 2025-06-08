<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверка авторизации
if (!isset($_SESSION['id_user'])) {
    die("Session user ID not set. Please log in.");
}

require_once __DIR__ . '/../database.php';

// Получение количества активных студентов
$activeStudents = 0;
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM enrol e
    JOIN courses c ON e.id_course = c.id
    WHERE c.id_users = ?
");
$stmt->bind_param("i", $_SESSION['id_user']);
$stmt->execute();
$stmt->bind_result($activeStudents);
$stmt->fetch();
$stmt->close();
?>
<link rel="stylesheet" href="./src/css/nav_student.css">
<section class="content" id="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-7 col-md-6 col-12">
                <div class="stats-card animated-card">
                    <div class="inner">
                        <h3><?= number_format($activeStudents, 0, '', ' ') ?></h3>
                        <p>Активных студентов</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Добавляем эффект при наведении на мобильных устройствах
    document.querySelectorAll('.stats-card').forEach(card => {
        card.addEventListener('touchstart', function() {
            this.classList.add('hover-effect');
        });
        
        card.addEventListener('touchend', function() {
            setTimeout(() => {
                this.classList.remove('hover-effect');
            }, 200);
        });
    });
</script>