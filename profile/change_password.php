<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/**
 * Sesuaikan nama kolom password pada tabel users:
 * - Jika kolom Anda bernama `password`, pakai 'password'
 * - Jika kolom Anda bernama `password_hash`, pakai 'password_hash'
 */
$passwordColumn = 'password'; // <-- UBAH jika perlu

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?error=Metode tidak valid.");
    exit;
}

// CSRF sederhana (opsional tapi direkomendasikan)
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    header("Location: index.php?error=Token tidak valid. Silakan coba lagi.");
    exit;
}

$current = trim($_POST['current_password'] ?? '');
$new     = trim($_POST['new_password'] ?? '');
$confirm = trim($_POST['confirm_password'] ?? '');

if ($current === '' || $new === '' || $confirm === '') {
    header("Location: index.php?error=Semua field password wajib diisi.");
    exit;
}

if ($new !== $confirm) {
    header("Location: index.php?error=Konfirmasi password tidak cocok.");
    exit;
}

// Validasi minimal (silakan perketat jika mau)
if (strlen($new) < 8) {
    header("Location: index.php?error=Password baru minimal 8 karakter.");
    exit;
}

// Ambil hash password lama
$sql = "SELECT {$passwordColumn} AS pass_hash FROM users WHERE user_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);

if (!$row || empty($row['pass_hash'])) {
    header("Location: index.php?error=Data akun tidak valid.");
    exit;
}

$oldHash = $row['pass_hash'];

// Verifikasi password lama
if (!password_verify($current, $oldHash)) {
    header("Location: index.php?error=Password saat ini salah.");
    exit;
}

// Cegah pakai password lama lagi
if (password_verify($new, $oldHash)) {
    header("Location: index.php?error=Password baru tidak boleh sama dengan password lama.");
    exit;
}

// Hash password baru
$newHash = password_hash($new, PASSWORD_DEFAULT);

// Update password + updated_at
$update = "UPDATE users SET {$passwordColumn} = ?, updated_at = NOW() WHERE user_id = ? LIMIT 1";
$stmtU = mysqli_prepare($conn, $update);
mysqli_stmt_bind_param($stmtU, 'si', $newHash, $user_id);

if (!mysqli_stmt_execute($stmtU)) {
    header("Location: index.php?error=Gagal mengubah password. Coba lagi.");
    exit;
}

// Security: regenerate session id setelah ganti password
session_regenerate_id(true);

header("Location: index.php?success=Password berhasil diubah.");
exit;
