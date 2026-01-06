<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$APP_BASE = '/IMK'; // sesuaikan jika folder project bukan IMK
$user_id  = (int)$_SESSION['user_id'];

$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';
$tab     = $_GET['tab'] ?? 'overview';

// CSRF token (global untuk halaman ini)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function scalar($conn, $sql, $types = '', $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_row($res);
    return $row ? $row[0] : 0;
}

function fetch_all($conn, $sql, $types = '', $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    return $rows;
}

function fetch_one($conn, $sql, $types = '', $params = []) {
    $rows = fetch_all($conn, $sql, $types, $params);
    return $rows ? $rows[0] : null;
}

/* ============ DATA USER ============ */
$user = fetch_one($conn,
    "SELECT user_id, username, full_name, email, phone, bio, photo, created_at, updated_at
     FROM users
     WHERE user_id = ?
     LIMIT 1",
    'i',
    [$user_id]
);

if (!$user) {
    header("Location: ../login.php?error=Akun tidak ditemukan.");
    exit;
}

/* ============ STAT USER ============ */
$stats = fetch_one($conn,
  "SELECT
    (SELECT COUNT(*) FROM questions WHERE user_id = ?) AS total_questions,
    (SELECT COUNT(*) FROM answers   WHERE user_id = ?) AS total_answers,
    (SELECT COUNT(*) FROM journals  WHERE user_id = ?) AS total_journals",
  'iii',
  [$user_id, $user_id, $user_id]
) ?: ['total_questions'=>0,'total_answers'=>0,'total_journals'=>0];

/* ============ ACTIVITY FEED (opsional) ============ */
$activity = fetch_all($conn,
  "(
      SELECT 'question' AS type, q.question_id AS id, q.title AS title, q.created_at AS at_time
      FROM questions q WHERE q.user_id = ?
   )
   UNION ALL
   (
      SELECT 'answer' AS type, a.answer_id AS id,
             CONCAT('Menjawab: ', COALESCE(q.title, 'Diskusi')) AS title, a.created_at AS at_time
      FROM answers a
      LEFT JOIN questions q ON q.question_id = a.question_id
      WHERE a.user_id = ?
   )
   UNION ALL
   (
      SELECT 'journal' AS type, j.journal_id AS id,
             COALESCE(j.title, 'Jurnal') AS title, j.created_at AS at_time
      FROM journals j WHERE j.user_id = ?
   )
   ORDER BY at_time DESC
   LIMIT 10",
  'iii',
  [$user_id, $user_id, $user_id]
);

/* ============ PHOTO URL ============ */
$photoUrl = '';
if (!empty($user['photo'])) {
    $photoUrl = $APP_BASE . '/' . ltrim($user['photo'], '/'); // simpan relative path di DB
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4 content-area">

      <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
        <div>
          <h3 class="fw-bold mb-1">Profil Saya</h3>
          <p class="text-muted mb-0">Kelola profil, akun, keamanan, dan ringkasan aktivitas.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <a class="btn btn-outline-primary" href="<?= $APP_BASE; ?>/dashboard/index.php">
            <i class="bi bi-house me-1"></i>Dashboard
          </a>
        </div>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <!-- PROFILE HEADER -->
      <div class="card shadow-sm mb-3" style="border-radius:18px;">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 flex-wrap">

            <div>
              <?php if ($photoUrl): ?>
                <img src="<?= htmlspecialchars($photoUrl); ?>"
                     alt="Foto Profil"
                     style="width:78px;height:78px;border-radius:22px;object-fit:cover;border:1px solid rgba(0,0,0,.08);">
              <?php else: ?>
                <div style="width:78px;height:78px;border-radius:22px;
                            background:linear-gradient(135deg,#00b7b5,#018790);
                            color:#fff;display:flex;align-items:center;justify-content:center;
                            font-weight:800;font-size:28px;">
                  <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="flex-grow-1">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="mb-0 fw-bold"><?= htmlspecialchars($user['full_name'] ?: $user['username']); ?></h5>
                <span class="badge rounded-pill text-bg-light border">
                  <i class="bi bi-person-badge me-1"></i>Pengguna
                </span>
              </div>

              <div class="text-muted">
                <span class="me-3"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($user['email']); ?></span>
                <?php if (!empty($user['phone'])): ?>
                  <span class="me-3"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($user['phone']); ?></span>
                <?php endif; ?>
              </div>

              <div class="text-muted small mt-1">
                Bergabung: <?= !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-'; ?>
                <?php if (!empty($user['updated_at'])): ?>
                  • Diperbarui: <?= date('d M Y H:i', strtotime($user['updated_at'])); ?>
                <?php endif; ?>
              </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
              <div class="px-3 py-2 rounded-4 border bg-white" style="min-width:130px;">
                <div class="text-muted small">Pertanyaan</div>
                <div class="fw-bold fs-4"><?= (int)$stats['total_questions']; ?></div>
              </div>
              <div class="px-3 py-2 rounded-4 border bg-white" style="min-width:130px;">
                <div class="text-muted small">Jawaban</div>
                <div class="fw-bold fs-4"><?= (int)$stats['total_answers']; ?></div>
              </div>
              <div class="px-3 py-2 rounded-4 border bg-white" style="min-width:130px;">
                <div class="text-muted small">Jurnal</div>
                <div class="fw-bold fs-4"><?= (int)$stats['total_journals']; ?></div>
              </div>
            </div>

          </div>

          <?php if (!empty($user['bio'])): ?>
            <hr>
            <div>
              <div class="text-muted small mb-1">Bio</div>
              <div><?= nl2br(htmlspecialchars($user['bio'])); ?></div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- TABS -->
      <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
          <button class="nav-link <?= $tab==='overview'?'active':''; ?>" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button">
            <i class="bi bi-grid me-1"></i>Overview
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link <?= $tab==='profile'?'active':''; ?>" data-bs-toggle="tab" data-bs-target="#tab-profile" type="button">
            <i class="bi bi-person-lines-fill me-1"></i>Edit Profil
          </button>
        </li>
      
        <li class="nav-item">
          <button class="nav-link <?= $tab==='security'?'active':''; ?>" data-bs-toggle="tab" data-bs-target="#tab-security" type="button">
            <i class="bi bi-shield-lock me-1"></i>Keamanan
          </button>
        </li>

      <div class="tab-content">

        <!-- OVERVIEW -->
        <div class="tab-pane fade <?= $tab==='overview'?'show active':''; ?>" id="tab-overview">
          <div class="row g-3">
            <div class="col-lg-7">
              <div class="card shadow-sm" style="border-radius:18px;">
                <div class="card-body">
                  <h5 class="fw-semibold mb-2"><i class="bi bi-activity me-2"></i>Aktivitas Terbaru</h5>

                  <?php if (empty($activity)): ?>
                    <div class="text-muted">Belum ada aktivitas untuk ditampilkan.</div>
                  <?php else: ?>
                    <div class="list-group list-group-flush">
                      <?php foreach ($activity as $a): ?>
                        <?php
                          $type = $a['type'];
                          $title = $a['title'];
                          $when = date('d M Y H:i', strtotime($a['at_time']));
                          if ($type === 'question') $url = $APP_BASE . "/discussion/detail.php?question_id=".(int)$a['id'];
                          elseif ($type === 'answer') $url = $APP_BASE . "/discussion/index.php";
                          else $url = $APP_BASE . "/journal/detail.php?journal_id=".(int)$a['id'];

                          $icon = $type === 'question' ? 'bi-chat-square-text' : ($type === 'answer' ? 'bi-reply' : 'bi-journal-text');
                        ?>
                        <a href="<?= htmlspecialchars($url); ?>" class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-start">
                          <div class="me-2">
                            <div class="fw-semibold"><i class="bi <?= $icon; ?> text-primary me-1"></i><?= htmlspecialchars($title); ?></div>
                            <div class="text-muted small"><?= $when; ?></div>
                          </div>
                          <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                </div>
              </div>
            </div>

            <div class="col-lg-5">
              <div class="card shadow-sm" style="border-radius:18px;">
                <div class="card-body">
                  <h5 class="fw-semibold mb-2"><i class="bi bi-lightbulb me-2"></i>Tip Personal</h5>
                  <div class="text-muted" style="font-size:.95rem">
                    Gunakan jurnal untuk mencatat pola: pemicu, emosi, dan langkah kecil yang membantu. Konsistensi 3–5 menit/hari sudah cukup.
                  </div>
                  <hr>
                  <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-primary" href="<?= $APP_BASE; ?>/journal/create.php"><i class="bi bi-journal-plus me-1"></i>Tulis Jurnal</a>
                    <a class="btn btn-outline-primary" href="<?= $APP_BASE; ?>/discussion/create.php"><i class="bi bi-plus-circle me-1"></i>Pertanyaan Baru</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- EDIT PROFIL -->
        <div class="tab-pane fade <?= $tab==='profile'?'show active':''; ?>" id="tab-profile">
          <div class="card shadow-sm" style="border-radius:18px;">
            <div class="card-body">
              <h5 class="fw-semibold mb-3"><i class="bi bi-person-lines-fill me-2"></i>Edit Profil</h5>

              <form method="post" action="update_profile.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="row g-3">
                  <div class="col-lg-4">
                    <div class="p-3 border rounded-4 bg-white">
                      <div class="fw-semibold mb-2">Foto Profil</div>

                      <div class="d-flex align-items-center gap-3">
                        <div>
                          <img id="avatarPreview"
                               src="<?= htmlspecialchars($photoUrl ?: ''); ?>"
                               alt=""
                               style="width:84px;height:84px;border-radius:24px;object-fit:cover;border:1px solid rgba(0,0,0,.08);<?= $photoUrl?'':'display:none;'; ?>">
                          <div id="avatarFallback"
                               style="width:84px;height:84px;border-radius:24px;
                                      background:linear-gradient(135deg,#00b7b5,#018790);
                                      color:#fff;display:<?= $photoUrl?'none':'flex'; ?>;
                                      align-items:center;justify-content:center;
                                      font-weight:800;font-size:28px;">
                            <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <input class="form-control" type="file" name="photo" id="photoInput" accept="image/png,image/jpeg,image/webp">
                          <div class="form-text">PNG/JPG/WEBP, max 2MB.</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-lg-8">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" name="full_name" class="form-control"
                               value="<?= htmlspecialchars($user['full_name'] ?? ''); ?>" maxlength="80">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">Nomor HP</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?= htmlspecialchars($user['phone'] ?? ''); ?>" maxlength="20"
                               placeholder="contoh: 08xxxxxxxxxx">
                      </div>
                      <div class="col-12">
                        <label class="form-label fw-semibold">Bio</label>
                        <textarea name="bio" class="form-control" rows="4" maxlength="500"
                                  placeholder="Tulis bio singkat..."><?= htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        <div class="form-text">Maksimal 500 karakter.</div>
                      </div>
                    </div>
                  </div>
                </div>

                <hr class="my-3">
                <button class="btn btn-primary" type="submit">
                  <i class="bi bi-save me-1"></i>Simpan Perubahan
                </button>
              </form>

            </div>
          </div>
        </div>

   

        <!-- SECURITY -->
        <div class="tab-pane fade <?= $tab==='security'?'show active':''; ?>" id="tab-security">
          <div class="card shadow-sm" style="border-radius:18px;">
            <div class="card-body">
              <h5 class="fw-semibold mb-3"><i class="bi bi-shield-lock me-2"></i>Ubah Password</h5>

              <form method="post" action="change_password.php" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="col-md-6">
                  <label class="form-label fw-semibold">Password Saat Ini</label>
                  <div class="input-group">
                    <input type="password" name="current_password" class="form-control" id="curPass" required>
                    <button class="btn btn-outline-secondary" type="button" data-toggle-pass="#curPass">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-semibold">Password Baru</label>
                  <div class="input-group">
                    <input type="password" name="new_password" class="form-control" id="newPass" minlength="8" required>
                    <button class="btn btn-outline-secondary" type="button" data-toggle-pass="#newPass">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                  <div class="form-text">Minimal 8 karakter.</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                  <div class="input-group">
                    <input type="password" name="confirm_password" class="form-control" id="confPass" minlength="8" required>
                    <button class="btn btn-outline-secondary" type="button" data-toggle-pass="#confPass">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>

                <div class="col-12">
                  <button class="btn btn-warning" type="submit">
                    <i class="bi bi-arrow-repeat me-1"></i>Update Password
                  </button>
                </div>
              </form>

              <hr class="my-3">
              <div class="p-3 rounded-4" style="background:rgba(255,193,7,.12);border:1px solid rgba(255,193,7,.25);">
                <div class="d-flex gap-2 align-items-start">
                  <i class="bi bi-info-circle"></i>
                  <div class="text-muted small">
                    Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol untuk password yang kuat.
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- DANGER ZONE -->
        <div class="tab-pane fade <?= $tab==='danger'?'show active':''; ?>" id="tab-danger">
          <div class="card border-danger" style="border-radius:18px;">
            <div class="card-body">
              <h5 class="fw-semibold mb-2 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Danger Zone</h5>
              <p class="text-muted mb-3">
                Menghapus akun dapat gagal jika database Anda menggunakan foreign key (misalnya data pertanyaan/jawaban/jurnal masih terkait).
                Jika gagal, pertimbangkan fitur “nonaktifkan akun” (soft delete) pada pengembangan lanjutan.
              </p>

              <form method="post" action="delete_account.php" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="col-md-6">
                  <label class="form-label fw-semibold">Konfirmasi Password</label>
                  <div class="input-group">
                    <input type="password" name="current_password" class="form-control" id="delPass" required>
                    <button class="btn btn-outline-secondary" type="button" data-toggle-pass="#delPass">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>

                <div class="col-12">
                  <button class="btn btn-danger" type="submit" onclick="return confirm('Yakin ingin menghapus akun? Tindakan ini tidak bisa dibatalkan.');">
                    <i class="bi bi-trash me-1"></i>Hapus Akun
                  </button>
                </div>
              </form>

            </div>
          </div>
        </div>

      </div><!-- /tab-content -->

    </main>
  </div>
</div>

<script>
// Preview foto
document.getElementById('photoInput')?.addEventListener('change', function(){
  const file = this.files?.[0];
  if(!file) return;

  const ok = ['image/jpeg','image/png','image/webp'].includes(file.type);
  if(!ok){
    alert('Format foto harus PNG/JPG/WEBP');
    this.value = '';
    return;
  }

  const max = 2 * 1024 * 1024;
  if(file.size > max){
    alert('Ukuran foto maksimal 2MB');
    this.value = '';
    return;
  }

  const url = URL.createObjectURL(file);
  const img = document.getElementById('avatarPreview');
  const fallback = document.getElementById('avatarFallback');
  if(img){
    img.src = url;
    img.style.display = 'block';
  }
  if(fallback) fallback.style.display = 'none';
});

// Toggle show/hide password
document.querySelectorAll('[data-toggle-pass]')?.forEach(btn => {
  btn.addEventListener('click', () => {
    const sel = btn.getAttribute('data-toggle-pass');
    const input = document.querySelector(sel);
    if(!input) return;
    input.type = (input.type === 'password') ? 'text' : 'password';
  });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
