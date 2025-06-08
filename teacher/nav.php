<section class="content" id="main-content">
    <div class="container-fluid">
        <!-- Add mode toggle button at the top -->
        <div class="row mb-4">
            <div class="col-12 text-right">
                <button id="theme-toggle" class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="fas fa-moon mr-1"></i> Dark Mode
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
      
        <div class="row">
            <!-- Course Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Мои Курсы</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalCourses; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-book fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lessons Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Мои лекции</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $lessons; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chalkboard-teacher fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Мои студенты</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $activeStudents; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tests Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Всего Тестов</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $tests; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-check fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modern CSS with animations -->
<link rel="stylesheet" href="src/css/style_nav.css">

<!-- Enhanced JavaScript for theme switching -->
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
            themeToggle.innerHTML = '<i class="fas fa-sun mr-1"></i> Light Mode';
            themeToggle.classList.remove('btn-outline-secondary');
            themeToggle.classList.add('btn-outline-light');
        }
        
        // Toggle theme on button click
        themeToggle.addEventListener('click', function() {
            const isDark = body.classList.toggle('dark-mode');
            
            if (isDark) {
                themeToggle.innerHTML = '<i class="fas fa-sun mr-1"></i> Light Mode';
                themeToggle.classList.remove('btn-outline-secondary');
                themeToggle.classList.add('btn-outline-light');
                localStorage.setItem('theme', 'dark');
            } else {
                themeToggle.innerHTML = '<i class="fas fa-moon mr-1"></i> Dark Mode';
                themeToggle.classList.remove('btn-outline-light');
                themeToggle.classList.add('btn-outline-secondary');
                localStorage.setItem('theme', 'light');
            }
        });
    });
</script>