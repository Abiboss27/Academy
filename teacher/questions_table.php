<body class="hold-transition sidebar-mini">
  <div class="wrapper">
    <?php include './nav_test.php' ?>
        <?php
        require_once __DIR__ . '/../database.php';
        // Query to get tests for courses given by the logged-in teacher
        $testsQuery = "
            SELECT t.id as test_id, t.title as test_title, se.name as section_name, t.id_section, c.title as course_title
            FROM tests t
            JOIN courses c ON t.id_course = c.id
            JOIN sections se ON t.id_section = se.id
            WHERE c.id_users = '" . $_SESSION['id_user'] . "'
            ORDER BY c.title ASC, se.name ASC, t.title ASC";

        $testsResult = mysqli_query($conn, $testsQuery);

        // Check if the query was successful
        if (!$testsResult) {
            die("Error: " . mysqli_error($conn));
        }
        ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Название теста</th>
                        <th>Курс</th>
                        <th>Раздел</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($testsResult)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['test_title']) ?></td>
                        <td><?= htmlspecialchars($row['course_title']) ?></td>
                        <td><?= htmlspecialchars($row['section_name']) ?></td>
                        <td>
                            <a href="view_test.php?id=<?= $row['test_id'] ?>" class="btn btn-primary btn-sm">Просмотреть</a>
                            <a href="edit_test.php?id=<?= $row['test_id'] ?>" class="btn btn-warning btn-sm">Редактировать</a>
                            <a href="delete_test.php?id=<?= $row['test_id'] ?>" class="btn btn-danger btn-sm">Удалить</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

