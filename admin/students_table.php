<?php
require_once __DIR__ . '/../database.php';

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список студентов</title>
    <link rel="stylesheet" href="./src/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
</head>
<body class="hold-transition sidebar-mini">

<div class="wrapper">
    <?php include 'header.php'; ?>
    <div class="content-wrapper">
        <?php include './nav_students.php'; ?>

        <section class="content pt-3">
            <?php
            $query = "SELECT e.id as id_enrol, u.FullName, c.title, e.rating, e.comments, e.date_added, CA.name as category
            FROM enrol e
            JOIN users u ON e.id_user = u.id 
            JOIN courses c ON e.id_course = c.id
            JOIN categories CA ON c.id_category = CA.id
            ORDER BY u.FullName ASC";
            $result = mysqli_query($conn, $query);
            ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Список студентов</h3>
                </div>
                <div class="card-body">
                    <table id="enrolTable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Студенты</th>
                                <th>Название курса</th>
                                <th>Категория</th>
                                <th>Рейтинг</th>
                                <th>Комментарии</th>
                                <th>Дата добавления</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['FullName']) ?></td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars($row['category']) ?></td>
                                    <td><?= htmlspecialchars($row['rating']) ?></td>
                                    <td><?= htmlspecialchars($row['comments']) ?></td>
                                    <td><?= htmlspecialchars($row['date_added']) ?></td>
                                    <td>
                                        <a href="delete.php?id=<?= $row['id_enrol'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить эту запись?');">Удалить</a>
                                    </td> 
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">Записи не найдены</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>
</div>

<!-- Подключение JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="./src/js/bootstrap.bundle.min.js"></script>
<script src="./src/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#enrolTable').DataTable({
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
