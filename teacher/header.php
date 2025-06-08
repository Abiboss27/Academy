<?php  
require "../database.php";   
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 

// Vérification de la connexion utilisateur  
if (!isset($_SESSION['id_user'])) {  
    header("Location: ../login.html");  
    exit();  
}  

$id_user = $_SESSION['id_user']; 

// Initialisation variables
$fullName = "Пользователь";
$imageSrc = "path/to/default/image.jpg"; // Remplace par un chemin valide par défaut

if (isset($conn)) {
    $stmt = $conn->prepare("SELECT FullName, Picture_Link FROM users WHERE id = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (!empty($user['FullName'])) {
            $fullName = htmlspecialchars($user['FullName']);
        }

        if (!empty($user['Picture_Link']) && strpos($user['Picture_Link'], 'data:image') === 0) {
            $imageSrc = $user['Picture_Link'];
        }
    }
    $stmt->close();
}
?>

<title>Teacher Dashboard</title>
<link rel="icon" type="image/x-icon" href="./assets/images/logo.png">  
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<style>
    /* Стили для активного пункта меню */
    .nav-link.active {
        background-color: #4a90e2 !important;
        color: white !important;
    }
    .nav-link.active i.nav-icon {
        color: white !important;
    }
</style>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <div class="sidebar">
    <div class="user-panel mt-3 pb-3 mb-3 d-flex flex-column align-items-center">
      <img src="<?= $imageSrc ?>" 
           alt="User Image" 
           style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #4a90e2; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s ease;"
           onmouseover="this.style.transform='scale(1.05)'" 
           onmouseout="this.style.transform='scale(1)'">
      <a href="#" 
         style="margin-top: 15px; font-size: 1.4rem; font-weight: 600; color: #2c3e50; text-decoration: none; padding: 8px 16px; border-radius: 4px; background-color: #f8f9fa; transition: all 0.3s ease;"
         onmouseover="this.style.color='#4a90e2'; this.style.backgroundColor='#e9ecef';"
         onmouseout="this.style.color='#2c3e50'; this.style.backgroundColor='#f8f9fa';">
         <?= $fullName ?>
      </a>
    </div>

    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" id="sidebarMenu" role="menu" aria-orientation="vertical">
        <li class="nav-item">
          <a href="teacher_home.php" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Панель управления</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="courses_table.php" class="nav-link menu-link" data-url="courses_table.php">
            <i class="nav-icon fas fa-book"></i>
            <p>Управление Курсами</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="students_table.php" class="nav-link menu-link" data-url="students_table.php">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p>Управление студентами</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="lessons_table.php" class="nav-link menu-link" data-url="lessons_table.php">
            <i class="nav-icon fas fa-book"></i>
            <p>Управление материалов</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="tests_table.php" class="nav-link menu-link" data-url="tests_table.php">
            <i class="nav-icon fas fa-book"></i>
            <p>Управление Тестами</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="scores_table.php" class="nav-link menu-link" data-url="scores_table.php">
            <i class="fas fa-chart-line mr-1"></i>
            <p>Прогресс студентов</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="report.php" class="nav-link menu-link" data-url="report.php">
            <i class="nav-icon fas fa-ruble-sign"></i>
            <p>Доход преподавателя</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="javascript:void(0);" class="nav-link" onclick="logout()">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Выход</p>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</aside>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function logout() {
    Swal.fire({
        title: 'Вы уверены?',
        text: "Вы будете выведены из системы!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Да, выйти!',
        cancelButtonText: 'Отмена'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../index.php';
        }
    });
}

// Подсветка активного пункта меню
document.addEventListener("DOMContentLoaded", function () {
    const navLinks = document.querySelectorAll(".nav-link.menu-link, .nav-link:not(.menu-link)");

    // Функция для удаления active у всех
    function clearActive() {
        navLinks.forEach(link => link.classList.remove("active"));
    }

    // Подсветка по клику
    navLinks.forEach(link => {
        link.addEventListener("click", function () {
            clearActive();
            this.classList.add("active");
        });
    });

    // Подсветка по текущему URL
    const currentPath = window.location.pathname.split("/").pop();
    navLinks.forEach(link => {
        const href = link.getAttribute("href");
        if (href === currentPath) {
            clearActive();
            link.classList.add("active");
        }
    });
});
</script>
