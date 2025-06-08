<?php
session_start();
require 'C:/xampp/htdocs/Academy/database.php';
header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация!']);
    exit;
}

// Валидация данных
$userId = $_SESSION['user_id'];
$courseId = intval($_POST['course_id']);
$paymentType = in_array($_POST['payment_type'], ['credit_card', 'paypal', 'bank_transfer']) 
               ? $_POST['payment_type'] 
               : null;
$amount = floatval($_POST['amount']);

if (!$paymentType || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    exit;
}

// Транзакция для безопасности
$conn->begin_transaction();

try {
    // Вставка платежа
    $stmt = $conn->prepare("INSERT INTO payment (
        user_id, payment_type, course_id, amount, 
        date_added, last_modified
    ) VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
    
    $stmt->bind_param("issd", $userId, $paymentType, $courseId, $amount);
    $stmt->execute();
    
    // Расчет доходов
    $paymentId = $conn->insert_id;
    $adminRevenue = $amount * 0.7;
    $instructorRevenue = $amount * 0.3;
    
    $conn->query("UPDATE payment SET 
        admin_revenue = '$adminRevenue',
        instructor_revenue = '$instructorRevenue'
        WHERE id = $paymentId");
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Оплата успешно проведена!']);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}

$conn->close();
?>
