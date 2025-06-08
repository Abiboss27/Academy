<?php
require 'database.php';

// Функция для редиректа
function redirect($url) {
    header("Location: $url");
    exit();
}

// Функция для получения всех курсов
function getAllCourses() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM course ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Функция для получения курса по ID
function getCourseById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM course WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Функция для получения секций курса
function getCourseSections($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM section WHERE course_id = ? ORDER BY `order` ASC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Функция для получения уроков секции
function getSectionLessons($section_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM lesson WHERE section_id = ? ORDER BY id ASC");
    $stmt->execute([$section_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Функция для загрузки файла
function uploadFile($file, $target_dir) {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($file["name"]);
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Генерация уникального имени файла
    $new_filename = uniqid() . '.' . $fileType;
    $new_target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $new_target_file)) {
        return $new_filename;
    }
    return false;
}