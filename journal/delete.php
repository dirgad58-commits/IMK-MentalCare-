<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    mysqli_query(
        $conn,
        "DELETE FROM journals 
         WHERE journal_id = $id AND user_id = $user_id"
    );
}

header("Location: index.php");
exit;
