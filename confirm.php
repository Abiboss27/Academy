<?php
// Start session (if you want to use sessions later)
session_start();
$userName = isset($_GET['name']) ? htmlspecialchars(urldecode($_GET['name'])) : 'Новый пользователь';

// Set page title
$pageTitle = "Регистрация завершена";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<link rel="icon" type="image/x-icon" href="assets/images/logo.png">  
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <meta name="description" content="">
      <link href="https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900" rel="stylesheet">
  
      <!-- Bootstrap core CSS -->
      <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  
      <!-- Additional CSS Files -->
      <link rel="stylesheet" href="assets/css/fontawesome.css">
      <link rel="stylesheet" href="assets/css/templatemo-edu-meeting.css">
      <link rel="stylesheet" href="assets/css/owl.css">
      <link rel="stylesheet" href="assets/css/lightbox.css">
    <title><?php echo $pageTitle; ?></title>
    <style>
    body {
    font-family: Arial, sans-serif;
    background-image: url("https://images.unsplash.com/photo-1515378960530-7c0da6231fb1?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D");
    background-size: cover;
    background-repeat: no-repeat;
    background-color: #f5f5f5;
    color: #333;
    margin: 0;
    min-height: 100vh;
}
 .container {
            max-width: 700px;
          
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.54);
            text-align: center;
           
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        p {
            margin-bottom: 30px;
            font-size: 18px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #e74c3c;
            color: white; 
        }
        .user-name {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Регистрация успешна!</h1>
        <p>Добро пожаловать, <span class="user-name"><?php echo $userName; ?></span>!</p>
        <p>Ваша учетная запись была успешно создана.</p>
        <p>Теперь вы можете войти в свой аккаунт, используя кнопку ниже.</p>
        
        <a href="login.html" class="btn">Войти сейчас</a>
        
        <p style="margin-top: 30px; font-size: 14px;">
            Нужна помощь? <a href="mailto:support@example.com">Свяжитесь с нашей службой поддержки</a>
        </p>
    </div>
</body>
</html>
