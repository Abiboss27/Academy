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

// Suppression d'une leçon (exemple pour delete.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_lesson_id'])) {
    $lessonId = (int)$_POST['delete_lesson_id'];
    if ($lessonId > 0) {
        $check_stmt = $conn->prepare("SELECT id FROM lessons WHERE id = ?");
        $check_stmt->bind_param("i", $lessonId);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows > 0) {
            $delete_stmt = $conn->prepare("DELETE FROM lessons WHERE id = ?");
            $delete_stmt->bind_param("i", $lessonId);
            $delete_stmt->execute();
            $delete_stmt->close();
            $_SESSION['success_message'] = "Материал успешно удалён.";
        } else {
            $_SESSION['error_message'] = "Материал не найден";
        }
        $check_stmt->close();
    } else {
        $_SESSION['error_message'] = "Неверный ID лекции.";
    }
    // После удаления или ошибки — редирект для обновления страницы и предотвращения повторной отправки формы
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  
  <link rel="stylesheet" href="./src/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">

  <title>Список лекций</title>
</head>
<body class="hold-transition sidebar-mini">
  <div class="wrapper">
    <?php include './header.php'; ?>
    <div class="content-wrapper">
    <?php include './nav_lessons.php'; ?>
      <section class="content">
        <div class="container-fluid">
          <div class="card mt-3">
            <div class="card-header">
              <h3 class="card-title">Список лекций</h3>
            </div>
            <div class="card-body">
              <?php if ($result->num_rows > 0): ?>
              <table id="lessonsTable" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Название лекции</th>
                    <th>Название курса</th>
                    <th>Секция</th>
                    <th>Файл</th>
                    <th>Видео Курса</th>
                    <th>Добавление</th>
                    <th>Модификация</th>
                    <th>Действия</th> 
                  </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $handler->getLessons();
                    ?>
                  <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($row['lesson']) ?></td>
                      <td><?= htmlspecialchars($row['course']) ?></td>
                      <td><?= htmlspecialchars($row['section']) ?></td>
                      <td>
                        <?php if (!empty($row['attachment'])): ?>
                          <?php
                            // Предполагается, что attachment — бинарные данные PDF
                            $base64 = base64_encode($row['attachment']);
                          ?>
                          <a href="data:application/pdf;base64,<?= $base64 ?>" download="attachment_<?= $row['id_lesson'] ?>.pdf">
                            Скачать
                          </a>
                        <?php else: ?>
                          Нет файла
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (!empty($row['video_url'])): ?>
                          <a href="<?= htmlspecialchars($row['video_url']) ?>" target="_blank" rel="noopener noreferrer">Смотреть</a>
                        <?php else: ?>
                          Нет видео
                        <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars($row['date_added']) ?></td> 
                      <td><?= htmlspecialchars($row['last_modified']) ?></td> 
                      <td>

                      <a href="Lessons/edit_lesson.php?id=<?= $row['id_lesson'] ?>" class="btn btn-warning btn-sm" title="Редактировать">
                            <i class="fas fa-edit"></i>
                          </a>
                          <button type="button" class="btn btn-sm btn-danger delete-lesson" data-id="<?= $row['id_lesson'] ?>" title="Удалить">
                            <i class="fas fa-trash"></i>
                          </button>

                        <!-- <a href="Lessons/edit_lesson.php?id=<?= $row['id_lesson'] ?>" class="btn btn-sm btn-primary">Редактировать</a>
                        <a href="delete.php?id=<?= $row['id_lesson'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Вы уверены, что хотите удалить эту лекцию?');">Удалить</a> -->
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
              <?php else: ?>
                <p>Лекции не найдены.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <form id="deleteForm" method="POST" style="display:none;">
  <input type="hidden" name="delete_lesson_id" id="delete_lesson_id" value="">
</form>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="./src/js/bootstrap.bundle.min.js"></script>
  <script src="./src/js/adminlte.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>

  <script>
  $(document).ready(function() {
      $('#lessonsTable').DataTable({
          responsive: true,
          autoWidth: false,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Russian.json"
          }
      });
  });

     $(document).on('click', '.delete-lesson', function(event) {
    event.preventDefault();
    if (confirm('Вы уверены, что хотите удалить эту лекцию?')) {
        var lessonId = $(this).data('id');
        $('#delete_lesson_id').val(lessonId);
        $('#deleteForm').submit();
    }
});
  </script>
</body>
</html>

