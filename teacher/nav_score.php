<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    die("Session user ID not set. Please log in.");
}

require_once __DIR__ . '/../database.php';
?>

<link rel="stylesheet" href="./src/css/nav_score.css">
<section class="content" id="main-content">
    <div class="container-fluid">
        <?php
        $passingScore = 75;
        
        $passedStudentsQuery = "
            SELECT COUNT(DISTINCT s.id_users) AS total_passed
            FROM scores s
            JOIN tests t ON s.id_test = t.id
            JOIN courses c ON t.id_course = c.id 
            WHERE c.id_users = '" . mysqli_real_escape_string($conn, $_SESSION['id_user']) . "'
            AND s.score >= $passingScore";

        $passedStudentsResult = mysqli_query($conn, $passedStudentsQuery);
        
        if (!$passedStudentsResult) {
            die('Error: ' . mysqli_error($conn));
        }

        $passedStudents = mysqli_fetch_assoc($passedStudentsResult)['total_passed'];
        ?>

        <div class="col-lg-4 col-md-6 col-12 mb-4">
            <div class="success-card">
                <div class="inner">
                    <h3><?php echo number_format($passedStudents); ?></h3>
                    <p>Студенты, успешно сдавшие тесты</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <a href="#" class="more-info">
                    Подробнее <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

    </div>
</section>

<script>
$(document).ready(function() {
    // Анимация при загрузке
    $('.success-card').hide().fadeIn(800);
    
    // Обработчик клика для кнопки "Подробнее"
    $('.more-info').click(function(e) {
        e.preventDefault();
        // Здесь можно добавить переход на страницу с детальной статистикой
        alert('Будет открыта страница с детальной статистикой студентов');
    });
});
</script>