<section class="content" id="main-content">
    <div class="container-fluid">
        <?php
        require_once __DIR__ . '/../database.php';
        
        $categoriesQuery = "SELECT COUNT(*) as total FROM categories";
        $categoriesResult = mysqli_query($conn, $categoriesQuery);
        $categories = mysqli_fetch_assoc($categoriesResult)['total'];
        ?>
        
        <div class="row">
            <!-- Статистика категорий -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="category-card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0"><?php echo $categories; ?></h2>
                                <p class="mb-0">Всего категорий</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-tags"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small><i class="fas fa-arrow-up mr-1"></i> Последнее обновление</small>
                        </div>
                    </div>
                </div>
            </div>

          
        </div>
    </div>
</section>
<link rel="stylesheet" href="./src/css/nav_cat.css">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Добавляем анимацию при наведении на карточку статистики
        const statCard = document.querySelector('.stat-card');
        
        statCard.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.15)';
        });
        
        statCard.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--shadow)';
        });
        
    });
</script>