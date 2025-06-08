<?php
session_start();  // Start the session

// Check if the session user ID is set
if (!isset($_SESSION['id_user'])) {
    die("Session user ID not set. Please log in.");
}

require_once __DIR__ . '/../database.php';
?>

<section class="content" id="main-content">
    <div class="container-fluid">
        <!-- Add mode toggle button at the top -->
        <div class="row mb-3">
            <div class="col-12 text-right">
                <button id="theme-toggle" class="btn btn-sm btn-secondary">
                    <i class="fas fa-moon"></i> Dark Mode
                </button>
            </div>
        </div>
        
        <?php
// Include database connection
require_once __DIR__ . '/../database.php';

// Check if session user ID is set
if (!isset($_SESSION['id_user'])) {
    die("Session user ID not set.");
}

// Get lessons
$lessonsQuery = "SELECT COUNT(*) as total FROM tests
    JOIN courses c ON l.id_course = c.id
    WHERE c.id_users = '" . $_SESSION['id_user'] . "'";
$lessonsResult = mysqli_query($conn, $lessonsQuery);
if (!$lessonsResult) {
    die('Error: ' . mysqli_error($conn));
}
$lessons = mysqli_fetch_assoc($lessonsResult)['total'];
?>

            <div class="col-lg-8 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?php echo $lessons; ?></h3>
                        <p> Мои лекции </p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-light">
                    <div class="inner">
                        <a href="#"> Добавить лекция </a>
                    </div> 
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>

</section>

<!-- Add CSS for dark mode -->
<style>
    .dark-mode {
        background-color: #1a1a1a;
        color: #f8f9fa;
    }
    
    .dark-mode .content-wrapper,
    .dark-mode .card,
    .dark-mode .small-box {
        background-color: #2d2d2d;
        color: #f8f9fa;
        border-color: #444;
    }
    
    .dark-mode .small-box .inner {
        color: #f8f9fa;
    }
    
    .dark-mode .small-box .icon {
        color: rgba(255,255,255,0.15);
    }
</style>

<!-- Add JavaScript for theme switching -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;
        
        // Check for saved user preference or use system preference
        const savedTheme = localStorage.getItem('theme') || 
                         (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        // Apply the saved theme
        if (savedTheme === 'dark') {
            body.classList.add('dark-mode');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i> Light Mode';
        }
        
        // Toggle theme on button click
        themeToggle.addEventListener('click', function() {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                themeToggle.innerHTML = '<i class="fas fa-sun"></i> Light Mode';
                localStorage.setItem('theme', 'dark');
            } else {
                themeToggle.innerHTML = '<i class="fas fa-moon"></i> Dark Mode';
                localStorage.setItem('theme', 'light');
            }
        });
    });
</script>