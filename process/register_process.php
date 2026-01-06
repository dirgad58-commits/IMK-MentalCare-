<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../register.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

if ($username === '' || $email === '' || $password === '' || $confirm === '') {
    $msg = "Semua field wajib diisi.";
    header("Location: ../register.php?error=" . urlencode($msg) . "&u=" . urlencode($username) . "&e=" . urlencode($email));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $msg = "Format email tidak valid.";
    header("Location: ../register.php?error=" . urlencode($msg) . "&u=" . urlencode($username) . "&e=" . urlencode($email));
    exit;
}

if ($password !== $confirm) {
    $msg = "Password dan konfirmasi password tidak sama.";
    header("Location: ../register.php?error=" . urlencode($msg) . "&u=" . urlencode($username) . "&e=" . urlencode($email));
    exit;
}

// Cek email sudah dipakai atau belum (users.email UNIQUE di database Anda)
$stmtCek = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmtCek, 's', $email);
mysqli_stmt_execute($stmtCek);
$resCek = mysqli_stmt_get_result($stmtCek);

if (mysqli_fetch_assoc($resCek)) {
    $msg = "Email sudah terdaftar. Silakan login.";
    header("Location: ../register.php?error=" . urlencode($msg) . "&u=" . urlencode($username) . "&e=" . urlencode($email));
    exit;
}

// Insert user baru (sesuai tabel users Anda: username, email, password)
$stmtIns = mysqli_prepare(
    $conn,
    "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
);
mysqli_stmt_bind_param($stmtIns, 'sss', $username, $email, $password);

if (!mysqli_stmt_execute($stmtIns)) {
    $msg = "Gagal membuat akun. Coba lagi.";
    header("Location: ../register.php?error=" . urlencode($msg) . "&u=" . urlencode($username) . "&e=" . urlencode($email));
    exit;
}

// Auto-login setelah register
$new_user_id = mysqli_insert_id($conn);
$_SESSION['user_id']  = (int)$new_user_id;
$_SESSION['username'] = $username;

header("Location: ../dashboard/index.php");
exit;
