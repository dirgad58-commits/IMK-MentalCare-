<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$BASE_URL = '/IMK-MentalCare-/';

function redirect_with(string $msgKey, string $msg, string $anchor = 'kontak') {
    $q = http_build_query([$msgKey => $msg]);
    header("Location: {$GLOBALS['BASE_URL']}index.php?{$q}#{$anchor}");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with('contact_error', 'Metode tidak valid.');
}

$csrf = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    redirect_with('contact_error', 'Sesi tidak valid. Silakan refresh halaman dan coba lagi.');
}

// honeypot
if (!empty($_POST['website'] ?? '')) {
    redirect_with('contact_success', 'Pesan diterima.');
}

$type = $_POST['type'] ?? 'pesan';
if ($type !== 'pesan') {
    redirect_with('contact_error', 'Tipe form tidak valid.');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    redirect_with('contact_error', 'Mohon lengkapi nama, email, dan pesan.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with('contact_error', 'Format email tidak valid.');
}
if (mb_strlen($name) > 80 || mb_strlen($email) > 120 || mb_strlen($message) > 2000) {
    redirect_with('contact_error', 'Panjang input melebihi batas.');
}

// Simpan ke DB (tabel: contact_messages)
$stmt = mysqli_prepare($conn, "INSERT INTO contact_messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $message);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) redirect_with('contact_success', 'Terima kasih. Pesan Anda sudah terkirim.');
}

redirect_with('contact_error', 'Gagal menyimpan pesan. Pastikan tabel sudah dibuat (lihat file SQL).');
