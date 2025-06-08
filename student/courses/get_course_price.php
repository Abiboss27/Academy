<?php
require 'C:/xampp/htdocs/Academy/database.php';

if (!isset($_GET['course_id'])) {
    exit; // Без course_id не продолжаем
}

$course_id = (int)$_GET['course_id'];

$stmt = $conn->prepare("SELECT id, title, price, discount FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $id = (int)$row['id'];
    $title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');

    $price = (float)$row['price'];
    $discount = (float)$row['discount'];
    $finalPrice = $price - $discount;

    echo "<option value='{$id}' data-price='{$finalPrice}' selected>{$title}</option>";
}

$stmt->close();
$conn->close();
?>
