<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}  // Start the session

// Check if the session user ID is set
if (!isset($_SESSION['id_user'])) {
    die("Session user ID not set. Please log in.");
}

require_once __DIR__ . '/../database.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Дашборд курсов</title>
    <link rel="stylesheet" href="src/css/style_nav_cours.css">
    <link rel="stylesheet" href="src/css/add_section.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>
<body>
<section class="dashboard-section">
    <div class="dashboard-container">

        <?php
        // Вывод сообщения об успешном добавлении раздела
        if (isset($_GET['msg']) && $_GET['msg'] === 'section_added') {
            echo '<div class="alert alert-success">Раздел успешно добавлен!</div>';
        }

        // Get total courses
        $totalCoursesQuery = "SELECT COUNT(*) as total FROM courses WHERE id_users = '" . $_SESSION['id_user'] . "'";
        $totalCoursesResult = mysqli_query($conn, $totalCoursesQuery);
        $totalCourses = mysqli_fetch_assoc($totalCoursesResult)['total'];

        // Get active courses (id_statut = 1)
        $activeCoursesQuery = "SELECT COUNT(*) as total FROM courses WHERE id_statut = 1 AND id_users = '" . $_SESSION['id_user'] . "'";
        $activeCoursesResult = mysqli_query($conn, $activeCoursesQuery);
        $activeCourses = mysqli_fetch_assoc($activeCoursesResult)['total'];

        // Get inactive courses (id_statut = 2)
        $notactiveCoursesQuery = "SELECT COUNT(*) as total FROM courses WHERE id_statut = 2 AND id_users = '" . $_SESSION['id_user'] . "'";
        $notactiveCoursesResult = mysqli_query($conn, $notactiveCoursesQuery);
        $notactiveCourses = mysqli_fetch_assoc($notactiveCoursesResult)['total'];
        ?>

        <div class="metrics-grid">
            <!-- Всего курсов -->
            <div class="metric-card metric-card-primary">
                <div class="metric-content">
                    <div class="metric-value"><?php echo $totalCourses; ?></div>
                    <div class="metric-label">Всего Курсов</div>
                </div>
                <div class="metric-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="metric-wave"></div>
            </div>

            <!-- Активные курсы -->
            <div class="metric-card metric-card-success">
                <div class="metric-content">
                    <div class="metric-value"><?php echo $activeCourses; ?></div>
                    <div class="metric-label">Активные курсы</div>
                </div>
                <div class="metric-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="metric-wave"></div>
            </div>

            <!-- Неактивные курсы -->
            <div class="metric-card metric-card-warning">
                <div class="metric-content">
                    <div class="metric-value"><?php echo $notactiveCourses; ?></div>
                    <div class="metric-label">Неактивные курсы</div>
                </div>
                <div class="metric-icon">
                    <i class="fas fa-pause-circle"></i>
                </div>
                <div class="metric-wave"></div>
            </div>

            <!-- Добавить курс -->
            <div class="metric-card metric-card-action">
                <a href="add_cours.php" class="add-course-link">
                    <div class="metric-content">
                        <div class="metric-value">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="metric-label">Добавить курс</div>
                    </div>
                </a>
                <div class="metric-wave"></div>
            </div>

            <!-- Добавить раздел к курсу -->
            <div class="metric-card metric-card-action">
                <button id="add-section-btn" class="add-section-btn">
                    <div class="metric-content">
                        <div class="metric-value">
                            <i class="fas fa-folder-plus"></i>
                        </div>
                        <div class="metric-label">Добавить раздел к курсу</div>
                    </div>
                </button>
                <div class="metric-wave"></div>
            </div>
        </div>
    </div>
</section>

<!-- Модальное окно для добавления раздела -->
<div id="add-section-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-modal" id="close-modal">&times;</span>
        <h2>Добавить раздел к курсу</h2>
        <form id="add-section-form" method="POST" action="add_section.php">
            <label for="course-select">Выберите курс:</label>
            <select name="id_course" id="course-select" required>
                <option value="">-- Выберите курс --</option>
                <?php
                // Получаем список курсов пользователя для выпадающего списка
                $coursesQuery = "SELECT id, title FROM courses WHERE id_users = '" . $_SESSION['id_user'] . "'";
                $coursesResult = mysqli_query($conn, $coursesQuery);
                while ($course = mysqli_fetch_assoc($coursesResult)) {
                    echo '<option value="' . htmlspecialchars($course['id']) . '">' . htmlspecialchars($course['title']) . '</option>';
                }
                ?>
            </select>

            <label for="section-title">Название раздела:</label>
            <input type="text" id="section-title" name="title" required maxlength="255">

            <label for="section-description">Описание раздела:</label>
            <textarea id="section-description" name="description" rows="4"></textarea>

            <button type="submit" class="btn-submit">Добавить раздел</button>
        </form>
    </div>
</div>

<script>
// Dark mode toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.createElement('button');
    darkModeToggle.id = 'dark-mode-toggle';
    darkModeToggle.className = 'dark-mode-toggle';
    darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';

    document.querySelector('.dashboard-container').prepend(darkModeToggle);

    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const icon = this.querySelector('i');

        if (document.body.classList.contains('dark-mode')) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            localStorage.setItem('darkMode', 'enabled');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
            localStorage.setItem('darkMode', 'disabled');
        }
    });

    // Check for saved preference
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
        const icon = darkModeToggle.querySelector('i');
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    }
});

// Counter animation
document.addEventListener('DOMContentLoaded', function() {
    const metricValues = document.querySelectorAll('.metric-value:not(.metric-card-action .metric-value)');

    metricValues.forEach(value => {
        const target = +value.innerText;
        const duration = 1500;
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                clearInterval(timer);
                current = target;
            }
            value.innerText = Math.floor(current);
        }, 16);
    });
});

// Модальное окно для добавления раздела
document.addEventListener('DOMContentLoaded', function() {
    const addSectionBtn = document.getElementById('add-section-btn');
    const modal = document.getElementById('add-section-modal');
    const closeModal = document.getElementById('close-modal');

    addSectionBtn.addEventListener('click', () => {
        modal.style.display = 'flex';
    });

    closeModal.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Закрытие модального окна при клике вне контента
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
