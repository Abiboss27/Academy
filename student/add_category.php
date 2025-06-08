<?php
require_once '../database.php';

// Обработка AJAX-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $response = handleRequest($pdo, $_POST);
        echo json_encode($response);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Основная функция обработки запросов
function handleRequest($pdo, $data) {
    if (!isset($data['action'])) {
        return ['success' => false, 'error' => 'Не указано действие'];
    }

    switch ($data['action']) {
        case 'add':
            return addCategory($pdo, $data);
        case 'edit':
            return editCategory($pdo, $data);
        case 'delete':
            return deleteCategory($pdo, $data);
        case 'get_categories':
            return ['success' => true, 'categories' => getCategories($pdo)];
        default:
            return ['success' => false, 'error' => 'Неизвестное действие'];
    }
}

// Функция добавления категории
function addCategory($pdo, $data) {
    $stmt = $pdo->prepare("INSERT INTO category (name, date_added, last_modified) 
                          VALUES (:name, NOW(), NOW())");
    
    $stmt->execute([
        ':name' => htmlspecialchars($data['name'])
    ]);
    
    return ['success' => true, 'id' => $pdo->lastInsertId()];
}

// Функция редактирования категории
function editCategory($pdo, $data) {
    if (!isset($data['id'])) {
        return ['success' => false, 'error' => 'Не указан ID категории'];
    }

    $stmt = $pdo->prepare("UPDATE category SET 
                          name = :name, 
                          last_modified = NOW()
                          WHERE id = :id");
    
    $stmt->execute([
        ':id' => (int)$data['id'],
        ':name' => htmlspecialchars($data['name'])
    ]);
    
    return ['success' => true];
}

// Функция удаления категории
function deleteCategory($pdo, $data) {
    if (!isset($data['id'])) {
        return ['success' => false, 'error' => 'Не указан ID категории'];
    }

    $stmt = $pdo->prepare("DELETE FROM category WHERE id = ?");
    $stmt->execute([(int)$data['id']]);
    
    return ['success' => true];
}

// Функция получения категорий
function getCategories($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM category ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Получаем категории для первоначальной загрузки страницы
$categories = getCategories($pdo);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление категориями</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="src/css/style.css">
</head>
<body>
    <div class="container mt-4 animate__animated animate__fadeIn">
        <h1 class="mb-4"><i class="bi bi-diagram-3"></i> Управление категориями</h1>
        
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-lg"></i> Добавить категорию
        </button>
        
        <div id="categoriesList">
            <?php if (empty($categories)): ?>
                <div class="no-categories">
                    <i class="bi bi-folder-x" style="font-size: 3rem; opacity: 0.5;"></i>
                    <h4 class="mt-3">Нет категорий</h4>
                    <p>Начните с добавления первой категории</p>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <div class="category-item" data-id="<?= $category['id'] ?>">
                        <span class="category-name"><?= htmlspecialchars($category['name']) ?></span>
                        <div class="actions">
                            <button class="btn btn-sm btn-outline-primary me-1 edit-btn" 
                                    data-id="<?= $category['id'] ?>"
                                    data-name="<?= htmlspecialchars($category['name']) ?>"
                               title="Редактировать">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-btn" 
                                    data-id="<?= $category['id'] ?>"
                                    data-name="<?= htmlspecialchars($category['name']) ?>"
                                    title="Удалить">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Модальное окно добавления категории -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-folder-plus"></i> Добавить категорию</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCategoryForm">
                        <div class="mb-3">
                            <label for="addName" class="form-label">Название *</label>
                            <input type="text" class="form-control" id="addName" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" id="saveAddCategory">
                        <i class="bi bi-save"></i> Сохранить
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Модальное окно редактирования категории -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Редактировать категорию</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editCategoryForm">
                        <input type="hidden" id="editId" name="id">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Название *</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" id="saveEditCategory">
                        <i class="bi bi-save"></i> Сохранить
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Модальное окно подтверждения удаления -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Подтверждение удаления</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить эту категорию?</p>
                    <p id="deleteCategoryName" class="fw-bold text-danger"></p>
                    <p class="text-muted"><small>Это действие нельзя отменить.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <i class="bi bi-trash"></i> Удалить
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Обновление списка категорий
        function refreshCategories() {
            $.post('categories.php', {action: 'get_categories'}, function(response) {
                if (response.success) {
                    let html = '';
                    if (response.categories.length === 0) {
                        html = `
                            <div class="no-categories">
                                <i class="bi bi-folder-x" style="font-size: 3rem; opacity: 0.5;"></i>
                                <h4 class="mt-3">Нет категорий</h4>
                                <p>Начните с добавления первой категории</p>
                            </div>
                        `;
                    } else {
                        response.categories.forEach(category => {
                            html += `
                                <div class="category-item animate__animated animate__fadeIn" data-id="${category.id}">
                                    <span class="category-name">${category.name}</span>
                                    <div class="actions">
                                        <button class="btn btn-sm btn-outline-primary me-1 edit-btn" 
                                                data-id="${category.id}"
                                                data-name="${category.name.replace(/"/g, '&quot;')}"
                                                title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-btn" 
                                                data-id="${category.id}"
                                                data-name="${category.name.replace(/"/g, '&quot;')}"
                                                title="Удалить">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                    }
                    $('#categoriesList').html(html);
                } else {
                    showError(response.error);
                }
            }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                showError('Ошибка загрузки категорий: ' + textStatus);
            });
        }
        
        // Показ ошибки
        function showError(message) {
            const errorHtml = `
                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                    <i class="bi bi-exclamation-octagon me-2"></i>
                    <strong>Ошибка:</strong> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            $('#categoriesList').prepend(errorHtml);
            
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
        
        // Добавление категории
        $('#saveAddCategory').click(function() {
            const form = $('#addCategoryForm');
            if (form[0].checkValidity()) {
                const formData = form.serialize() + '&action=add';
                
                $.post('categories.php', formData, function(response) {
                    if (response.success) {
                        $('#addCategoryModal').modal('hide');
                        form[0].reset();
                        
                        const successHtml = `
                            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                Категория успешно добавлена
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        $('#categoriesList').prepend(successHtml);
                        
                        refreshCategories();
                        
                        setTimeout(() => {
                            $('.alert-success').alert('close');
                        }, 3000);
                    } else {
                        showError(response.error);
                    }
                }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                    showError('Ошибка сервера: ' + textStatus);
                });
            } else {
                form[0].reportValidity();
            }
        });
        
        // Редактирование категории
        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            $('#editId').val(id);
            $('#editName').val(name);
            
            $('#editCategoryModal').modal('show');
        });
        
        // Сохранение изменений
        $('#saveEditCategory').click(function() {
            const form = $('#editCategoryForm');
            if (form[0].checkValidity()) {
                const formData = form.serialize() + '&action=edit';
                
                $.post('categories.php', formData, function(response) {
                    if (response.success) {
                        $('#editCategoryModal').modal('hide');
                        
                        const successHtml = `
                            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                Категория успешно обновлена
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        $('#categoriesList').prepend(successHtml);
                        
                        refreshCategories();
                        
                        setTimeout(() => {
                            $('.alert-success').alert('close');
                        }, 3000);
                    } else {
                        showError(response.error);
                    }
                }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                    showError('Ошибка сервера: ' + textStatus);
                });
            } else {
                form[0].reportValidity();
            }
        });
        
        // Удаление категории
        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            $('#deleteCategoryName').text(name);
            $('#deleteCategoryModal').data('id', id);
            $('#deleteCategoryModal').modal('show');
        });
        
        // Подтверждение удаления
        $('#confirmDelete').click(function() {
            const id = $('#deleteCategoryModal').data('id');
            
            $.post('categories.php', {id: id, action: 'delete'}, function(response) {
                if (response.success) {
                    $('#deleteCategoryModal').modal('hide');
                    
                    const successHtml = `
                        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            Категория успешно удалена
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('#categoriesList').prepend(successHtml);
                    
                    refreshCategories();
                    
                    setTimeout(() => {
                        $('.alert-success').alert('close');
                    }, 3000);
                } else {
                    showError(response.error);
                }
            }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                showError('Ошибка сервера: ' + textStatus);
            });
        });
    });
    </script>
</body>
</html>