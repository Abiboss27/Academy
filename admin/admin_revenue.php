<?php
require_once '../database.php';

// Обработка AJAX-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $response = handleRequest($pdo, $_POST);
        echo json_encode($response);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Получение данных о доходах (убрал p.id из SELECT)
$query = "
    SELECT 
        p.user_id,
        p.payment_type,
        p.course_id,
        p.amount,
        FROM_UNIXTIME(p.date_added) as payment_date,
        p.admin_revenue,
        p.instructor_revenue,
        p.instructor_payment_status,
        u.first_name as user_first_name,
        u.last_name as user_last_name,
        c.title as course_title
    FROM 
        payment p
    LEFT JOIN 
        users u ON p.user_id = u.id
    LEFT JOIN 
        course c ON p.course_id = c.id
    ORDER BY 
        p.date_added DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Расчет общих сумм
$total_admin_revenue = 0;
$total_instructor_revenue = 0;
$total_amount = 0;

foreach ($payments as $payment) {
    $total_admin_revenue += (float)$payment['admin_revenue'];
    $total_instructor_revenue += (float)$payment['instructor_revenue'];
    $total_amount += (float)$payment['amount'];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Доходы администратора и преподавателей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .revenue-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .status-paid {
            color: green;
            font-weight: bold;
        }
        .status-unpaid {
            color: orange;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Доходы администратора и преподавателей</h1>
        
        <div class="row revenue-summary">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Общий доход</h5>
                        <p class="card-text fs-3"><?= number_format($total_amount, 2) ?> ₽</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Доход администратора</h5>
                        <p class="card-text fs-3 text-primary"><?= number_format($total_admin_revenue, 2) ?> ₽</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Доход преподавателей</h5>
                        <p class="card-text fs-3 text-success"><?= number_format($total_instructor_revenue, 2) ?> ₽</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Детализация платежей</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Пользователь</th>
                                <th>Курс</th>
                                <th>Сумма</th>
                                <th>Админ</th>
                                <th>Преподаватель</th>
                                <th>Статус выплаты</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= $payment['payment_date'] ?></td>
                                <td><?= $payment['user_first_name'] ?> <?= $payment['user_last_name'] ?></td>
                                <td><?= $payment['course_title'] ?></td>
                                <td><?= number_format($payment['amount'], 2) ?> ₽</td>
                                <td class="text-primary"><?= number_format($payment['admin_revenue'], 2) ?> ₽</td>
                                <td class="text-success"><?= number_format($payment['instructor_revenue'], 2) ?> ₽</td>
                                <td class="<?= $payment['instructor_payment_status'] ? 'status-paid' : 'status-unpaid' ?>">
                                    <?= $payment['instructor_payment_status'] ? 'Выплачено' : 'Не выплачено' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>