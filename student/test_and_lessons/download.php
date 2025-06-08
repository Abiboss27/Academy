<?php
// download.php

if (!isset($_GET['file'])) {
    die('Файл не указан.');
}

$file = basename($_GET['file']); // защита от обхода директорий
$filepath ='C:/xampp/htdocs/Academy/uploads/'.$file; // путь к папке с файлами

if (!file_exists($filepath)) {
    die('Файл не найден.');
}

// Отдаём файл
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
