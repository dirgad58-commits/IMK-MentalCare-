<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$question_id = (int)$_POST['question_id'];
$content = trim($_POST['content']);

if ($content === '') {
    header("Location: detail.php?id=$question_id");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$content = mysqli_real_escape_string($conn, $content);

mysqli_query($conn,
    "INSERT INTO answers (question_id,user_id,content)
     VALUES ($question_id,$user_id,'$content')"
);

header("Location: detail.php?id=$question_id");
exit;
