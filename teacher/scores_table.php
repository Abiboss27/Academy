<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/EnrollmentRequestHandler.php';

// Проверка авторизации
if (!isset($_SESSION['id_user'])) {
    header('Location: ../login.html');
    exit();
}

$userId = (int)$_SESSION['id_user'];
$handler = new EnrollmentRequestHandler($conn, $userId);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Список студентов | Обучение</title>
  <link rel="stylesheet" href="./src/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="./src/css/score_table.css">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed student-table">
  <div class="wrapper">
    <?php include './header.php'; ?>
    <div class="content-wrapper">
      <?php include './nav_score.php'; ?>
      <section class="content">
        <div class="container-fluid">
          <div class="card shadow-sm">
            <div class="card-header">
              <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
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
            <div class="card-body">
          <div class="table-responsive">
            <table id="coursesTable" class="table">
                      <thead>
                        <tr>
                          <th><i class="fas fa-user mr-1"></i> Студент</th>
                          <th><i class="fas fa-book mr-1"></i> Курс</th>
                          <th><i class="fas fa-tag mr-1"></i> Категория</th>
                          <th><i class="fas fa-chart-line mr-1"></i> Прогресс</th>
                          
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
                            <td><?= htmlspecialchars($row['FullName']) ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($row['category']) ?></span></td>
                            <td>
                              <?php
                                  $courseId = $row['id_course'];
                                  $userIdEnrolled = $row['id_user'];
                                  
                                  if ($courseId && $userIdEnrolled) {
                                      $progressList = $handler->getAllUsersProgressByCourse($courseId);
                                      $averageProgress = $progressList[$userIdEnrolled] ?? 0;
                                  } else {
                                      $averageProgress = 0;
                                  }
                                ?>
                              <div class="d-flex align-items-center">
                                  <div class="progress-container mr-2">
                                      <div class="progress-bar" style="width: <?= $averageProgress ?>%"></div>
                                  </div>
                                  <small><?= $averageProgress ?>%</small>
                              </div>
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
          </div>
        </div>
      </section>
      <section class="content mt-4">
  <div class="container-fluid">
    <div class="card shadow-sm">
      <div class="card-header">
        <h3 class="card-title mb-0">
          <i class="fas fa-list mr-2"></i>
          Пройденные тесты студентами
        </h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="testsTable" class="table table-striped">
            <thead>
              <tr>
                <th>Студент</th>
                <th>Курс</th>
                <th>Раздел</th>
                <th>Тест</th>
                <th>Оценка</th>
                <th>Попытка</th>
                <th>Дата последной попытки</th>
              </tr>
            </thead>
            <tbody>
              <?php  $testsData = $handler->getTestsDataByTeacher($userId)?>
              <?php foreach ($testsData as $test): ?>
              <tr>
                <td><?= htmlspecialchars($test['student']) ?></td>
                <td><?= htmlspecialchars($test['course']) ?></td>
                <td><?= htmlspecialchars($test['section']) ?></td>
                <td><?= htmlspecialchars($test['test']) ?></td>
                <td><?= htmlspecialchars($test['score']) ?></td>
                <td><?= htmlspecialchars($test['attempt_count']) ?></td>
                <td><?= htmlspecialchars($test['attempt_date']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="./src/js/bootstrap.bundle.min.js"></script>
  <script src="./src/js/adminlte.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#studentsTable').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json"
        },
        dom: '<"top"<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>>rt<"bottom"<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>>',
        initComplete: function() {
          $('.dataTables_filter input').addClass('form-control form-control-sm');
          $('.dataTables_length select').addClass('form-control form-control-sm');
        }
      });
    });

    $(document).ready(function() {
    $('#testsTable').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
        url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json"
        }
    });
    });

  </script>
</body>
</html>
