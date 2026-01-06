<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$question_id = $_POST['question_id'] ?? null;
$content = trim($_POST['content'] ?? '');

if (!$question_id || !is_numeric($question_id)) {
    die('Question ID tidak valid.');
}

if ($content === '') {
    die('Jawaban tidak boleh kosong.');
}

$user_id = (int)$_SESSION['user_id'];
$question_id = (int)$question_id;

// pastikan question ada (hindari FK error)
$stmtC = mysqli_prepare($conn, "SELECT question_id FROM questions WHERE question_id=? LIMIT 1");
mysqli_stmt_bind_param($stmtC, 'i', $question_id);
mysqli_stmt_execute($stmtC);
mysqli_stmt_store_result($stmtC);

if (mysqli_stmt_num_rows($stmtC) === 0) {
    die('Pertanyaan tidak ditemukan.');
}

$stmt = mysqli_prepare($conn,
    "INSERT INTO answers (question_id, user_id, content) VALUES (?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, 'iis', $question_id, $user_id, $content);
mysqli_stmt_execute($stmt);

header("Location: ../discussion/detail.php?question_id=$question_id");
exit;
