<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$journal_id = (int)($_GET['journal_id'] ?? 0);

if ($journal_id <= 0) {
    header("Location: index.php?error=Jurnal tidak valid.");
    exit;
}

// Ambil jurnal milik user yang login (proteksi: tidak bisa buka jurnal orang lain)
$stmt = mysqli_prepare($conn,
    "SELECT journal_id, title, content, mood, created_at
     FROM journals
     WHERE journal_id = ? AND user_id = ?
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'ii', $journal_id, $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$journal = mysqli_fetch_assoc($res);

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4">

      <div class="d-flex justify-content-between align-items-start align-items-md-center mb-4 gap-2 flex-wrap">
        <div>
          <h3 class="fw-bold mb-1">Detail Jurnal</h3>
          <p class="text-muted mb-0">Baca kembali catatan Anda dengan tenang.</p>
        </div>
        <div class="d-flex gap-2">
          <a href="/IMK/journal/index.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
          </a>
          <!-- Jika Anda belum punya edit.php, boleh hapus tombol ini -->
          <a href="/IMK/journal/edit.php?journal_id=<?= (int)$journal_id; ?>" class="btn btn-primary">
            <i class="bi bi-pencil-square me-1"></i>Edit
          </a>
        </div>
      </div>

      <?php if (!$journal): ?>
        <div class="alert alert-danger">
          Jurnal tidak ditemukan atau Anda tidak punya akses.
        </div>
      <?php else: ?>
        <div class="card shadow-sm" style="border-radius:18px;">
          <div class="card-body">

            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
              <div>
                <h4 class="fw-bold mb-1">
                  <?= htmlspecialchars($journal['title'] ?: 'Tanpa Judul'); ?>
                </h4>
                <div class="text-muted">
                  <span class="me-2">
                    <i class="bi bi-calendar2-week me-1"></i>
                    <?= date('d M Y H:i', strtotime($journal['created_at'])); ?>
                  </span>

                  <?php if (!empty($journal['mood'])): ?>
                    <span class="badge rounded-pill text-bg-light border">
                      <i class="bi bi-emoji-smile me-1"></i><?= htmlspecialchars($journal['mood']); ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="d-flex gap-2">
                <!-- Tombol aksi opsional -->
                <a href="/IMK/journal/create.php" class="btn btn-outline-primary btn-sm">
                  <i class="bi bi-journal-plus me-1"></i>Tulis Baru
                </a>
              </div>
            </div>

            <hr class="my-3">

            <div style="font-size:1rem; line-height:1.7;">
              <?= nl2br(htmlspecialchars($journal['content'] ?? '')); ?>
            </div>

          </div>
        </div>
      <?php endif; ?>

    </main>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
