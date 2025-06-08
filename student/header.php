<?php  
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 

if (!isset($_SESSION['id_user'])) {  
    header("Location: ../login.html");  
    exit(); 
}    

$id_user = $_SESSION['id_user']; 
require __DIR__ . "/../database.php";   

$fullName = "Студент";
$imageSrc = '/Academy/student/assets/images/default-user.png';

if (isset($conn)) {
    $query = "SELECT * FROM users WHERE id = $id_user";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (!empty($user['FullName'])) {
            $fullName = htmlspecialchars($user['FullName']);
        }
        if (!empty($user['Picture_Link'])) {
            $imageSrc = $user['Picture_Link'];
        }
    }
}
?>

<!-- Подключение стилей -->
<link href="/Academy/student/assets/css/header.css" rel="stylesheet">
<link href="/Academy/student/assets/css/header_.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<nav class="horizontal-menu">
    <ul>
        <li><a href="/Academy/student/courses/index.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>"><i class="fas fa-home"></i> Главная</a></li>
        <li><a href="/Academy/student/courses/my_courses.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'my_courses.php') ? 'active' : '' ?>"><i class="fas fa-book"></i> Мои курсы</a></li>
        <li><a href="/Academy/student/zachetnya/grades.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'grade.php') ? 'active' : '' ?>"><i class="fas fa-clipboard-list"></i> Зачетная книжка</a></li>
    </ul>

    <!-- Блок пользователя вынесен из списка и позиционируется отдельно -->
    <div class="user-panel">
        <img src="<?= $imageSrc ?>" class="profile-avatar" alt="Фото профиля">
        <span class="user-name"><?= explode(' ', $fullName)[0] ?></span>

        <div class="profile-options">
            <a href="/Academy/student/courses/profile.php" class="profile-option ajax-link" data-content="courses/profile.php">
                <i class="fas fa-user"></i> Профиль
            </a>
            <a href="courses/my_cours.php" class="profile-option ajax-link" data-content="courses/my_cours.php">
                <i class="fas fa-book"></i> Мои курсы
            </a>
            <a href="zachetnya/grade.php" class="profile-option ajax-link" data-content="zachetnya/grade.php">
                <i class="fas fa-clipboard-list"></i> Зачетная книжка
            </a>
            <div class="profile-option logout-btn" id="logout-btn" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> Выход
            </div>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function logout() {
        Swal.fire({
            title: 'Вы уверены?',
            text: "Вы будете разлогинены!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Да, выйти!',
            cancelButtonText: 'Отмена',
            backdrop: 'rgba(0,0,0,0.4)'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/Academy/index.php';
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        const currentPage = location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.horizontal-menu ul li a');
        
        navLinks.forEach(link => {
            const linkHref = link.getAttribute('href').split('/').pop();
            if (currentPage === linkHref) {
                link.classList.add('active');
            }
        });
    });

    const avatar = document.querySelector('.profile-avatar');
    if (avatar) {
        avatar.addEventListener('mouseenter', () => {
            avatar.style.transform = 'scale(1.1)';
            avatar.style.boxShadow = '0 0 10px rgba(255,255,255,0.5)';
        });
        avatar.addEventListener('mouseleave', () => {
            avatar.style.transform = 'scale(1)';
            avatar.style.boxShadow = 'none';
        });
    }
</script>

<style>
 
</style>
