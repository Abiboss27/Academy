<?php  
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 
 

// Check if the user is logged in  
if (!isset($_SESSION['id_user'])) {  
    header("Location: ../login.html");  
    exit(); 
}    

require "../database.php";   

$currentPage = basename($_SERVER['SCRIPT_NAME']);
$pageTitle = $pageTitle ?? 'Админ панель'; // Default fallback
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/x-icon" href="./assets/images/logo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
</head>
<body>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <?php
            $fullName = '';
            $imageSrc = 'path/to/default/image.jpg'; // Default fallback image

            if (isset($conn)) {
                $query = "SELECT * FROM users WHERE id_role = 1 LIMIT 1";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    $user = $result->fetch_assoc();

                    if (!empty($user['FullName'])) {
                        $fullName = htmlspecialchars($user['FullName']);
                    }

                    if (!empty($user['Picture_Link']) && strpos($user['Picture_Link'], 'data:image') === 0) {
                        $imageSrc = $user['Picture_Link'];
                    }
                }
            }
            ?>

            <div style="text-align: center; width: 100%;">
                <img src="<?= $imageSrc; ?>"
                     alt="User Image"
                     style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;
                            border: 3px solid #4a90e2; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                            transition: transform 0.3s ease;"
                     onmouseover="this.style.transform='scale(1.05)'"
                     onmouseout="this.style.transform='scale(1)'">
                <div style="margin-top: 15px;">
                    <a href="#"
                       style="font-size: 1.4rem; font-weight: 600; color: #2c3e50; text-decoration: none;
                              padding: 8px 16px; border-radius: 4px; background-color: #f8f9fa;
                              transition: all 0.3s ease;"
                       onmouseover="this.style.color='#4a90e2'; this.style.backgroundColor='#e9ecef'"
                       onmouseout="this.style.color='#2c3e50'; this.style.backgroundColor='#f8f9fa'">
                        <?= $fullName ?>
                    </a>
                </div>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" id="sidebarMenu">
                <li class="nav-item"><a href="admin_home.php" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Панель управления</p></a></li>
                <li class="nav-item"><a href="categories_table.php" class="nav-link"><i class="nav-icon fas fa-book"></i><p>Категории Курсы</p></a></li>
                <li class="nav-item"><a href="courses_table.php" class="nav-link"><i class="nav-icon fas fa-book"></i><p>Все Курсы</p></a></li>
                <li class="nav-item"><a href="stud_table.php" class="nav-link"><i class="nav-icon fas fa-user-graduate"></i><p>Студенты</p></a></li>
                <li class="nav-item"><a href="students_table.php" class="nav-link"><i class="nav-icon fas fa-user-graduate"></i><p>Студенты и Курсы</p></a></li>
                <li class="nav-item"><a href="teachers_table.php" class="nav-link"><i class="nav-icon fas fa-user"></i><p>Все преподаватели</p></a></li>
                <li class="nav-item"><a href="report.php" class="nav-link"><i class="nav-icon fas fa-ruble-sign"></i><p>Доход администратора</p></a></li>
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

<script>
    function logout() {
        Swal.fire({
            title: 'Вы уверены?',
            text: "Вы будете разлогинены!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Да, выйти!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../index.php';
            }
        });
    }

    // Sidebar submenu toggle
    document.addEventListener("DOMContentLoaded", function () {
        const submenuParents = document.querySelectorAll(".has-submenu > .nav-link");
        submenuParents.forEach(link => {
            link.addEventListener("click", function (e) {
                e.preventDefault();
                const parent = this.closest(".nav-item");
                parent.classList.toggle("menu-open");

                const submenu = parent.querySelector(".nav-treeview");
                if (submenu) {
                    submenu.classList.toggle("show");
                }
            });
        });

        // Добавляем логику подсветки активного пункта меню при клике
        const navLinks = document.querySelectorAll(".nav-link");

        navLinks.forEach(link => {
            link.addEventListener("click", function (e) {
                // Убираем active у всех
                navLinks.forEach(l => l.classList.remove("active"));

                // Добавляем active к текущему
                this.classList.add("active");

                // Если это пункт с подменю, раскрываем его
                const parent = this.closest(".nav-item");
                if (parent && parent.classList.contains("has-submenu")) {
                    parent.classList.add("menu-open");
                    const submenu = parent.querySelector(".nav-treeview");
                    if (submenu) {
                        submenu.classList.add("show");
                    }
                }
            });
        });

        // Подсвечиваем пункт меню, соответствующий текущему URL при загрузке страницы
        const currentPath = window.location.pathname.split("/").pop();

        navLinks.forEach(link => {
            const href = link.getAttribute("href");
            if (href === currentPath) {
                link.classList.add("active");
                const parent = link.closest(".nav-item.has-submenu");
                if (parent) {
                    parent.classList.add("menu-open");
                    const submenu = parent.querySelector(".nav-treeview");
                    if (submenu) submenu.classList.add("show");
                }
            }
        });
    });
</script>

</body>
</html>
