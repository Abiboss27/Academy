<?php  
include('database.php');  

function deleteRecord($conn, $tableName, $idField, $idValue, $redirectPage) {  
    $stmt = $conn->prepare("DELETE FROM $tableName WHERE $idField = ?");  
    if ($stmt) {  
        $stmt->bind_param("s", $idValue); // Assuming the IDs are strings; change to "i" for integers  
        if ($stmt->execute()) {  
            header("Location: $redirectPage");  
            exit;  
        } else {  
            echo "Error: " . $stmt->error;  
        }  
        $stmt->close();  
    } else {  
        echo "Error preparing statement: " . $conn->error;  
    }  
}  

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["id_Aflight"])) {  
        deleteRecord($conn, "actual_fligths", "id", $_POST["id_Aflight"], "flights_table.php");  
    }   
}  

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["id_Fday"])) {  
        deleteRecord($conn, "flights_days", "id", $_POST["id_Fday"], "tickets_table.php");  
    }   
}

$conn->close();  
?>