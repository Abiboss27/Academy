<?php
require_once __DIR__ . '/../database.php';

// Обработка удаления студента по GET-параметру id
if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    if ($user_id > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            // Перенаправление обратно без GET-параметра
            header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
            exit;
        } else {
            echo "Ошибка при удалении студента: " . $stmt->error;
            exit;
        }
    }
}
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
            $query = "SELECT * FROM users WHERE id_role = 2 ORDER BY FullName ASC;";
            $result = mysqli_query($conn, $query);
            ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Список студентов</h3>
                </div>
                <div class="card-body">
                    <table id="studentsTable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Студент</th>
                                <th>Дата рождения</th>
                                <th>Возраст</th>
                                <th>Эл. адрес</th>
                                <th>Дата добавления</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)):
                                    $birthDate = new DateTime($row['BirthDate']);
                                    $today = new DateTime();
                                    $age = $today->diff($birthDate)->y;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['FullName']) ?></td>
                                    <td><?= htmlspecialchars($row['BirthDate']) ?></td>
                                    <td><?= $age ?> лет</td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['date_added']) ?></td>
                                    <td>
                                        <a href="?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить этого студента?');">
                                            <i class="fas fa-trash-alt"></i> Удалить
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Студенты не найдены</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- JS-библиотеки -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="./src/js/bootstrap.bundle.min.js"></script>
<script src="./src/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#studentsTable').DataTable({
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
