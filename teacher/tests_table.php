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


 if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_test'])) {
    $id_test = (int)($_POST['test_id'] ?? 0);
    if ($id_test > 0) {
        // Vérifier que le test appartient bien à l'utilisateur
        $check_stmt = $conn->prepare("SELECT id FROM tests WHERE id = ? ");
        $check_stmt->bind_param("i", $id_test);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $delete_stmt = $conn->prepare("DELETE FROM tests WHERE id = ?");
            $delete_stmt->bind_param("i", $id_test);
            $delete_stmt->execute();
            $delete_stmt->close();
            $_SESSION['success_message'] = "Тест успешно удалён.";
        } else {
            $_SESSION['error_message'] = "Тест не найден";
        }
        $check_stmt->close();
    } else {
        $_SESSION['error_message'] = "Неверный ID теста.";
    }

  
}
 $testsResult=$handler->getTests();

?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />
  
  <link rel="stylesheet" href="./src/css/adminlte.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css" />

  <title>Список тестов</title>
</head>
<form id="deleteForm" method="post" style="display:none;">
  <input type="hidden" name="delete_test" value="1">
  <input type="hidden" id="delete_test_id" name="test_id" value="">
</form>
<body class="hold-transition sidebar-mini">
  <div class="wrapper">
    <?php include './header.php'; ?>
    <div class="content-wrapper">
    <?php include './nav_test.php'; ?>
      <section class="content pt-3">
        <div class="container-fluid">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Список тестов</h3>
            </div>
            <div class="card-body table-responsive">
              <?php if ($testsResult->num_rows > 0): ?>
              <table id="testsTable" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Название теста</th>
                    <th>Курс</th>
                    <th>Раздел</th>
                    <th>Действия</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = $testsResult->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['test_title']) ?></td>
                    <td><?= htmlspecialchars($row['course_title']) ?></td>
                    <td><?= htmlspecialchars($row['section_name']) ?></td>
                    <td>
                       <a href="test_management/edit_test.php?test_id=<?= $row['test_id'] ?>" class="btn btn-warning btn-sm" title="Редактировать">
                            <i class="fas fa-edit"></i>
                          </a>
                          <button type="button" class="btn btn-sm btn-danger delete-test" data-id="<?= $row['test_id'] ?>" title="Удалить">
                            <i class="fas fa-trash"></i>
                          </button>
                      
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
              <?php else: ?>
                <p>Тесты не найдены.</p>
              <?php endif; ?>
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
      $('#testsTable').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
          url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Russian.json"
        }
      });
      
    });

     $(document).on('click', '.delete-test', function(event) {
    event.preventDefault();
    if (confirm('Вы уверены, что хотите удалить этот тест?')) {
        var testId = $(this).data('id');
        $('#delete_test_id').val(testId);
        $('#deleteForm').submit();
    }
});

    
  </script>
</body>
</html>

