<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$APP_BASE = '/IMK';

function redirect_with($path, $params = []) {
  $qs = http_build_query($params);
  header("Location: {$path}" . ($qs ? "?{$qs}" : ""));
  exit;
}

if (!isset($_SESSION['user_id'])) {
  redirect_with($APP_BASE . "/login.php", ['error' => 'Silakan login dulu.']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect_with($APP_BASE . "/report/index.php", ['error' => 'Metode tidak valid.']);
}

/* CSRF */
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
  redirect_with($APP_BASE . "/report/index.php", ['error' => 'Token tidak valid.']);
}

/* Honeypot */
if (!empty($_POST['website'])) {
  redirect_with($APP_BASE . "/report/index.php", ['error' => 'Permintaan ditolak.']);
}

/* Rate limit (30 detik) */
$now = time();
$last = $_SESSION['report_last_submit'] ?? 0;
if (($now - (int)$last) < 30) {
  redirect_with($APP_BASE . "/report/index.php", ['error' => 'Terlalu cepat. Coba lagi beberapa saat.']);
}
$_SESSION['report_last_submit'] = $now;

/* Inputs */
$user_id = (int)$_SESSION['user_id'];

$reporter_name  = trim($_POST['reporter_name'] ?? '');
$reporter_email = trim($_POST['reporter_email'] ?? '');
$reason         = trim($_POST['reason'] ?? '');
$details        = trim($_POST['details'] ?? '');

$target_type = trim($_POST['target_type'] ?? 'other');
$target_id   = (int)($_POST['target_id'] ?? 0);
$target_url  = trim($_POST['target_url'] ?? '');

/* validate */
$allowedTypes = ['question','answer','journal','other'];
if (!in_array($target_type, $allowedTypes, true)) $target_type = 'other';

if ($reporter_name === '' || $reporter_email === '' || $reason === '' || $details === '') {
  redirect_with($APP_BASE . "/report/index.php", ['error' => 'Semua field wajib diisi (nama, email, alasan, detail).']);
}

if (!filter_var($reporter_email, FILTER_VALIDATE_EMAIL)) {
  redirect_with($APP_BASE . "/report/index.php", ['error' => 'Email tidak valid.']);
}

if ($target_id <= 0 && $target_url === '') {
  redirect_with($APP_BASE . "/report/index.php", ['error' => 'Isi minimal ID konten atau URL konten.']);
}

/* limit length */
if (mb_strlen($reporter_name) > 80) $reporter_name = mb_substr($reporter_name, 0, 80);
if (mb_strlen($reporter_email) > 120) $reporter_email = mb_substr($reporter_email, 0, 120);
if (mb_strlen($reason) > 150) $reason = mb_substr($reason, 0, 150);
if (mb_strlen($details) > 2000) $details = mb_substr($details, 0, 2000);
if (mb_strlen($target_url) > 255) $target_url = mb_substr($target_url, 0, 255);

$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
if ($ua && mb_strlen($ua) > 255) $ua = mb_substr($ua, 0, 255);

/* Insert */
$sql = "INSERT INTO content_reports
        (user_id, reporter_name, reporter_email, reason, details,
         target_type, target_id, target_url, status, ip_address, user_agent)
        VALUES (?,?,?,?,?,?,?,?, 'baru', ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
  redirect_with($APP_BASE . "/report/index.php", ['error' => 'Server error (prepare).']);
}

$target_id_db = $target_id > 0 ? $target_id : null;

mysqli_stmt_bind_param(
  $stmt,
  'issssssiss',
  $user_id,
  $reporter_name,
  $reporter_email,
  $reason,
  $details,
  $target_type,
  $target_id_db,
  $target_url,
  $ip,
  $ua
);

$ok = mysqli_stmt_execute($stmt);
if (!$ok) {
  redirect_with($APP_BASE . "/report/index.php", ['error' => 'Gagal mengirim laporan. Coba lagi.']);
}

redirect_with($APP_BASE . "/report/index.php", ['success' => 'Laporan berhasil dikirim. Terima kasih.']);
