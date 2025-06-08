<section class="content" id="main-content">
    <div class="container-fluid">
        <?php
        // Include database connection
        require_once __DIR__ . '/../database.php';
        
          
        // Get categories 
        $categoriesQuery = "SELECT COUNT(*) as total FROM categories ";
        $categoriesResult = mysqli_query($conn, $categoriesQuery);
        $categories = mysqli_fetch_assoc($categoriesResult)['total'];
        ?>
        
        <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo $categories; ?></h3>
                        <p>Всего Категории </p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-light">
                    <div class="inner">
                        <a href="#"> Добавить категори </a>
                    </div> 
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                </div>
            </div>

           

        </div>
    </div>
</section>