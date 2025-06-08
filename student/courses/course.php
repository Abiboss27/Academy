<?php
include 'C:/xampp/htdocs/Academy/database.php';

// Обработка фильтров
$where = [];
$params = [];
$types = '';

if (!empty($_GET['category'])) {
    $where[] = "c.id_category = ?";
    $params[] = (int)$_GET['category'];
    $types .= 'i';
}

if (!empty($_GET['price_type'])) {
    if ($_GET['price_type'] === 'free') {
        $where[] = "c.price = 0";
    } elseif ($_GET['price_type'] === 'paid') {
        $where[] = "c.price > 0";
    }
}

if (!empty($_GET['search'])) {
    $where[] = "(c.title LIKE ? OR c.short_description LIKE ?)";
    $search = "%{$_GET['search']}%";
    $params[] = $search;
    $params[] = $search;
    $types .= 'ss';
}

// Базовый SQL запрос
$where[] = "c.id_statut = 1";
$sql = "SELECT c.*, cat.name AS category_name, AVG(e.rating) AS avg_rating, lv.name AS lvl
        FROM courses c
        LEFT JOIN categories cat ON c.id_category = cat.id
        LEFT JOIN enrol e ON c.id = e.id_course
        LEFT JOIN levels lv ON c.id_level = lv.id
        ".(!empty($where) ? " WHERE ".implode(' AND ', $where) : "")."
        GROUP BY c.id";

// Фильтр по рейтингу
if (!empty($_GET['rating'])) {
    $sql .= " HAVING avg_rating >= ?";
    $params[] = (int)$_GET['rating'];
    $types .= 'i';
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="./assets/css/courses.css">
   
   
</head>
<body>
    <!-- Фильтры -->
    <form method="GET" class="filters">
        <input type="text" name="search" placeholder="Поиск курсов..." value="<?= $_GET['search'] ?? '' ?>">
        
        <select name="category">
            <option value="">Все категории</option>
            <?php 
            $categories = $conn->query("SELECT * FROM categories");
            while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>" <?= isset($_GET['category']) && $_GET['category'] == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <select name="rating">
            <option value="">Любой рейтинг</option>
            <option value="4" <?= isset($_GET['rating']) && $_GET['rating'] == 4 ? 'selected' : '' ?>>4+ звезд</option>
            <option value="3" <?= isset($_GET['rating']) && $_GET['rating'] == 3 ? 'selected' : '' ?>>3+ звезд</option>
        </select>

        <label><input type="radio" name="price_type" value="all" <?= empty($_GET['price_type']) ? 'checked' : '' ?>> Все</label>
        <label><input type="radio" name="price_type" value="free" <?= ($_GET['price_type'] ?? '') === 'free' ? 'checked' : '' ?>> Бесплатные</label>
        <label><input type="radio" name="price_type" value="paid" <?= ($_GET['price_type'] ?? '') === 'paid' ? 'checked' : '' ?>> Платные</label>
        
        <button type="submit">Применить</button>
    </form>


<div class="courses-grid">
    <?php while ($course = $result->fetch_assoc()): 
        $price = '';
        if ($course['price'] == 0) {
            $price = 'Бесплатно';
        } else {
            $price = $course['discount'] > 0 
                ? "<s>{$course['price']}</s> ".($course['price'] - $course['discount'])
                : $course['price'].' ₽';
        }
    ?>
    <div class="course-card">
        <img src="<?= htmlspecialchars($course['Picture_Link']) ?>" alt="Обложка курса">
        <h3><?= htmlspecialchars($course['title']) ?></h3>
        <div class="category"><?= htmlspecialchars($course['category_name']) ?></div>
        <p class="description"><?= htmlspecialchars($course['short_description']) ?></p>
        
        <div class="details">
            <span>Уровень: <?= htmlspecialchars($course['lvl']) ?></span>
            <span>Язык: <?= htmlspecialchars($course['language']) ?></span>
            <div class="rating">
                <?= str_repeat('★', round($course['avg_rating'] ?? 0)) ?>
                <?= str_repeat('☆', 5 - round($course['avg_rating'] ?? 0)) ?>
            </div>
            <div class="price"><?= $price ?></div>
        </div>
        
        <a href="http://localhost:80/Academy/student/courses/enroll.php?course_id=<?= $course['id'] ?>" class="enroll-btn">Записаться</a>
    </div>
    <?php endwhile; ?>
</div>

    <script src="script.js"></script>
 
</body>
</html>

