<section class="content" id="main-content">
    <div class="container-fluid">
        <!-- Theme toggle button -->
        <div class="row mb-4">
            <div class="col-12 text-right">
                <button id="theme-toggle" class="btn btn-sm btn-outline-secondary shadow-sm" type="button">
                    <i class="fas fa-moon"></i> <span class="toggle-text">Dark Mode</span>
                </button>
            </div>
        </div>
        
        <?php
        // Подключение к базе
        require_once __DIR__ . '/../database.php';

        // Функция для безопасного получения количества записей
        function getCount(mysqli $conn, string $query): int {
            $result = $conn->query($query);
            if ($result && $row = $result->fetch_assoc()) {
                return (int)$row['total'];
            }
            return 0;
        }

        $totalCourses = getCount($conn, "SELECT COUNT(*) as total FROM courses");
        $categories = getCount($conn, "SELECT COUNT(*) as total FROM categories");
        $teachers = getCount($conn, "SELECT COUNT(*) as total FROM users WHERE id_role = 3");
        $activeStudents = getCount($conn, "SELECT COUNT(*) as total FROM users WHERE id_role = 2");
        $lessons = getCount($conn, "SELECT COUNT(*) as total FROM lessons");
        ?>
        
        <div class="row">
            <!-- Courses Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card stat-card bg-gradient-info hover-scale shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0"><?= $totalCourses ?></h2>
                            <p class="mb-0 text-muted">Курсы</p>
                        </div>
                        <div class="icon-circle bg-white text-info">
                            <i class="fas fa-book-open fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card stat-card bg-gradient-success hover-scale shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0"><?= $categories ?></h2>
                            <p class="mb-0 text-muted">Категории</p>
                        </div>
                        <div class="icon-circle bg-white text-success">
                            <i class="fas fa-tags fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lessons Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card stat-card bg-gradient-primary hover-scale shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0"><?= $lessons ?></h2>
                            <p class="mb-0 text-muted">Лекции</p>
                        </div>
                        <div class="icon-circle bg-white text-primary">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teachers Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card stat-card bg-gradient-warning hover-scale shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0"><?= $teachers ?></h2>
                            <p class="mb-0 text-muted">Преподаватели</p>
                        </div>
                        <div class="icon-circle bg-white text-warning">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Students Card -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card stat-card bg-gradient-danger hover-scale shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0"><?= $activeStudents ?></h2>
                            <p class="mb-0 text-muted">Студенты</p>
                        </div>
                        <div class="icon-circle bg-white text-danger">
                            <i class="fas fa-user-graduate fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="src/css/nav.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;

    function updateToggleButton(isDark) {
        themeToggle.innerHTML = isDark
            ? '<i class="fas fa-sun"></i> <span class="toggle-text">Light Mode</span>'
            : '<i class="fas fa-moon"></i> <span class="toggle-text">Dark Mode</span>';
    }

    // Получаем сохранённую тему или системную
    const savedTheme = localStorage.getItem('theme') || 
                       (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
    } else {
        body.classList.remove('dark-mode');
    }
    updateToggleButton(savedTheme === 'dark');

    themeToggle.addEventListener('click', function() {
        const isDark = body.classList.toggle('dark-mode');
        updateToggleButton(isDark);
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });

    // Анимация карточек статистики
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 150 * index);
    });
});
</script>
