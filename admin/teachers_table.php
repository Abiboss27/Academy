<?php
require_once __DIR__ . '/../database.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список преподавателей</title>
    <link rel="stylesheet" href="./src/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
</head>
<body class="hold-transition sidebar-mini">

<div class="wrapper">
    <?php include './header.php'; ?>
    <div class="content-wrapper">
        <?php include './nav_teachers.php'; ?>

        <section class="content pt-3">
            <?php
            $query = "
                SELECT 
                    c.id AS id_course, 
                    u.id AS id_teacher, 
                    u.id_role, 
                    u.FullName AS teacher,  
                    c.title, 
                    CA.name AS category, 
                    s.id AS id_statut,
                    s.name AS statut
                FROM courses c
                JOIN categories CA ON c.id_category = CA.id
                JOIN users u ON c.id_users = u.id
                JOIN statuts s ON c.id_statut = s.id
                WHERE u.id_role = 3
                ORDER BY u.FullName ASC
            ";
            $result = mysqli_query($conn, $query);
            ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Список преподавателей</h3>
                </div>
                <div class="card-body">
                    <table id="teachersTable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Преподаватель</th>
                                <th>Категория курса</th>
                                <th>Название курса</th>
                                <th>Статус курса</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['teacher']) ?></td>
                                    <td><?= htmlspecialchars($row['category']) ?></td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars($row['statut']) ?></td>
                                    <td>
                                        <button 
                                            class="btn btn-sm btn-primary btnEditCourse" 
                                            data-id="<?= $row['id_course'] ?>" 
                                            data-teacher="<?= htmlspecialchars($row['teacher'], ENT_QUOTES) ?>" 
                                            data-category="<?= htmlspecialchars($row['category'], ENT_QUOTES) ?>" 
                                            data-title="<?= htmlspecialchars($row['title'], ENT_QUOTES) ?>" 
                                            data-idstatut="<?= $row['id_statut'] ?>">
                                            Редактировать
                                        </button>
                                    </td> 
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">Преподаватели не найдены</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>
</div>

<!-- Модальное окно редактирования курса -->
<div class="modal fade" id="editCourseModal" tabindex="-1" role="dialog" aria-labelledby="editCourseModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="editCourseForm">
      <input type="hidden" id="editCourseId" name="id_course">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editCourseModalLabel">Редактировать курс</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="editTeacher">Преподаватель</label>
            <input type="text" class="form-control" id="editTeacher" name="teacher" readonly>
          </div>
          <div class="form-group">
            <label for="editCategory">Категория курса</label>
            <input type="text" class="form-control" id="editCategory" name="category" readonly>
          </div>
          <div class="form-group">
            <label for="editTitle">Название курса</label>
            <input type="text" class="form-control" id="editTitle" name="title" required>
          </div>
          <div class="form-group">
            <label for="editStatut">Статус курса</label>
            <select class="form-control" id="editStatut" name="id_statut" required>
              <option value="1">Активен</option>
              <option value="2">Неактивен</option>
              <option value="3">Архив</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
          <button type="submit" class="btn btn-primary">Сохранить изменения</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Подключение JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="./src/js/bootstrap.bundle.min.js"></script>
<script src="./src/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#teachersTable').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Russian.json"
        }
    });

    // Открытие модального окна редактирования и заполнение полей
    $('.btnEditCourse').on('click', function() {
        const btn = $(this);
        $('#editCourseId').val(btn.data('id'));
        $('#editTeacher').val(btn.data('teacher'));
        $('#editCategory').val(btn.data('category'));
        $('#editTitle').val(btn.data('title'));
        $('#editStatut').val(btn.data('idstatut'));

        $('#editCourseModal').modal('show');
    });

    // Обработка отправки формы редактирования
    $('#editCourseForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: 'update_course.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Курс успешно обновлен');
                    location.reload();
                } else {
                    alert('Ошибка: ' + response.error);
                }
            },
            error: function() {
                alert('Ошибка при отправке запроса');
            }
        });
    });
});
</script>

</body>
</html>
