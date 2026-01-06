<?php
require_once '../config/database.php';
require_once '../config/session.php';

/* =========================
   VALIDASI LOGIN
========================= */
if (!isset($_SESSION['user_id'])) {
    die('Anda harus login untuk menjawab.');
}

/* =========================
   VALIDASI INPUT
========================= */
$question_id = $_POST['question_id'] ?? null;
$content     = $_POST['content'] ?? null;

if (!$question_id || !is_numeric($question_id)) {
    die('Question ID tidak valid.');
}

$content = trim($content);
if ($content === '') {
    die('Jawaban tidak boleh kosong.');
}

$question_id = (int) $question_id;
$user_id     = (int) $_SESSION['user_id'];

/* =========================
   CEK PERTANYAAN ADA
========================= */
$stmtCheck = mysqli_prepare(
    $conn,
    "SELECT question_id FROM questions WHERE question_id = ?"
);
mysqli_stmt_bind_param($stmtCheck, 'i', $question_id);
mysqli_stmt_execute($stmtCheck);
mysqli_stmt_store_result($stmtCheck);

if (mysqli_stmt_num_rows($stmtCheck) === 0) {
    die('Pertanyaan tidak ditemukan.');
}

/* =========================
   SIMPAN JAWABAN
========================= */
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO answers (question_id, user_id, content)
     VALUES (?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, 'iis', $question_id, $user_id, $content);
mysqli_stmt_execute($stmt);

/* =========================
   REDIRECT
========================= */
header("Location: ../discussion/detail.php?question_id=$question_id");
exit;
