<section class="content" id="main-content">
    <div class="container-fluid">
        <?php
        require_once __DIR__ . '/../database.php';
        
        // Get students count
        $teachersQuery = "SELECT COUNT(*) as total FROM users WHERE id_role = 2";
        $teachersResult = mysqli_query($conn, $teachersQuery);
        $teachers = mysqli_fetch_assoc($teachersResult)['total'];
        ?>
        
        <div class="row">
            <!-- Статистика студентов -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="stats-card student-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0"><?php echo $teachers; ?></h2>
                                <p class="mb-0">Всего студентов</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                        <div class="progress mt-3">
                            <div class="progress-bar" style="width: 75%"></div>
                        </div>
                        <small class="text-muted">Активных: <?php echo round($teachers * 0.75); ?></small>
                    </div>
                </div>
            </div>

            <!-- Преобразование в преподавателя -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="action-card transform-card">
                    <div class="card-body">
                        <a href="change.php" class="action-link">
                            <div class="action-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="action-text">
                                <h4>Студент → Преподаватель</h4>
                                <p>Изменить роль пользователя</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<link rel="stylesheet" href="./src/css/nav_stud.css">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Анимация при наведении на карточку
        const cards = document.querySelectorAll('.stats-card, .action-card');
        
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>