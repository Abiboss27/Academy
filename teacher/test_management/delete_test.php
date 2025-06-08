<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'C:/xampp/htdocs/Academy/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location:../../login.html");
    exit();
}

$user_id = $_SESSION['id_user'];

$test_id = $_GET['test_id'] ?? 0;

if ($test_id) {
    // First delete all questions for this test
    $stmt = $conn->prepare("DELETE FROM questions WHERE id_test = ?");
    $stmt->bind_param("i", $test_id);
    $stmt->execute();
    $stmt->close();
    
    // Then delete the test
    $stmt = $conn->prepare("DELETE FROM tests WHERE id = ?");
    $stmt->bind_param("i", $test_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: index.php");
exit();
?>