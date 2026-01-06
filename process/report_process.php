<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$BASE_URL = '/IMK/';

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
    redirect_with('contact_success', 'Laporan diterima.');
}

$type = $_POST['type'] ?? '';
if ($type !== 'laporan_konten') {
    redirect_with('contact_error', 'Tipe form tidak valid.');
}

$reporter_name  = trim($_POST['reporter_name'] ?? '');
$reporter_email = trim($_POST['reporter_email'] ?? '');
$target_type    = trim($_POST['target_type'] ?? 'other');
$target_id      = trim((string)($_POST['target_id'] ?? ''));
$target_url     = trim($_POST['target_url'] ?? '');
$reason         = trim($_POST['reason'] ?? '');
$details        = trim($_POST['details'] ?? '');

$allowedTypes = ['question','answer','journal','other'];
if (!in_array($target_type, $allowedTypes, true)) $target_type = 'other';

if ($reporter_name === '' || $reporter_email === '' || $reason === '' || $details === '') {
    redirect_with('contact_error', 'Mohon lengkapi nama, email, alasan, dan detail laporan.');
}
if (!filter_var($reporter_email, FILTER_VALIDATE_EMAIL)) {
    redirect_with('contact_error', 'Format email tidak valid.');
}

// HCI: pencegahan error — minimal salah satu ID/URL wajib
if ($target_id === '' && $target_url === '') {
    redirect_with('contact_error', 'Isi minimal salah satu: ID Konten atau URL.');
}

$target_id_int = null;
if ($target_id !== '') {
    if (!ctype_digit($target_id)) {
        redirect_with('contact_error', 'ID konten harus berupa angka.');
    }
    $target_id_int = (int)$target_id;
}

if (mb_strlen($reporter_name) > 80 || mb_strlen($reporter_email) > 120) {
    redirect_with('contact_error', 'Panjang input melebihi batas.');
}
if (mb_strlen($target_url) > 255 || mb_strlen($reason) > 150 || mb_strlen($details) > 2000) {
    redirect_with('contact_error', 'Panjang input melebihi batas.');
}

// Simpan ke DB (tabel: content_reports)
$stmt = mysqli_prepare($conn, "
  INSERT INTO content_reports
  (reporter_name, reporter_email, target_type, target_id, target_url, reason, details, status, created_at)
  VALUES (?, ?, ?, ?, ?, ?, ?, 'new', NOW())
");

if ($stmt) {
    // bind: s s s i s s s  (target_id nullable -> pakai variable int + check)
    $tid = $target_id_int ?? 0;
    mysqli_stmt_bind_param($stmt, 'sssisss', $reporter_name, $reporter_email, $target_type, $tid, $target_url, $reason, $details);

    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        redirect_with('contact_success', 'Terima kasih. Laporan Anda sudah diterima.');
    }
}

redirect_with('contact_error', 'Gagal menyimpan laporan. Pastikan tabel sudah dibuat (lihat file SQL).');
