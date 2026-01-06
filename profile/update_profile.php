<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php?error=Silakan login dulu.");
  exit;
}

$user_id = (int)$_SESSION['user_id'];
$APP_BASE = '/IMK/';

// CSRF (jika Anda pakai)
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
  header("Location: /IMK/profile/index.php?error=Sesi tidak valid. Silakan refresh.");
  exit;
}

// Pastikan ada file
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
  header("Location: /IMK/profile/index.php?error=Pilih foto terlebih dahulu.");
  exit;
}

// Tangani error upload PHP
if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
  $err = $_FILES['photo']['error'];
  header("Location: /IMK/profile/index.php?error=Upload gagal (kode: {$err}).");
  exit;
}

$tmpPath = $_FILES['photo']['tmp_name'];
$size    = (int)$_FILES['photo']['size'];

// Validasi ukuran (contoh 2MB)
$maxSize = 2 * 1024 * 1024;
if ($size > $maxSize) {
  header("Location: /IMK/profile/index.php?error=Ukuran foto maksimal 2MB.");
  exit;
}

// Validasi MIME (lebih aman pakai finfo)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($tmpPath);

$allowed = [
  'image/jpeg' => 'jpg',
  'image/png'  => 'png',
  'image/webp' => 'webp',
];

if (!isset($allowed[$mime])) {
  header("Location: /IMK/profile/index.php?error=Format file harus JPG/PNG/WEBP.");
  exit;
}

$ext = $allowed[$mime];

// Folder tujuan (di dalam IMK/)
$uploadDir = __DIR__ . '/../uploads/avatars/';
if (!is_dir($uploadDir)) {
  // buat folder jika belum ada
  if (!mkdir($uploadDir, 0775, true)) {
    header("Location: /IMK/profile/index.php?error=Folder upload tidak tersedia.");
    exit;
  }
}

// Ambil foto lama untuk opsi hapus
$oldPhoto = '';
$stmt = mysqli_prepare($conn, "SELECT photo FROM users WHERE user_id=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($res)) {
  $oldPhoto = $row['photo'] ?? '';
}
mysqli_stmt_close($stmt);

// Nama file baru (unik)
$newName = 'u' . $user_id . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

// Simpan path RELATIF yang konsisten dengan sidebar Anda
// => nanti URL jadi /IMK/uploads/avatars/xxx.jpg
$newRelPath = 'uploads/avatars/' . $newName;
$destPath   = $uploadDir . $newName;

// Pindahkan file
if (!move_uploaded_file($tmpPath, $destPath)) {
  header("Location: /IMK/profile/index.php?error=Gagal menyimpan file ke server.");
  exit;
}

// Update DB
$upd = mysqli_prepare($conn, "UPDATE users SET photo=? WHERE user_id=?");
mysqli_stmt_bind_param($upd, 'si', $newRelPath, $user_id);
$ok = mysqli_stmt_execute($upd);
mysqli_stmt_close($upd);

if (!$ok) {
  // rollback: hapus file baru kalau DB gagal
  @unlink($destPath);
  header("Location: /IMK/profile/index.php?error=Gagal menyimpan ke database.");
  exit;
}

// Update session agar sidebar langsung berubah
$_SESSION['photo'] = $newRelPath;

// Opsional: hapus foto lama (hanya jika berada di uploads/avatars/)
if (!empty($oldPhoto)) {
  $old = ltrim($oldPhoto, '/');
  if (str_starts_with($old, 'uploads/avatars/')) {
    $oldFs = __DIR__ . '/../' . $old;
    if (is_file($oldFs)) @unlink($oldFs);
  }
}

header("Location: /IMK/profile/index.php?success=Foto profil berhasil diperbarui.");
exit;
