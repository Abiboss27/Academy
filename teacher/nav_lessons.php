<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_user'])) {
    die("Session user ID not set. Please log in.");
}
require_once __DIR__ . '/../database.php';
?>
<link rel="stylesheet" href="./src/css/nav_lesson.css">

<section class="content" id="main-content">
    <div class="container-fluid">
        <!-- Theme toggle button -->
        <div class="row mb-4">
            <div class="col-12 text-right">
                <button id="theme-toggle" class="theme-toggle">
                    <i class="fas fa-moon"></i>
                    <span>Dark Mode</span>
                </button>
            </div>
        </div>
        
        <div class="row">
            <?php
            $lessonsQuery = "SELECT COUNT(*) as total FROM lessons l
                JOIN courses c ON l.id_course = c.id
                WHERE c.id_users = '" . mysqli_real_escape_string($conn, $_SESSION['id_user']) . "'";
            $lessonsResult = mysqli_query($conn, $lessonsQuery);
            if (!$lessonsResult) {
                die('Error: ' . mysqli_error($conn));
            }
            $lessons = mysqli_fetch_assoc($lessonsResult)['total'];
            ?>
            
            <div class="col-lg-8 col-md-6">
                <div class="dashboard-card primary-card">
                    <div class="inner">
                        <h3><?php echo number_format($lessons); ?></h3>
                        <p>Мои лекции</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card action-card">
                    <div class="inner">
                        <h3><i class="fas fa-plus-circle mr-2"></i> Новая лекция</h3>
                        <p>Добавьте новый учебный материал</p>
                    </div>
                    <a href="Lessons/add_lesson.php" class="action-link">
                        Перейти к созданию <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                    <div class="icon">
                        <i class="fas fa-plus"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;
        const icon = themeToggle.querySelector('i');
        const text = themeToggle.querySelector('span');
        
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme') || 
                         (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        // Apply the saved theme
        if (savedTheme === 'dark') {
            body.classList.add('dark-mode');
            icon.classList.replace('fa-moon', 'fa-sun');
            text.textContent = 'Light Mode';
        }
        
        // Toggle theme on button click
        themeToggle.addEventListener('click', function() {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                icon.classList.replace('fa-moon', 'fa-sun');
                text.textContent = 'Light Mode';
                localStorage.setItem('theme', 'dark');
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
                text.textContent = 'Dark Mode';
                localStorage.setItem('theme', 'light');
            }
        });
    });
</script>