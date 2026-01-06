<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$error   = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

$APP_BASE = '/IMK-MentalCare-/';

// CSRF token (kalau proses Anda mengecek token; aman walau belum dipakai)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT username, full_name, email, phone, bio, photo
     FROM users
     WHERE user_id = ?
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res  = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$user) {
    header("Location: {$APP_BASE}profile/index.php?error=Akun tidak ditemukan.");
    exit;
}

/**
 * Foto URL + cache busting supaya setelah update langsung berubah.
 */
$photoUrl = '';
if (!empty($user['photo'])) {
    $rel = ltrim($user['photo'], '/');
    $fs  = __DIR__ . '/../' . $rel; // contoh: /IMK-MentalCare-/uploads/avatars/xxx.jpg

    if (is_file($fs)) {
        $v = @filemtime($fs) ?: time();
        $photoUrl = $APP_BASE . $rel . '?v=' . $v;
    } else {
        $photoUrl = $APP_BASE . $rel; // fallback kalau file check tidak cocok
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-background"></div>

<div class="container-fluid">
  <div class="row">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4">

      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
          <h3 class="fw-bold mb-1">Edit Profil</h3>
          <p class="text-muted mb-0">Perbarui foto, data diri, dan bio Anda.</p>
        </div>

        <!-- FIX: link harus sesuai base /IMK-MentalCare-/ -->
        <a href="<?= $APP_BASE; ?>profile/index.php" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <div class="card shadow-sm" style="border-radius:18px;">
        <div class="card-body">

          <!-- FIX UTAMA: action harus /IMK-MentalCare-/process/... (bukan ../process/...) -->
          <form action="<?= $APP_BASE; ?>process/profile_process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

            <!-- Batasi ukuran via browser (tetap wajib validasi di server) -->
            <input type="hidden" name="MAX_FILE_SIZE" value="<?= 2 * 1024 * 1024; ?>">

            <div class="row g-4">

              <div class="col-lg-4">
                <div class="p-3 rounded-4 border bg-white h-100">
                  <div class="fw-semibold mb-2">Foto Profil</div>

                  <div class="mb-3">
                    <?php if ($photoUrl): ?>
                      <img
                        src="<?= htmlspecialchars($photoUrl); ?>"
                        alt="Foto Profil"
                        style="width:100%;max-width:240px;aspect-ratio:1/1;border-radius:18px;object-fit:cover;border:1px solid rgba(0,0,0,.08);"
                      >
                    <?php else: ?>
                      <div
                        style="width:240px;max-width:100%;aspect-ratio:1/1;border-radius:18px;
                               background:linear-gradient(135deg,#00b7b5,#018790);
                               color:#fff;display:flex;align-items:center;justify-content:center;
                               font-weight:800;font-size:44px;">
                        <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                      </div>
                    <?php endif; ?>
                  </div>

                  <label class="form-label text-muted small">
                    Upload foto baru (JPG/PNG/WebP, max 2MB)
                  </label>

                  <!-- accept pakai MIME agar lebih konsisten -->
                  <input
                    type="file"
                    name="photo"
                    class="form-control"
                    accept="image/jpeg,image/png,image/webp"
                  >

                  <div class="text-muted small mt-2">
                    Tips: gunakan foto 1:1 agar hasil rapi.
                  </div>
                </div>
              </div>

              <div class="col-lg-8">
                <div class="row g-3">

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Lengkap</label>
                    <input
                      type="text"
                      name="full_name"
                      class="form-control form-control-lg"
                      value="<?= htmlspecialchars($user['full_name'] ?? ''); ?>"
                      placeholder="Contoh: La Ode Muhamad Dirga"
                      maxlength="120"
                    >
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Nomor HP</label>
                    <input
                      type="text"
                      name="phone"
                      class="form-control form-control-lg"
                      value="<?= htmlspecialchars($user['phone'] ?? ''); ?>"
                      placeholder="Contoh: 08xxxxxxxxxx"
                      maxlength="25"
                    >
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Username</label>
                    <input
                      type="text"
                      class="form-control form-control-lg"
                      value="<?= htmlspecialchars($user['username']); ?>"
                      disabled
                    >
                    <div class="form-text">Username belum disediakan untuk diubah.</div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input
                      type="text"
                      class="form-control form-control-lg"
                      value="<?= htmlspecialchars($user['email']); ?>"
                      disabled
                    >
                    <div class="form-text">Email belum disediakan untuk diubah.</div>
                  </div>

                  <div class="col-12">
                    <label class="form-label fw-semibold">Bio</label>
                    <textarea
                      name="bio"
                      rows="5"
                      class="form-control form-control-lg"
                      placeholder="Ceritakan singkat tentang Anda (opsional)"
                      maxlength="1000"
                    ><?= htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                  </div>

                  <div class="col-12 d-flex gap-2 justify-content-end">
                    <a href="<?= $APP_BASE; ?>profile/index.php" class="btn btn-outline-secondary btn-lg">Batal</a>
                    <button type="submit" class="btn btn-brand btn-lg">
                      <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                  </div>

                </div>
              </div>

            </div>
          </form>

        </div>
      </div>

    </main>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
