<?php
require_once __DIR__ . '/db.php';

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    global $db;
    $stmt = $db->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function is_teacher() {
    $user = current_user();
    return $user && $user['role'] === 'teacher';
}

function is_student() {
    $user = current_user();
    return $user && $user['role'] === 'student';
}

function is_admin() {
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

function redirect_home() {
    header('Location: dashboard.php');
    exit;
}

function calculate_grade_from_mark($mark) {
    $mark = (int) $mark;

    if ($mark >= 90) {
        return 'A';
    }
    if ($mark >= 80) {
        return 'B';
    }
    if ($mark >= 70) {
        return 'C';
    }
    if ($mark >= 60) {
        return 'D';
    }

    return 'F';
}

function grade_description($grade) {
    $grades = [
        'A' => 'Excellent',
        'B' => 'Very Good',
        'C' => 'Good',
        'D' => 'Fair',
        'F' => 'Fail'
    ];

    return $grades[$grade] ?? 'Unknown';
}
?>
