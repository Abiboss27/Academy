<section class="content" id="main-content">
    <div class="container-fluid">
        <?php
        require_once __DIR__ . '/../database.php';
        
        // Database queries
        $totalCoursesQuery = "SELECT COUNT(*) as total FROM courses";
        $activeCoursesQuery = "SELECT COUNT(*) as total FROM courses WHERE id_statut = 1";
        $notactiveCoursesQuery = "SELECT COUNT(*) as total FROM courses WHERE id_statut = 2";
        
        $totalCourses = mysqli_fetch_assoc(mysqli_query($conn, $totalCoursesQuery))['total'];
        $activeCourses = mysqli_fetch_assoc(mysqli_query($conn, $activeCoursesQuery))['total'];
        $notactiveCourses = mysqli_fetch_assoc(mysqli_query($conn, $notactiveCoursesQuery))['total'];
        ?>
        
        <div class="row stats-row">
            <!-- Total Courses Card -->
            <div class="col-xl-4 col-md-4 mb-4">
                <div class="stats-card total-courses">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1"><?= $totalCourses ?></h2>
                                <p class="mb-0">Всего курсов</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-book-open"></i>
                            </div>
                        </div>
                        <div class="progress mt-3">
                            <div class="progress-bar" style="width: <?= ($totalCourses ? ($activeCourses/$totalCourses)*100 : 0 )?>%"></div>
                        </div>
                        <small class="stats-note"><?= round(($totalCourses ? ($activeCourses/$totalCourses)*100 : 0)) ?>% активных</small>
                    </div>
                </div>
            </div>

            <!-- Active Courses Card -->
            <div class="col-xl-4 col-md-4 mb-4">
                <div class="stats-card active-courses">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1"><?= $activeCourses ?></h2>
                                <p class="mb-0">Активные курсы</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                       
                    </div>
                </div>
            </div>

            <!-- Inactive Courses Card -->
            <div class="col-xl-4 col-md-4 mb-4">
                <div class="stats-card inactive-courses">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1"><?= $notactiveCourses ?></h2>
                                <p class="mb-0">Неактивные курсы</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-pause-circle"></i>
                            </div>
                        </div>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="./src/css/style_cours.css">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add animation to cards on page load
        const cards = document.querySelectorAll('.stats-card');
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