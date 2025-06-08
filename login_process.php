<?php  
require 'database.php';  
session_start();  

// Инициализация счетчика попыток входа
if (!isset($_SESSION['login_attempts'])) {  
    $_SESSION['login_attempts'] = 0;  
}

// Проверка, что форма была отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login-submit'])) {    
    // Очистка и валидация ввода
    $password = trim($_POST['password'] ?? '');    
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);   

    // Проверка заполненности полей
    if (empty($email) || empty($password)) {  
        header("Location: login.html?error=emptyfields");  
        exit();  
    }  

    // Валидация email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {  
        header("Location: login.html?error=invalidEmail");  
        exit();  
    }  

    // Подготовленный запрос для безопасности
    $sql = "SELECT id, email, password, id_role FROM users WHERE email = ? LIMIT 1";  
    $stmt = mysqli_stmt_init($conn);  
    
    if (!mysqli_stmt_prepare($stmt, $sql)) {  
        error_log("SQL error in login: " . mysqli_error($conn));
        header("Location: login.html?error=sqlerror");  
        exit();  
    } 
    
    mysqli_stmt_bind_param($stmt, "s", $email);  
    mysqli_stmt_execute($stmt);  
    $result = mysqli_stmt_get_result($stmt);  

    if ($row = mysqli_fetch_assoc($result)) {  
        // Проверка пароля
        if (!password_verify($password, $row['password'])) {  
            $_SESSION['login_attempts']++;  
            
            if ($_SESSION['login_attempts'] >= 4) {  
                // Перенаправление на сброс пароля после 4 неудачных попыток
                header("Location: new_password.html");  
                exit();  
            } else {  
                header("Location: login.html?error=wrongpassword&attempts=" . $_SESSION['login_attempts']);  
                exit();  
            }  
        }  
        
        // Успешная авторизация
        $_SESSION['login_attempts'] = 0;  
        $_SESSION['id_user'] = $row['id'];   
        $_SESSION['user_role'] = $row['id_role'];
        $_SESSION['user_email'] = $row['email'];
        
        // Регенерация ID сессии для защиты от фиксации
        session_regenerate_id(true);
        
        // Перенаправление в зависимости от роли
        switch ($row['id_role']) {
            case 1:
                header("Location: admin/admin_home.php");   
                break;
            case 2:
                header("Location: student/courses/index.php");  
                break;
            case 3:
                header("Location: teacher/teacher_home.php");  
                break;
            default:
                header("Location: login.html?error=invalidrole");  
        }
        exit(); 
    } else {  
        // Пользователь не найден
        header("Location: login.html?error=nouser");  
        exit();  
    }  
} else {  
    // Прямой доступ к скрипту без отправки формы
    header("Location: login.html?error=unauthorized");  
    exit();  
}  
?>