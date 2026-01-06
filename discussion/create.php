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
        <h3 class="fw-bold mb-1">Buat Pertanyaan</h3>
        <p class="text-muted mb-0">Gunakan bahasa yang sopan dan suportif.</p>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <form action="/IMK-MentalCare-/process/question_process.php" method="POST">

            <div class="mb-3">
              <label class="form-label">Judul</label>
              <input type="text" name="title" class="form-control form-control-lg" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Kategori (opsional)</label>
              <input type="text" name="category" class="form-control" placeholder="mis: cemas, stres, overthinking">
            </div>

            <div class="mb-3">
              <label class="form-label">Isi Pertanyaan</label>
              <textarea name="content" class="form-control" rows="5" required></textarea>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="is_anonymous" value="1" id="anon">
              <label class="form-check-label" for="anon">Posting sebagai anonim</label>
            </div>

            <button class="btn btn-primary">
              <i class="bi bi-send me-1"></i> Posting
            </button>

            <a href="/IMK-MentalCare-/discussion/index.php" class="btn btn-outline-secondary ms-2">Batal</a>
          </form>
        </div>
      </div>

    </main>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
