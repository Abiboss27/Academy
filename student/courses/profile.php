<?php
include 'C:/xampp/htdocs/Academy/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверка авторизации
if (!isset($_SESSION['id_user'])) {
    header("Location: /login.php");
    exit();
}

// Получение данных пользователя
$userId = $_SESSION['id_user'];
$user = [];
$roles = [2 => 'Студент', 3 => 'Преподаватель', 1 => 'Администратор'];
$statuses = [1 => 'Активен', 2 => 'Заблокирован', 3 => 'На проверке'];

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        throw new Exception("Пользователь не найден");
    }
} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="/Academy/student/assets/css/profil.css" rel="stylesheet">

</head>
<body class="hold-transition sidebar-mini">

<div class="wrapper">
    <?php include '../header.php'; ?>

    <section class="content">
        <div class="profile-header">
            <img src="<?= htmlspecialchars($user['Picture_Link'] ?: 'https://via.placeholder.com/150') ?>" 
                 alt="Аватар" class="profile-avatar_">
            
            <h1 class="profile-name">
                <?= htmlspecialchars($user['FullName'] ?? 'Не указано') ?>
            </h1>
            
            <span class="profile-role">
                <?= $roles[$user['id_role']] ?? 'Неизвестная роль' ?>
            </span>
            
            <?php if ($user['id_statut']): ?>
                <span class="profile-status <?= 
                    $user['id_statut'] == 1 ? 'status-active' : 
                    ($user['id_statut'] == 2 ? 'status-banned' : 'status-pending') 
                ?>">
                    <i class="fas fa-<?= 
                        $user['id_statut'] == 1 ? 'check-circle' : 
                        ($user['id_statut'] == 2 ? 'ban' : 'clock') 
                    ?>"></i>
                    <?= $statuses[$user['id_statut']] ?? 'Неизвестный статус' ?>
                </span>
            <?php endif; ?>
        </div>
        
        <div class="profile-grid">
            <div class="profile-card">
                <h2 class="card-title">
                    <i class="fas fa-id-card"></i>
                    Основная информация
                </h2>
                
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value">
                        <?= htmlspecialchars($user['email'] ?? 'Не указан') ?>
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Дата рождения</span>
                    <span class="info-value">
                        <?= $user['BirthDate'] ? date('d.m.Y', strtotime($user['BirthDate'])) : 'Не указана' ?>
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Дата регистрации</span>
                    <span class="info-value">
                        <?= $user['date_added'] ? date('d.m.Y', strtotime($user['date_added'])) : 'Неизвестно' ?>
                    </span>
                </div>
            </div>
            
            <div class="profile-card">
                <h2 class="card-title">
                    <i class="fas fa-shield-alt"></i>
                    Безопасность
                </h2>
                
                <div class="info-item">
                    <span class="info-label">Роль в системе</span>
                    <span class="info-value">
                        <?= $roles[$user['id_role']] ?? 'Неизвестная роль' ?>
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Статус аккаунта</span>
                    <span class="info-value">
                        <?= $user['id_statut'] ? $statuses[$user['id_statut']] : 'Неизвестный статус' ?>
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Последний вход</span>
                    <span class="info-value">
                        <?= isset($_SESSION['last_login']) ? 
                            date('d.m.Y H:i', $_SESSION['last_login']) : 'Неизвестно' ?>
                    </span>
                </div>
            </div>
        </div>
        
        <a href="#" class="edit-btn">
            <i class="fas fa-edit"></i>
            Редактировать профиль
        </a>

    </div>
    </div>

    </section>
    <!-- Модальное окно редактирования профиля -->
<div id="editProfileModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" id="closeModal">&times;</span>
    <h2>Редактировать профиль</h2>
    <form id="editProfileForm" action="/Academy/student/courses/update_profile.php" method="POST" enctype="multipart/form-data">
      <label for="fullName">ФИО:</label>
      <input type="text" id="fullName" name="FullName" value="<?= htmlspecialchars($user['FullName'] ?? '') ?>" required>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>

      <label for="picture">Фото профиля:</label>
      <input type="file" id="picture" name="Picture_Link" accept="image/*">

      <button type="submit" class="btn-save">Сохранить</button>
    </form>
  </div>
</div>
<script>
  const modal = document.getElementById('editProfileModal');
  const editBtn = document.querySelector('.edit-btn');
  const closeBtn = document.getElementById('closeModal');

  editBtn.addEventListener('click', function(event) {
    event.preventDefault(); // чтобы не переходить по ссылке
    modal.style.display = 'flex';
  });

  closeBtn.addEventListener('click', function() {
    modal.style.display = 'none';
  });

  window.addEventListener('click', function(event) {
    if (event.target === modal) {
      modal.style.display = 'none';
    }
  });
</script>


</body>
</html>