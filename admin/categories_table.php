<?php
require_once __DIR__ . '/../database.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление категориями | Админ-панель</title>
    <link rel="stylesheet" href="./src/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="./src/css/cat_tab.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">
    <?php include './header.php'; ?>
    <div class="content-wrapper">
        <?php include './nav_categories.php'; ?>
        <section class="content pt-3">
            <?php
                $query = "SELECT id as id_categorie, name, date_added FROM categories ORDER BY name ASC;";
                $result = mysqli_query($conn, $query);
            ?>
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-tags mr-2"></i>Управление категориями</h3>
                        <button class="btn btn-success" data-toggle="modal" data-target="#modalAddCategory">
                            <i class="fas fa-plus-circle"></i> Добавить категорию
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="categoriesTable" class="table table-bordered table-hover w-100">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Название категории</th>
                                        <th>Дата добавления</th>
                                        <th width="15%">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['name']) ?></td>
                                                <td><?= date('d.m.Y H:i', strtotime($row['date_added'])) ?></td>
                                                <td class="action-buttons">
                                                    <button class="btn btn-sm btn-primary btnEditCategory" 
                                                            data-id="<?= $row['id_categorie'] ?>" 
                                                            data-name="<?= htmlspecialchars($row['name']) ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger btnDeleteCategory" 
                                                            data-id="<?= $row['id_categorie'] ?>" 
                                                            data-name="<?= htmlspecialchars($row['name']) ?>">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="empty-table-message">
                                                <i class="fas fa-info-circle fa-2x mb-3" style="color: #dddfeb;"></i>
                                                <p>Нет доступных категорий</p>
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
    </div>
</div>

<!-- Модальное окно добавления категории -->
<div class="modal fade" id="modalAddCategory" tabindex="-1" role="dialog" aria-labelledby="modalAddCategoryLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form id="formAddCategory">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Добавить категорию</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="addCategoryName">Название категории</label>
            <input type="text" class="form-control" id="addCategoryName" name="name" placeholder="Введите название категории" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Отмена</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-check mr-1"></i> Добавить</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Модальное окно редактирования категории -->
<div class="modal fade" id="modalEditCategory" tabindex="-1" role="dialog" aria-labelledby="modalEditCategoryLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form id="formEditCategory">
      <input type="hidden" id="editCategoryId" name="id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Редактировать категорию</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="editCategoryName">Название категории</label>
            <input type="text" class="form-control" id="editCategoryName" name="name" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Отмена</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Сохранить</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Модальное окно удаления категории -->
<div class="modal fade" id="modalDeleteCategory" tabindex="-1" role="dialog" aria-labelledby="modalDeleteCategoryLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <form id="formDeleteCategory">
      <input type="hidden" id="deleteCategoryId" name="id">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Подтверждение удаления</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Закрыть">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Вы действительно хотите удалить категорию <strong id="deleteCategoryName" class="text-danger"></strong>?</p>
          <p class="text-muted">Это действие нельзя будет отменить.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Отмена</button>
          <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt mr-1"></i> Удалить</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="./src/js/jquery.min.js"></script>
<script src="./src/js/bootstrap.bundle.min.js"></script>
<script src="./src/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        $('#categoriesTable').DataTable({
            responsive: true,
            autoWidth: false,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Russian.json"
            },
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });
        
        // Инициализация модальных окон
        $('.btnEditCategory').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#editCategoryId').val(id);
            $('#editCategoryName').val(name);
            $('#modalEditCategory').modal('show');
        });

        $('.btnDeleteCategory').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#deleteCategoryId').val(id);
            $('#deleteCategoryName').text(name);
            $('#modalDeleteCategory').modal('show');
        });

        // Обработка форм
        $('#formAddCategory').submit(function(e) {
            e.preventDefault();
            const name = $('#addCategoryName').val().trim();
            if (!name) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Ошибка',
                    text: 'Введите название категории!',
                    confirmButtonColor: '#4e73df'
                });
                return;
            }
            
            $.post('categories_action.php', {action: 'add', name: name}, function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Успешно!',
                        text: 'Категория успешно добавлена',
                        confirmButtonColor: '#1cc88a',
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ошибка',
                        text: response.error || 'Произошла ошибка при добавлении',
                        confirmButtonColor: '#e74a3b'
                    });
                }
            }, 'json').fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Ошибка',
                    text: 'Произошла ошибка при отправке запроса',
                    confirmButtonColor: '#e74a3b'
                });
            });
        });

        $('#formEditCategory').submit(function(e) {
            e.preventDefault();
            const id = $('#editCategoryId').val();
            const name = $('#editCategoryName').val().trim();
            if (!name) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Ошибка',
                    text: 'Введите название категории!',
                    confirmButtonColor: '#4e73df'
                });
                return;
            }
            
            $.post('categories_action.php', {action: 'edit', id: id, name: name}, function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Успешно!',
                        text: 'Категория успешно обновлена',
                        confirmButtonColor: '#1cc88a',
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ошибка',
                        text: response.error || 'Произошла ошибка при обновлении',
                        confirmButtonColor: '#e74a3b'
                    });
                }
            }, 'json').fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Ошибка',
                    text: 'Произошла ошибка при отправке запроса',
                    confirmButtonColor: '#e74a3b'
                });
            });
        });

        $('#formDeleteCategory').submit(function(e) {
            e.preventDefault();
            const id = $('#deleteCategoryId').val();
            
            Swal.fire({
                title: 'Вы уверены?',
                text: "Категория будет удалена безвозвратно!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Да, удалить!',
                cancelButtonText: 'Отмена'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('categories_action.php', {action: 'delete', id: id}, function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Удалено!',
                                text: 'Категория успешно удалена',
                                confirmButtonColor: '#1cc88a',
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ошибка',
                                text: response.error || 'Произошла ошибка при удалении',
                                confirmButtonColor: '#e74a3b'
                            });
                        }
                    }, 'json').fail(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ошибка',
                            text: 'Произошла ошибка при отправке запроса',
                            confirmButtonColor: '#e74a3b'
                        });
                    });
                }
            });
        });
    });
</script>
</body>
</html>