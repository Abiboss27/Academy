<?php
include '../database.php';

// Вынесем функцию выше, чтобы не было ошибок вызова
function getLevel($id) {
    $levels = [1 => 'Начальный', 2 => 'Средний', 3 => 'Продвинутый'];
    return $levels[$id] ?? 'Не указан';
}

// Обработка фильтров
$where = [];
$params = [];
$types = '';

if (!empty($_GET['category'])) {
    $where[] = "c.id_category = ?";
    $params[] = (int)$_GET['category'];
    $types .= 'i';
}

// price_type: all (или не передан) - не фильтруем, free - price=0, paid - price>0
if (!empty($_GET['price_type']) && $_GET['price_type'] !== 'all') {
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
$sql = "SELECT c.*, cat.name AS category_name, AVG(e.rating) AS avg_rating
        FROM courses c
        LEFT JOIN categories cat ON c.id_category = cat.id
        LEFT JOIN enrol e ON c.id = e.id_course
        ".(!empty($where) ? " WHERE ".implode(' AND ', $where) : "")."
        GROUP BY c.id";

// Фильтр по рейтингу (HAVING)
if (!empty($_GET['rating'])) {
    $sql .= " HAVING avg_rating >= ?";
    $params[] = (int)$_GET['rating'];
    $types .= 'i';
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $conn->error);
}
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
    <style>
        .enrol-btn {
    display: block;
    background: #4f46e5;
    color: white;
    text-align: center;
    padding: 10px;
    margin: 0 15px 15px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.3s ease;
}

.enrol-btn:hover {
    background: #4338ca;
}
    </style>
</head>
<body>
<header>
    <div class="container header-container">
        <div class="logo">ABIBOSS <span>ACADEMY</span></div>
        <nav>
            <ul>
                <li><a href="../index.php">Главная</a></li>
                <li><a href="../login.html">Вход</a></li>
                <li><a href="index.php">Курсы</a></li>
                <li><a href="../about_us.html">О нас</a></li>
            </ul>
        </nav>
    </div>
</header>
<!-- Фильтры -->
<form method="GET" class="filters">
    <input type="text" name="search" placeholder="Поиск курсов..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
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
    <label><input type="radio" name="price_type" value="all" <?= empty($_GET['price_type']) || $_GET['price_type']=='all' ? 'checked' : '' ?>> Все</label>
    <label><input type="radio" name="price_type" value="free" <?= ($_GET['price_type'] ?? '') === 'free' ? 'checked' : '' ?>> Бесплатные</label>
    <label><input type="radio" name="price_type" value="paid" <?= ($_GET['price_type'] ?? '') === 'paid' ? 'checked' : '' ?>> Платные</label>
    <button type="submit">Применить</button>
</form>

<div class="courses-grid">
    <?php if ($result->num_rows == 0): ?>
        <div class="no-courses">Курсы не найдены.</div>
    <?php else: ?>
        <?php while ($course = $result->fetch_assoc()): 
            $price = '';
            if ($course['price'] == 0) {
                $price = 'Бесплатно';
            } else {
                $price = $course['discount'] > 0 
                    ? "<s>{$course['price']} ₽</s> ".($course['price'] - $course['discount']).' ₽'
                    : $course['price'].' ₽';
            }
        ?>
        <div class="course-card">
            <img src="<?= htmlspecialchars($course['Picture_Link']) ?>" alt="Обложка курса">
            <h3><?= htmlspecialchars($course['title']) ?></h3>
            <div class="category"><?= htmlspecialchars($course['category_name']) ?></div>
            <p class="description"><?= htmlspecialchars($course['short_description']) ?></p>
            <div class="details">
                <span>Уровень: <?= getLevel($course['id_level']) ?></span>
                <span>Язык: <?= htmlspecialchars($course['language']) ?></span>
                <div class="rating">
                    <?= str_repeat('★', round($course['avg_rating'] ?? 0)) ?>
                    <?= str_repeat('☆', 5 - round($course['avg_rating'] ?? 0)) ?>
                </div>
                <div class="price"><?= $price ?></div>
            </div>
            <a href="../login.html" class="enrol-btn">Записаться</a>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<script src="script.js"></script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
