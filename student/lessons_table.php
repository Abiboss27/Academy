<body class="hold-transition sidebar-mini">
  <div class="wrapper">
    <?php include './nav_lessons.php' ?>

    <?php
    // SQL query to fetch courses
    $query = "SELECT l.id as id_lesson, c.title as course, s.name as section, l.title as lesson, l.attachment, l.video_url, 
    l.date_added, l.last_modified FROM lessons l
    JOIN courses c ON l.id_course = c.id 
    JOIN sections s ON l.id_section = s.id  
    WHERE c.id_users = '" . $_SESSION['id_user'] . "' 
    ORDER BY course ASC";
    
    // Execute query
    $result = mysqli_query($conn, $query);

    // Check for query errors
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }
    ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"> Список лекции </h3>
    </div>
    <div class="card-body">
        <table id="coursesTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th> Название лекции </th>
                    <th> Название курса</th>
                    <th> Секция </th>
                    <th>Файл</th>
                    <th>Видео Курса</th>
                    <th>Добавление</th>
                    <th>Модификация</th>
                    <th>Действия</th> 
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['lesson']) ?></td>
                    <td><?= htmlspecialchars($row['course']) ?></td>
                    <td><?= htmlspecialchars($row['section']) ?></td>
                    <td>
                    <a href="data:application/pdf;base64,<?= urlencode($row['attachment']) ?>" download="<?= basename($row['attachment']) ?>">
    <?= htmlspecialchars(basename($row['attachment'])) ?> Скачать 
</a>
                       </td>
                    <td><a href="<?= htmlspecialchars($row['video_url']) ?>" target="_blank" rel="noopener noreferrer"> Смотреть</a></td>
                    <td><?= htmlspecialchars($row['date_added']) ?></td> 
                    <td><?= htmlspecialchars($row['last_modified']) ?></td> 
                    <td>
                        <a href="edit.php?id=<?= $row['id_lesson'] ?>" class="btn btn-sm btn-primary">Редактировать</a>
                        <a href="delete.php?id=<?= $row['id_lesson'] ?>" class="btn btn-sm btn-danger">Удалить</a>
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
        "responsive": true,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Russian.json"
        }
    });
});
</script>
</body>