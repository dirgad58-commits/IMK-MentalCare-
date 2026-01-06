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

mysqli_query($conn,
    "INSERT IGNORE INTO answer_votes (answer_id,user_id)
     VALUES ($answer_id,$user_id)"
);

header("Location: detail.php?id=$question_id");
exit;
