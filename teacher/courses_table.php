<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../database.php';

// Проверка авторизации
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Управление курсами</title>
  <link rel="stylesheet" href="./src/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include './header.php' ?>
  
    <div class="content-wrapper">
        <?php include './nav_courses.php' ?>
      
      <section class="content">
        <div class="container-fluid">
          <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
              <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
          <?php endif; ?>
          
          <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
              <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
          <?php endif; ?>
          
          <div class="card mt-3">
            <div class="card-header">
              <h3 class="card-title">Список курсов</h3>
              
            </div>
            <div class="card-body">
              <form id="deleteForm" method="post" style="display:none;">
                <input type="hidden" name="delete_course" value="1">
                <input type="hidden" id="delete_course_id" name="id_course" value="">
              </form>
              <table id="coursesTable" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Категория</th>
                    <th>Название</th>
                    <th>Статус</th>
                    <th>Уровень</th> 
                    <th>Цена (руб)</th>
                    <th>Скидка (%)</th>
                    <th>Видео</th>
                    <th>Действия</th> 
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course'])) {
                    $id_course = (int)$_POST['id_course'];
                    $id_user = $_SESSION['id_user'];

                    $check_stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND id_users = ?");
                    $check_stmt->bind_param("ii", $id_course, $id_user);
                    $check_stmt->execute();
                    $check_stmt->store_result();

                    if ($check_stmt->num_rows > 0) {
                        $delete_stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
                        $delete_stmt->bind_param("i", $id_course);
                        $delete_stmt->execute();
                        $delete_stmt->close();
                    } else {
                        $_SESSION['error_message'] = "Курс не найден или нет прав доступа";
                    }
                    $check_stmt->close();
                }

                $id_user = $_SESSION['id_user'];
                $stmt = $conn->prepare("SELECT c.id as id_course, c.title, c.language, c.price, c.discount, 
                                      c.video_url, c.date_added, c.last_modified, CA.name as category, 
                                      st.name as statut, l.name as level 
                                      FROM courses c 
                                      JOIN categories CA ON c.id_category = CA.id 
                                      JOIN statuts st ON c.id_statut = st.id 
                                      JOIN levels l ON c.id_level = l.id 
                                      WHERE c.id_users = ? 
                                      ORDER BY category ASC");

                $stmt->bind_param("i", $id_user);
                $stmt->execute();
                $result = $stmt->get_result();

                if (!$result) {
                    die("Query failed: " . mysqli_error($conn));
                }
                ?>

                  <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                      <tr>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['statut']) ?></td>
                        <td><?= htmlspecialchars($row['level']) ?></td>
                        <td><?= number_format(htmlspecialchars($row['price']), 2, '.', ' ') ?></td>
                        <td><?= htmlspecialchars($row['discount']) ?></td>
                        <td>
                          <?php if (!empty($row['video_url'])): ?>
                            <a href="<?= htmlspecialchars($row['video_url']) ?>" target="_blank" class="btn btn-sm btn-info">
                              <i class="fas fa-play"></i> Смотреть
                            </a>
                          <?php else: ?>
                            <span class="text-muted">Нет видео</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <a href="edit_cours.php?id=<?= $row['id_course'] ?>" class="btn btn-sm btn-primary" title="Редактировать">
                            <i class="fas fa-edit"></i>
                          </a>
                          <button type="button" class="btn btn-sm btn-danger delete-course" data-id="<?= $row['id_course'] ?>" title="Удалить">
                            <i class="fas fa-trash"></i>
                          </button>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="8" class="text-center">Нет доступных курсов</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </div>
</div>

<script src="./src/js/jquery.min.js"></script>
<script src="./src/js/bootstrap.bundle.min.js"></script>
<script src="./src/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
  $(document).ready(function() {
      // Инициализация DataTable
      $('#coursesTable').DataTable({
          "language": {
              "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"
          },
          "responsive": true,
          "autoWidth": false
      });

      // Обработка удаления курса
      $(document).on('click', '.delete-course', function(event) {
          event.preventDefault();
          if (confirm('Вы уверены, что хотите удалить этот курс?')) {
              var courseId = $(this).data('id');
              $('#delete_course_id').val(courseId);
              $('#deleteForm').submit();
          }
      });
  });
</script>
</body>
</html>