<body class="hold-transition sidebar-mini">
  <div class="wrapper">
    <?php include './nav_courses.php' ?>

    <?php
    // SQL query to fetch courses
    $query = "SELECT c.id as id_course, c.title, c.language, c.price, c.discount, 
    c.video_url, c.date_added, c.last_modified, CA.name as category, 
    st.name as statut, l.name as level FROM courses c 
    JOIN categories CA ON c.id_category = CA.id 
    JOIN statuts st ON c.id_statut = st.id 
    JOIN levels l ON c.id_level = l.id 
    WHERE c.id_users = '" . $_SESSION['id_user'] . "' 
    ORDER BY category ASC";
    
    // Execute query
    $result = mysqli_query($conn, $query);

    // Check for query errors
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }
    ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"> Список курсов </h3>
    </div>
    <div class="card-body">
        <table id="coursesTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Категори курса</th>
                    <th>Название курса</th>
                    <th>Статус курса</th>
                    <th>Уровень курса</th> 
                    <th>Цена курса</th>
                    <th>Скидка на курса</th>
                    <th>Видео Курса</th>
                    <th>Действия</th> 
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['statut']) ?></td>
                    <td><?= htmlspecialchars($row['level']) ?></td>
                    <td><?= htmlspecialchars($row['price']) ?> РУБ </td>
                    <td><?= htmlspecialchars($row['discount'] ) ?> % </td>
                    <td><a href="<?= htmlspecialchars($row['video_url']) ?>" target="_blank" rel="noopener noreferrer"> Watch Video</a></td>
                    <td>
                        <a href="edit.php?id=<?= $row['id_course'] ?>" class="btn btn-sm btn-primary">Редактировать</a>
                        <a href="delete.php?id=<?= $row['id_course'] ?>" class="btn btn-sm btn-danger">Удалить</a>
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