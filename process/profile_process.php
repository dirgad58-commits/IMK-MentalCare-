<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../profile/index.php?error=Metode tidak valid.");
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$bio       = trim($_POST['bio'] ?? '');

if ($full_name !== '' && mb_strlen($full_name) > 100) {
    header("Location: ../profile/edit.php?error=Nama terlalu panjang.");
    exit;
}
if ($phone !== '' && mb_strlen($phone) > 20) {
    header("Location: ../profile/edit.php?error=Nomor HP terlalu panjang.");
    exit;
}

/* =========================
   Ambil foto lama (jika ada)
========================= */
$oldPhoto = null;
$stmtOld = mysqli_prepare($conn, "SELECT photo FROM users WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmtOld, 'i', $user_id);
mysqli_stmt_execute($stmtOld);
$resOld = mysqli_stmt_get_result($stmtOld);
$rowOld = mysqli_fetch_assoc($resOld);
if ($rowOld && !empty($rowOld['photo'])) {
    $oldPhoto = $rowOld['photo'];
}

/* =========================
   Upload foto (opsional)
========================= */
$newPhotoPath = $oldPhoto;

if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        header("Location: ../profile/edit.php?error=Gagal upload foto.");
        exit;
    }

    // Maks 2MB
    if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
        header("Location: ../profile/edit.php?error=Ukuran foto maksimal 2MB.");
        exit;
    }

    $tmpName = $_FILES['photo']['tmp_name'];

    // Validasi MIME (lebih aman)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        header("Location: ../profile/edit.php?error=Format foto harus JPG/PNG/WebP.");
        exit;
    }

    $ext = $allowed[$mime];

    // Pastikan folder ada
    $uploadDir = __DIR__ . '/../assets/images/avatars/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    // Nama file unik
    $filename = 'u' . $user_id . '_' . date('Ymd_His') . '.' . $ext;
    $target   = $uploadDir . $filename;

    if (!move_uploaded_file($tmpName, $target)) {
        header("Location: ../profile/edit.php?error=Tidak bisa menyimpan foto.");
        exit;
    }

    // Simpan path relatif (dipakai untuk src gambar)
    $newPhotoPath = 'assets/images/avatars/' . $filename;

    // Hapus foto lama jika sebelumnya ada & bukan file yang sama
    if ($oldPhoto && $oldPhoto !== $newPhotoPath) {
        $oldAbs = __DIR__ . '/../' . ltrim($oldPhoto, '/');
        if (is_file($oldAbs)) {
            @unlink($oldAbs);
        }
    }
}

/* =========================
   Update profil
========================= */
$stmt = mysqli_prepare(
    $conn,
    "UPDATE users
     SET full_name = ?, phone = ?, bio = ?, photo = ?, updated_at = NOW()
     WHERE user_id = ?"
);

mysqli_stmt_bind_param($stmt, 'ssssi', $full_name, $phone, $bio, $newPhotoPath, $user_id);

if (!mysqli_stmt_execute($stmt)) {
    header("Location: ../profile/edit.php?error=Gagal menyimpan profil.");
    exit;
}

// (Opsional) Update session username jika user ganti username (di sistem Anda tidak ada edit username)
// Jadi tidak diperlukan.

header("Location: ../profile/index.php?success=Profil berhasil diperbarui.");
exit;
