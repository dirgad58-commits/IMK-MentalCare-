<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/** UBAH jika kolom password Anda bernama password_hash */
$PASSWORD_COL = 'password';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?tab=account&error=Metode tidak valid.");
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    header("Location: index.php?tab=account&error=Token tidak valid.");
    exit;
}

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$pass     = trim($_POST['current_password'] ?? '');

if ($username === '' || $email === '' || $pass === '') {
    header("Location: index.php?tab=account&error=Semua field wajib diisi.");
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
    header("Location: index.php?tab=account&error=Username tidak valid. Gunakan huruf/angka/underscore 3–30.");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.php?tab=account&error=Email tidak valid.");
    exit;
}

// ambil password hash & current username/email
$stmt = mysqli_prepare($conn, "SELECT username, email, {$PASSWORD_COL} AS pass_hash FROM users WHERE user_id=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$me = mysqli_fetch_assoc($res);

if (!$me) {
    header("Location: index.php?tab=account&error=Akun tidak ditemukan.");
    exit;
}

if (!password_verify($pass, $me['pass_hash'])) {
    header("Location: index.php?tab=account&error=Password konfirmasi salah.");
    exit;
}

// cek unik username
$stmtU = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username=? AND user_id<>? LIMIT 1");
mysqli_stmt_bind_param($stmtU, 'si', $username, $user_id);
mysqli_stmt_execute($stmtU);
$rU = mysqli_stmt_get_result($stmtU);
if (mysqli_fetch_assoc($rU)) {
    header("Location: index.php?tab=account&error=Username sudah dipakai.");
    exit;
}

// cek unik email
$stmtE = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email=? AND user_id<>? LIMIT 1");
mysqli_stmt_bind_param($stmtE, 'si', $email, $user_id);
mysqli_stmt_execute($stmtE);
$rE = mysqli_stmt_get_result($stmtE);
if (mysqli_fetch_assoc($rE)) {
    header("Location: index.php?tab=account&error=Email sudah dipakai.");
    exit;
}

// update
$stmtUp = mysqli_prepare($conn, "UPDATE users SET username=?, email=?, updated_at=NOW() WHERE user_id=? LIMIT 1");
mysqli_stmt_bind_param($stmtUp, 'ssi', $username, $email, $user_id);

if (!mysqli_stmt_execute($stmtUp)) {
    header("Location: index.php?tab=account&error=Gagal memperbarui akun.");
    exit;
}

// update session username (jika Anda pakai di navbar)
$_SESSION['username'] = $username;

header("Location: index.php?tab=account&success=Akun berhasil diperbarui.");
exit;
