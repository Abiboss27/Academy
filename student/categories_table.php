<?php
require_once __DIR__ . '/../database.php';

$query = "SELECT id as id_categorie, name, date_added
 FROM categories
 ORDER BY name ASC; ";
$result = mysqli_query($conn, $query);
?>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">
    <?php include './nav_categories.php' ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"> Список Категори </h3>
    </div>
    <div class="card-body">
        <table id="coursesTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th> Название категори</th>
                    <th>Дата добавление </th> 
                    <th>Действия</th> 
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['date_added']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $row['id_categorie'] ?>" class="btn btn-sm btn-primary">Редактировать</a>
                        <a href="delete.php?id=<?= $row['id_categorie'] ?>" class="btn btn-sm btn-danger">Удалить</a>
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