<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$title   = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$category = trim($_POST['category'] ?? '');
$is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;

if ($title === '' || $content === '') {
    die('Judul dan isi wajib diisi.');
}

$user_id = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn,
    "INSERT INTO questions (user_id, title, content, category, is_anonymous)
     VALUES (?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, 'isssi', $user_id, $title, $content, $category, $is_anonymous);
mysqli_stmt_execute($stmt);

$question_id = mysqli_insert_id($conn);
header("Location: ../discussion/detail.php?question_id=$question_id");
exit;
