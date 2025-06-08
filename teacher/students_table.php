<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/EnrollmentRequestHandler.php';

$userId = $_SESSION['id_user'] ?? null;
$handler = new EnrollmentRequestHandler($conn, $userId);

// Vérification de l'authentification
if (!$handler->isAuthenticated()) {
    header('Location: login.php');
    exit;
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_enrol'])) {
    $id_enrol = (int)$_POST['id_enrol'];
    if ($handler->deleteEnrolment($id_enrol)) {
        echo '<script>alert("Студент успешно удалён из курса");window.location.href=window.location.href;</script>';
        exit;
    } else {
        echo '<script>alert("Ошибка: нет прав на удаление или запись не найдена");</script>';
    }
}

// Récupération des inscriptions

// if (!$result) {
//     die("Error in enrollment query: " . mysqli_error($conn));
// }
?>


<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <link rel="stylesheet" href="./src/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">

  <title>Список студентов</title>
  <link rel="stylesheet" href="./src/css/student_table.css">
  
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include './header.php' ?>
    <div class="content-wrapper">
    <?php include './nav_students.php' ?>
      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card card-primary card-outline mt-3">
                <div class="card-header">
                  <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                      <i class="fas fa-user-graduate mr-2"></i>
                      Список студентов
                    </h3>
                    <div class="card-tools">
                      <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus text-white"></i>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="card-body pt-3 pb-0">
                  <form id="searchForm" method="get" class="mb-3">
                      <div class="input-group">
                          <input type="text" name="search" class="form-control" placeholder="Поиск студентов..." 
                                value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                          <div class="input-group-append">
                              <button type="submit" class="btn btn-primary">
                                  <i class="fas fa-search"></i>
                              </button>
                          </div>
                      </div>
                  </form>
              </div>
                <div class="card-body p-0">
                  <form id="deleteForm" method="post" style="display:none;">
                    <input type="hidden" name="delete_enrol" value="1">
                    <input type="hidden" id="delete_enrol_id" name="id_enrol" value="">
                  </form>
                  
                  <div class="table-responsive">
                    <table id="coursesTable" class="table">
                      <thead>
                        <tr>
                          <th><i class="fas fa-user mr-1"></i> Студент</th>
                          <th><i class="fas fa-book mr-1"></i> Курс</th>
                          <th><i class="fas fa-tag mr-1"></i> Категория</th>
                          <th><i class="fas fa-star mr-1"></i> Рейтинг</th>
                          <th><i class="fas fa-comment mr-1"></i> Отзыв</th>
                          <th><i class="fas fa-cog mr-1"></i> Действия</th> 
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                       $searchTerm = $_GET['search'] ?? '';
                      // Get enrolments with optional search
                      $result = $handler->getEnrolments($searchTerm);
                        ?>

                        <?php if(mysqli_num_rows($result) > 0): ?>
                          
                          <?php while($row = mysqli_fetch_assoc($result)): ?>
                          <tr>
                            <td data-label="Студент" class="font-weight-bold text-dark">
                              <?= htmlspecialchars($row['FullName']) ?>
                              <div class="text-muted small mt-1">
                                <i class="far fa-calendar-alt mr-1"></i> <?= date('d.m.Y', strtotime($row['date_added'])) ?>
                              </div>
                            </td>
                            <td data-label="Курс"><?= htmlspecialchars($row['title']) ?></td>
                            <td data-label="Категория">
                              <span class="badge badge-info"><?= htmlspecialchars($row['category']) ?></span>
                            </td>
                            <td data-label="Рейтинг">
                              <div class="rating-stars">
                                <?php
                                  $rating = (int)$row['rating'];
                                  for ($i = 1; $i <= 5; $i++):
                                ?>
                                  <i class="fas fa-star<?= $i <= $rating ? ' text-warning' : ' text-secondary' ?>"></i>
                                <?php endfor; ?>
                                <span class="ml-2"><?= $rating ?>/5</span>
                              </div>
                            </td>
                            <td data-label="Отзыв">
                              <?php if (!empty($row['comments'])): ?>
                                <button type="button" class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="<?= htmlspecialchars($row['comments']) ?>">
                                  <i class="fas fa-eye mr-1"></i> Показать
                                </button>
                              <?php else: ?>
                                <span class="text-muted small">Нет комментариев</span>
                              <?php endif; ?>
                            </td>
                            <td data-label="Действия">
                              <button type="button" class="btn btn-sm btn-danger delete-student" data-id="<?= $row['id_enrol'] ?>" data-toggle="tooltip" title="Удалить">
                                  <i class="fas fa-trash-alt"></i>
                              </button>
                            </td> 
                          </tr>
                          <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-user-slash"></i>
                                        <p class="mt-3">
                                            <?= empty($searchTerm) ? 'Нет зарегистрированных студентов' : 'Студенты не найдены' ?>
                                        </p>
                                        <?php if (!empty($searchTerm)): ?>
                                            <a href="?" class="btn btn-primary mt-2">Сбросить поиск</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <?php if(mysqli_num_rows($result) > 0): ?>
                <div class="card-footer clearfix">
                  <div class="float-right d-flex align-items-center">
                    <span class="text-muted mr-3">Всего студентов: <?= mysqli_num_rows($result) ?></span>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
</div>
<script src="./src/js/jquery.min.js"></script>
<script src="./src/js/bootstrap.bundle.min.js"></script>
<script src="./src/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    // Обработчик для кнопок удаления
    $(document).on('click', '.delete-student', function() {
        if (!confirm('Вы уверены, что хотите удалить этого студента из курса?')) {
            return;
        }
        var enrolId = $(this).data('id');
        $('#delete_enrol_id').val(enrolId);
        $('#deleteForm').submit();
    });

    // Инициализация тултипов
    $('[data-toggle="tooltip"]').tooltip({
      trigger: 'hover'
    });
    
    // Анимация при загрузке
    $('table tbody tr').each(function(i) {
      $(this).delay(i * 100).animate({
        opacity: 1
      }, 200);
    });
});
</script>
</body>
</html>