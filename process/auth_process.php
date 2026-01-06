<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header("Location: ../login.php?error=Email dan password wajib diisi.");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT user_id, username, password FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);

if (!$user) {
    header("Location: ../login.php?error=Email tidak ditemukan.");
    exit;
}

if ($password !== $user['password']) {
    header("Location: ../login.php?error=Password salah.");
    exit;
}

$_SESSION['user_id']  = (int)$user['user_id'];
$_SESSION['username'] = $user['username'];

header("Location: ../dashboard/index.php");
exit;
