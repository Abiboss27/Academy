
<?php
        // Include database connection
        require_once __DIR__ . '/../database.php';

        // Get total courses
        $totalCoursesQuery = "SELECT COUNT(*) as total FROM courses WHERE id_users = '" . $_SESSION['id_user'] . "'";
        $totalCoursesResult = mysqli_query($conn, $totalCoursesQuery);
        if (!$totalCoursesResult) {
            die('Error: ' . mysqli_error($conn));
        }
        $totalCourses = mysqli_fetch_assoc($totalCoursesResult)['total'];

        // Get active students
        $activeStudentsQuery = "SELECT COUNT(*) AS total
            FROM enrol e
            JOIN courses c ON e.id_course = c.id
            WHERE c.id_users = '" . $_SESSION['id_user'] . "'";
        $activeStudentsResult = mysqli_query($conn, $activeStudentsQuery);
        if (!$activeStudentsResult) {
            die('Error: ' . mysqli_error($conn));
        }
        $activeStudents = mysqli_fetch_assoc($activeStudentsResult)['total'];

        // Get lessons
        $lessonsQuery = "SELECT COUNT(*) as total FROM lessons l
            JOIN courses c ON l.id_course = c.id
            WHERE c.id_users = '" . $_SESSION['id_user'] . "'";
        $lessonsResult = mysqli_query($conn, $lessonsQuery);
        if (!$lessonsResult) {
            die('Error: ' . mysqli_error($conn));
        }
        $lessons = mysqli_fetch_assoc($lessonsResult)['total'];

        // Get tests
        $testsQuery = "SELECT COUNT(*) as total FROM tests l
            JOIN courses c ON l.id_course = c.id
            WHERE c.id_users = '" . $_SESSION['id_user'] . "'";
        $testsResult = mysqli_query($conn, $testsQuery);
        if (!$lessonsResult) {
            die('Error: ' . mysqli_error($conn));
        }
        $tests = mysqli_fetch_assoc($testsResult)['total'];


 ?>
<section class="content" id="main-content">
            
               
</section>

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