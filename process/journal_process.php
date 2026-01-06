<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$title = trim($_POST['title'] ?? '');
$mood  = $_POST['mood'] ?? 'netral';
$content = trim($_POST['content'] ?? '');

if ($content === '') {
    die('Isi jurnal tidak boleh kosong.');
}

$user_id = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn,
  "INSERT INTO journals (user_id, title, content, mood) VALUES (?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, 'isss', $user_id, $title, $content, $mood);
mysqli_stmt_execute($stmt);

header("Location: ../journal/index.php");
exit;
