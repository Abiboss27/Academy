<?php
// update_role.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_teacher'])) {
    require_once __DIR__ . '/../database.php';
    $stmt = $conn->prepare("UPDATE users SET id_role = 3 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    // Set parameters and execute
    $user_id = $_POST['user_id'];
    $stmt->execute();
    
    // Close statement and connection
    $stmt->close();
    $conn->close();
    
    // Redirect back to the students page
    header("Location: stud_table.php");
    exit();
} else {
    header("Location: change.php");
    exit();
}
?>