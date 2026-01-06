<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$answer_id   = (int)$_POST['answer_id'];
$question_id = (int)$_POST['question_id'];
$user_id     = (int)$_SESSION['user_id'];

$q = mysqli_query($conn,
    "SELECT user_id FROM questions WHERE question_id=$question_id"
);
$data = mysqli_fetch_assoc($q);

if ($data && $data['user_id'] == $user_id) {
    mysqli_query($conn, "UPDATE answers SET is_best=0 WHERE question_id=$question_id");
    mysqli_query($conn, "UPDATE answers SET is_best=1 WHERE answer_id=$answer_id");
}

header("Location: detail.php?id=$question_id");
exit;
