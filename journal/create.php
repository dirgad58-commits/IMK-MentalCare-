<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4">

      <div class="mb-4">
        <h3 class="fw-bold mb-1">Tulis Jurnal</h3>
        <p class="text-muted mb-0">Jujur, singkat, dan aman untuk diri sendiri.</p>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <form action="/IMK/process/journal_process.php" method="POST">

            <div class="mb-3">
              <label class="form-label">Judul (opsional)</label>
              <input type="text" name="title" class="form-control" placeholder="mis: Hari yang melelahkan">
            </div>

            <div class="mb-3">
              <label class="form-label">Mood</label>
              <select name="mood" class="form-select" required>
                <option value="senang">senang</option>
                <option value="netral">netral</option>
                <option value="sedih">sedih</option>
                <option value="cemas">cemas</option>
                <option value="marah">marah</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Isi</label>
              <textarea name="content" class="form-control" rows="6" required></textarea>
            </div>

            <button class="btn btn-primary">
              <i class="bi bi-save me-1"></i> Simpan
            </button>

            <a href="/IMK/journal/index.php" class="btn btn-outline-secondary ms-2">Batal</a>
          </form>
        </div>
      </div>

    </main>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
