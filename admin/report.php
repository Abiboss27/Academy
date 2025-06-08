<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 

require_once __DIR__ . '/../database.php';

// Параметры пагинации
$limit = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Поиск по имени пользователя, названию курса и типу оплаты
$search = $_GET['search'] ?? '';
$params = [];
$types = '';

$whereClauses = [];
if ($search !== '') {
    $whereClauses[] = "(u.FullName LIKE ? OR c.title LIKE ? OR p.payment_type LIKE ?)";
    $search_like = "%$search%";
    $params = [$search_like, $search_like, $search_like];
    $types = 'sss';
}

$whereSQL = '';
if (!empty($whereClauses)) {
    $whereSQL = " WHERE " . implode(' AND ', $whereClauses);
}

// Получаем общее количество записей
$count_sql = "SELECT COUNT(*) as total 
              FROM payment p
              LEFT JOIN users u ON p.user_id = u.id
              LEFT JOIN courses c ON p.course_id = c.id
              $whereSQL";

$stmt = $conn->prepare($count_sql);
if ($search !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$count_result = $stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_rows / $limit);

// Получаем данные для текущей страницы
$data_sql = "SELECT 
                p.id,
                u.FullName AS user_name,
                p.payment_type,
                c.title AS course_title,
                p.amount,
                p.date_added,
                p.last_modified,
                p.admin_revenue,
                p.instructor_revenue,
                p.instructor_payment_status
             FROM payment p
             LEFT JOIN users u ON p.user_id = u.id
             LEFT JOIN courses c ON p.course_id = c.id
             $whereSQL
             ORDER BY p.date_added DESC
             LIMIT ? OFFSET ?";

$stmt = $conn->prepare($data_sql);

if ($search !== '') {
    $types .= 'ii';
    $params[] = $limit;
    $params[] = $offset;
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Отчёт по платежам</title>
     <link rel="stylesheet" href="./src/css/report.css">
</head>
<body>

<h1>Отчёт по платежам</h1>

<div class="search-box">
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Поиск по имени пользователя, курсу или типу оплаты" value="<?= htmlspecialchars($search) ?>" />
        <button type="submit">Поиск</button>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>Пользователь</th>
            <th>Тип оплаты</th>
            <th>Курс</th>
            <th>Сумма</th>
            <th>Дата добавления</th>
            <th>Дата изменения</th>
            <th>Доход админа</th>
            <th>Доход преподавателя</th>
            <th>Статус оплаты преподавателя</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="10">Платежи не найдены.</td></tr>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                   
                    <td><?= htmlspecialchars($row['user_name'] ?? 'Неизвестно') ?></td>
                    <td><?= htmlspecialchars($row['payment_type']) ?></td>
                    <td><?= htmlspecialchars($row['course_title'] ?? 'Неизвестно') ?></td>
                    <td><?= number_format($row['amount'], 2, ',', ' ') ?> ₽</td>
                    <td><?= $row['date_added'] ? date('d.m.Y', strtotime($row['date_added'])) : '' ?></td>
                    <td><?= $row['last_modified'] ? date('d.m.Y', strtotime($row['last_modified'])) : '' ?></td>
                    <td><?= htmlspecialchars($row['admin_revenue']) ?></td>
                    <td><?= htmlspecialchars($row['instructor_revenue']) ?></td>
                    <td><?= $row['instructor_payment_status'] ? 'Оплачено' : 'В ожидании' ?></td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=1&search=<?= urlencode($search) ?>">&laquo; Первая</a>
        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">&lt; Предыдущая</a>
    <?php endif; ?>

    <span class="current"><?= $page ?></span>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Следующая &gt;</a>
        <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>">Последняя &raquo;</a>
    <?php endif; ?>
</div>

</body>
</html>
