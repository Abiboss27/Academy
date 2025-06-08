<?php  
require('database.php');  
session_start();  

if (isset($_SESSION['id_worker'])) {  
    $id_worker = $_SESSION['id_worker'];  
    session_unset();  
    session_destroy();  

    // Prevent caching of the page  
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");  
    header("Cache-Control: post-check=0, pre-check=0", false);  
    header("Expires: 0");  
    header("Location: login.php");  
    exit();  
} else {  
    // Optional: Redirect to login if no session is found  
    header("Location: login.php");  
    exit();  
}  
?>