<?php
require_once __DIR__ . '/../database.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список курсов</title>
    <link rel="stylesheet" href="./src/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <script src="./src/js/jquery.min.js"></script>
    <script src="./src/js/bootstrap.bundle.min.js"></script>
    <script src="./src/js/adminlte.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include './header.php'; ?>
    <div class="content-wrapper">
        <?php include './nav_courses.php'; ?>

        <section class="content p-3">
            <?php
            $query = "SELECT c.id as id_course, c.title, c.language, c.price, c.discount, 
            c.video_url, c.date_added, c.last_modified, CA.name as category , 
            st.name as statut, l.name as level FROM courses c 
            JOIN categories CA ON c.id_category = CA.id 
            JOIN statuts st ON c.id_statut = st.id 
            JOIN levels l ON c.id_level = l.id 
            ORDER BY category ASC";
            $result = mysqli_query($conn, $query);
            ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Список курсов</h3>
                </div>
                <div class="card-body">
                    <table id="coursesTable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Категория курса</th>
                                <th>Название курса</th>
                                <th>Статус курса</th>
                                <th>Уровень курса</th>
                                <th>Цена курса</th>
                                <th>Скидка курса</th>
                                <th>Видео курса</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['statut']) ?></td>
                                <td><?= htmlspecialchars($row['level']) ?></td>
                                <td><?= number_format($row['price'], 2, ',', ' ') ?> РУБ</td>
                                <td><?= htmlspecialchars($row['discount']) ?> %</td>
                                <td>
                                    <?php if (!empty($row['video_url'])): ?>
                                        <a href="<?= htmlspecialchars($row['video_url']) ?>" target="_blank" rel="noopener noreferrer">Смотреть видео</a>
                                    <?php else: ?>
                                        Нет видео
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#coursesTable').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Russian.json"
        }
    });
});
</script>
</body>
</html>
