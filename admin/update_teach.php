<?php
require_once '../database.php';

$errors = [];
$user_id = intval($_GET['user_id'] ?? 0);
$userData = null;

// Загрузка данных пользователя для отображения в форме
if ($user_id > 0) {
    $stmt = $conn->prepare("SELECT FullName, email, BirthDate, Picture_Link FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    $stmt->close();

    if (!$userData) {
        die("Пользователь не найден");
    }
} else {
    die("Не указан ID пользователя");
}

// Обработка формы обновления
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update-submit'])) {
    $FullName = trim($_POST['FullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $BirthDate = $_POST['BirthDate'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $pictureBase64 = null;

    // Валидация
    if (empty($FullName)) {
        $errors[] = "ФИО обязательно";
    }
    if (empty($email)) {
        $errors[] = "Email обязателен";
    } else {
        // Проверка уникальности email (кроме текущего пользователя)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email уже используется";
        }
        $stmt->close();
    }
    if (empty($BirthDate)) {
        $errors[] = "Дата рождения обязательна";
    }

    // Пароль обновляем только если заполнен
    $passwordHash = null;
    if (!empty($password)) {
        if ($password !== $confirmPassword) {
            $errors[] = "Пароли не совпадают";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            if ($passwordHash === false) {
                $errors[] = "Ошибка хеширования пароля";
            }
        }
    }

    // Обработка файла фото
    if (isset($_FILES['PictureLink']) && $_FILES['PictureLink']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['PictureLink'];
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);

        if (!array_key_exists($detectedType, $allowedTypes)) {
            $errors[] = "Разрешены только JPG, PNG, GIF";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = "Размер изображения не более 2 МБ";
        } else {
            $imageData = file_get_contents($file['tmp_name']);
            $pictureBase64 = 'data:' . $detectedType . ';base64,' . base64_encode($imageData);
        }

        if ($_FILES['PictureLink']['error'] > 0 && $_FILES['PictureLink']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => "Файл слишком большой",
                UPLOAD_ERR_FORM_SIZE => "Файл слишком большой",
                UPLOAD_ERR_PARTIAL => "Файл загружен частично",
                UPLOAD_ERR_NO_TMP_DIR => "Отсутствует временная папка",
                UPLOAD_ERR_CANT_WRITE => "Ошибка записи файла",
                UPLOAD_ERR_EXTENSION => "Загрузка остановлена расширением"
            ];
            $errors[] = $uploadErrors[$_FILES['PictureLink']['error']] ?? "Неизвестная ошибка загрузки";
        }
    }

    // Если нет ошибок — обновляем данные
    if (empty($errors)) {
        $query = "UPDATE users SET FullName = ?, email = ?, BirthDate = ?";
        $params = [$FullName, $email, $BirthDate];
        $types = "sss";

        if ($passwordHash !== null) {
            $query .= ", password = ?";
            $types .= "s";
            $params[] = $passwordHash;
        }
        if ($pictureBase64 !== null) {
            $query .= ", Picture_Link = ?";
            $types .= "s";
            $params[] = $pictureBase64;
        }
        $query .= " WHERE id = ?";
        $types .= "i";
        $params[] = $user_id;

        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            $errors[] = "Ошибка базы данных: " . $conn->error;
        } else {
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                header("Location: confirm.php?name=" . urlencode($FullName));
                exit();
            } else {
                $errors[] = "Ошибка обновления: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Обновление данных пользователя</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 600px; margin-top: 30px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Обновление данных пользователя</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">

        <div class="form-group">
            <label for="FullName">ФИО</label>
            <input type="text" class="form-control" id="FullName" name="FullName" required value="<?= htmlspecialchars($userData['FullName']) ?>">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($userData['email']) ?>">
        </div>

        <div class="form-group">
            <label for="BirthDate">Дата рождения</label>
            <input type="date" class="form-control" id="BirthDate" name="BirthDate" required value="<?= htmlspecialchars($userData['BirthDate']) ?>">
        </div>

        <div class="form-group">
            <label for="PictureLink">Фото профиля (оставьте пустым, чтобы не менять)</label><br>
            <?php if (!empty($userData['Picture_Link'])): ?>
                <img src="<?= htmlspecialchars($userData['Picture_Link']) ?>" alt="Фото профиля" style="max-width: 150px; max-height: 150px;"><br><br>
            <?php endif; ?>
            <input type="file" id="PictureLink" name="PictureLink" accept="image/jpeg,image/png,image/gif">
        </div>

        <div class="form-group">
            <label for="password">Новый пароль (оставьте пустым, чтобы не менять)</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Новый пароль">
        </div>

        <div class="form-group">
            <label for="confirmPassword">Подтверждение пароля</label>
            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Подтвердите пароль">
        </div>

        <button type="submit" name="update-submit" class="btn btn-primary">Обновить</button>
    </form>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
