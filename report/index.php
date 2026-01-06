<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php?error=Silakan login dulu.");
  exit;
}

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';

/* Prefill opsional dari URL: ?type=question&id=12 */
$prefType = $_GET['type'] ?? 'question';
$prefId   = (int)($_GET['id'] ?? 0);
$allowed  = ['question','answer','journal','other'];
if (!in_array($prefType, $allowed, true)) $prefType = 'other';

include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4 content-area">

      <div class="d-flex justify-content-between align-items-start align-items-md-center mb-4">
        <div>
          <h3 class="fw-bold mb-1">Pelaporan Konten</h3>
          <p class="text-muted mb-0">Laporkan konten yang merugikan/spam/pelecehan agar dapat ditinjau.</p>
        </div>
        <a href="/IMK/discussion/index.php" class="btn btn-outline-primary">
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
          <form action="/IMK/report/submit.php" method="post" class="row g-3">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

            <!-- honeypot anti-spam -->
            <div style="position:absolute;left:-9999px;top:-9999px;">
              <label>Website</label>
              <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Nama Pelapor</label>
              <input class="form-control" name="reporter_name" required maxlength="80" placeholder="Nama Anda">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Email Pelapor</label>
              <input class="form-control" type="email" name="reporter_email" required maxlength="120" placeholder="nama@email.com">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Jenis Konten</label>
              <select class="form-select" name="target_type">
                <option value="question" <?= $prefType==='question'?'selected':''; ?>>Posting / Pertanyaan</option>
                <option value="answer"   <?= $prefType==='answer'?'selected':''; ?>>Jawaban</option>
                <option value="journal"  <?= $prefType==='journal'?'selected':''; ?>>Jurnal</option>
                <option value="other"    <?= $prefType==='other'?'selected':''; ?>>Lainnya</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">ID Konten (opsional)</label>
              <input class="form-control" type="number" min="0" name="target_id"
                     value="<?= $prefId>0 ? (int)$prefId : ''; ?>" placeholder="Contoh: 12">
              <div class="form-text">Jika Anda tahu ID konten, isi ini. Jika tidak, isi URL di bawah.</div>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">URL Konten (opsional)</label>
              <input class="form-control" name="target_url" maxlength="255" placeholder="Tempel link konten jika ada">
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Alasan Laporan</label>
              <input class="form-control" name
