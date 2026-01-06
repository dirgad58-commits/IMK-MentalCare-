<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$answer_id   = $_POST['answer_id'] ?? null;
$question_id = $_POST['question_id'] ?? null;

if (!$answer_id || !is_numeric($answer_id) || !$question_id || !is_numeric($question_id)) {
    die('Data tidak valid.');
}

$user_id = (int)$_SESSION['user_id'];
$answer_id = (int)$answer_id;
$question_id = (int)$question_id;

// cek pemilik pertanyaan
$stmt = mysqli_prepare($conn, "SELECT user_id FROM questions WHERE question_id=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $question_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$q = mysqli_fetch_assoc($res);

if (!$q || (int)$q['user_id'] !== $user_id) {
    die('Anda tidak berhak mengubah jawaban terbaik.');
}

// pastikan answer milik question
$stmt2 = mysqli_prepare($conn, "SELECT answer_id FROM answers WHERE answer_id=? AND question_id=? LIMIT 1");
mysqli_stmt_bind_param($stmt2, 'ii', $answer_id, $question_id);
mysqli_stmt_execute($stmt2);
mysqli_stmt_store_result($stmt2);

if (mysqli_stmt_num_rows($stmt2) === 0) {
    die('Jawaban tidak ditemukan.');
}

// reset semua is_best=0, lalu set yang dipilih is_best=1
mysqli_begin_transaction($conn);

$stmtR = mysqli_prepare($conn, "UPDATE answers SET is_best=0 WHERE question_id=?");
mysqli_stmt_bind_param($stmtR, 'i', $question_id);
mysqli_stmt_execute($stmtR);

$stmtS = mysqli_prepare($conn, "UPDATE answers SET is_best=1 WHERE answer_id=?");
mysqli_stmt_bind_param($stmtS, 'i', $answer_id);
mysqli_stmt_execute($stmtS);

mysqli_commit($conn);

header("Location: ../discussion/detail.php?question_id=$question_id");
exit;
