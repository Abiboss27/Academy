<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} // Start the session

// Check if the session user ID is set
if (!isset($_SESSION['id_user'])) {
    die("Session user ID not set. Please log in.");
}

require_once __DIR__ . '/../database.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель преподавателя</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./src/css/nav_test.css">
</head>
<body>
   

    <section class="content" id="main-content">
        <div class="container">
            <div class="row">
                <?php
                // Query to count the total number of tests created by the logged-in teacher
                $testsCountQuery = "
                    SELECT COUNT(*) AS total_tests
                    FROM tests t
                    JOIN courses c ON t.id_course = c.id
                    WHERE c.id_users = '" . $_SESSION['id_user'] . "'";

                $testsCountResult = mysqli_query($conn, $testsCountQuery);
                
                if (!$testsCountResult) {
                    die('Error: ' . mysqli_error($conn));
                }

                $testsCount = mysqli_fetch_assoc($testsCountResult)['total_tests'];
                ?>

                <div class="col-md-4">
                    <div class="small-box bg-info">
                        <div class="inner p-4">
                            <h3 class="mb-3"><?php echo $testsCount; ?></h3>
                            <p class="fs-5">Всего тестов</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <a href="./tests_table.php" class="small-box-footer d-block p-2 text-center text-white">
                            Просмотреть все <i class="fas fa-arrow-circle-right ms-1"></i>
                        </a>
                    </div>
                </div>

            <!-- Блок быстрых действий -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Быстрые действия</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-3">
                                <a href="test_management/index.php" class="btn btn-add-test">
                                    <i class="fas fa-plus-circle me-2"></i>Создать тест
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
