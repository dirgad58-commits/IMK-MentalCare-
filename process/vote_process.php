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
    die('Data vote tidak valid.');
}

$user_id  = (int)$_SESSION['user_id'];
$answer_id = (int)$answer_id;
$question_id = (int)$question_id;

// Unique(answer_id,user_id) sudah ada → gunakan INSERT IGNORE
$stmt = mysqli_prepare($conn,
    "INSERT IGNORE INTO answer_votes (answer_id, user_id, vote_type) VALUES (?, ?, 'upvote')"
);
mysqli_stmt_bind_param($stmt, 'ii', $answer_id, $user_id);
mysqli_stmt_execute($stmt);

header("Location: ../discussion/detail.php?question_id=$question_id");
exit;
