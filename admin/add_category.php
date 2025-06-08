<?php
 require_once __DIR__ . '/../database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            echo json_encode(['success' => false, 'error' => 'Название не заполнено']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO categories (name, date_added, last_modified) VALUES (?, CURDATE(), UNIX_TIMESTAMP())");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        exit;
    }

    if ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id <= 0 || $name === '') {
            echo json_encode(['success' => false, 'error' => 'Данные не заполнены']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE categories SET name=?, last_modified=UNIX_TIMESTAMP() WHERE id=?");
        $stmt->bind_param("si", $name, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Некорректный ID']);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        exit;
    }

    if ($action === 'get_categories') {
        $result = $conn->query("SELECT * FROM categories ORDER BY name");
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        echo json_encode(['success' => true, 'categories' => $categories]);
        exit;
    }
}

// Если запрос не POST или не указан action
echo json_encode(['success' => false, 'error' => 'Некорректный запрос']);
exit;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление категориями</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1>Управление категориями</h1>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Добавить категорию</button>
    <div id="categoriesList"></div>
</div>

<!-- Модальное окно добавления -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Добавить категорию</h5></div>
      <div class="modal-body">
        <input type="text" id="addName" class="form-control" placeholder="Название категории">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
        <button type="button" class="btn btn-primary" id="saveAddCategory">Сохранить</button>
      </div>
    </div>
  </div>
</div>

<!-- Модальное окно редактирования -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Редактировать категорию</h5></div>
      <div class="modal-body">
        <input type="hidden" id="editId">
        <input type="text" id="editName" class="form-control" placeholder="Название категории">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
        <button type="button" class="btn btn-primary" id="saveEditCategory">Сохранить</button>
      </div>
    </div>
  </div>
</div>

<!-- Модальное окно удаления -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Удалить категорию</h5></div>
      <div class="modal-body">
        <input type="hidden" id="deleteId">
        <p id="deleteCategoryName"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Удалить</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function refreshCategories() {
    $.post('categories.php', {action: 'get_categories'}, function(response) {
        if (response.success) {
            let html = '';
            if (response.categories.length === 0) {
                html = '<div class="alert alert-info">Нет категорий</div>';
            } else {
                response.categories.forEach(cat => {
                    html += `<div class="mb-2 p-2 border rounded d-flex justify-content-between align-items-center">
                        <span>${cat.name}</span>
                        <span>
                            <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${cat.id}" data-name="${cat.name}">Редактировать</button>
                            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${cat.id}" data-name="${cat.name}">Удалить</button>
                        </span>
                    </div>`;
                });
            }
            $('#categoriesList').html(html);
        } else {
            $('#categoriesList').html('<div class="alert alert-danger">' + response.error + '</div>');
        }
    }, 'json');
}
refreshCategories();

$('#saveAddCategory').click(function() {
    let name = $('#addName').val().trim();
    if (!name) {
        alert('Введите название!');
        return;
    }
    $.post('categories.php', {action: 'add', name: name}, function(response) {
        if (response.success) {
            $('#addCategoryModal').modal('hide');
            $('#addName').val('');
            refreshCategories();
        } else {
            alert(response.error);
        }
    }, 'json');
});

$(document).on('click', '.edit-btn', function() {
    $('#editId').val($(this).data('id'));
    $('#editName').val($(this).data('name'));
    $('#editCategoryModal').modal('show');
});

$('#saveEditCategory').click(function() {
    let id = $('#editId').val();
    let name = $('#editName').val().trim();
    if (!name) {
        alert('Введите название!');
        return;
    }
    $.post('categories.php', {action: 'edit', id: id, name: name}, function(response) {
        if (response.success) {
            $('#editCategoryModal').modal('hide');
            refreshCategories();
        } else {
            alert(response.error);
        }
    }, 'json');
});

$(document).on('click', '.delete-btn', function() {
    $('#deleteId').val($(this).data('id'));
    $('#deleteCategoryName').text($(this).data('name'));
    $('#deleteCategoryModal').modal('show');
});

$('#confirmDelete').click(function() {
    let id = $('#deleteId').val();
    $.post('categories.php', {action: 'delete', id: id}, function(response) {
        if (response.success) {
            $('#deleteCategoryModal').modal('hide');
            refreshCategories();
        } else {
            alert(response.error);
        }
    }, 'json');
});
</script>
</body>
</html>
