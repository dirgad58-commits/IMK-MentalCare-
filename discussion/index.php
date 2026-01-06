<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$stmt = mysqli_prepare($conn,
    "SELECT q.question_id, q.title, q.content, q.category, q.is_anonymous, q.created_at, u.username,
            (SELECT COUNT(*) FROM answers a WHERE a.question_id=q.question_id) AS total_answers
     FROM questions q
     JOIN users u ON u.user_id=q.user_id
     ORDER BY q.created_at DESC"
);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4">

      <!-- HEADER -->
      <div class="d-flex justify-content-between align-items-start align-items-md-center mb-4">
        <div>
          <h3 class="fw-bold mb-1">Diskusi</h3>
          <p class="text-muted mb-0">Tanya, jawab, dan saling mendukung dengan aman.</p>
        </div>

        <!-- Gunakan tombol brand agar konsisten -->
        <a href="/IMK/discussion/create.php" class="btn btn-brand">
          <i class="bi bi-plus-circle me-1"></i> Buat Pertanyaan
        </a>
      </div>

      <!-- SEARCH/FILTER (disabled) -->
      <div class="card mc-card mb-3">
        <div class="card-body">
          <div class="row g-2">
            <div class="col-md-8">
              <div class="input-group">
                <span class="input-group-text bg-white border-0" style="border-radius:14px 0 0 14px;">
                  <i class="bi bi-search text-muted"></i>
                </span>
                <input class="form-control" placeholder="Cari pertanyaan... (pengembangan lanjut)" disabled
                       style="border-left:0;border-radius:0 14px 14px 0;">
              </div>
            </div>
            <div class="col-md-4">
              <select class="form-select" disabled>
                <option>Filter kategori... (pengembangan lanjut)</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- EMPTY STATE -->
      <?php if (mysqli_num_rows($res) === 0): ?>
        <div class="card mc-card p-4">
          <div class="d-flex gap-3 align-items-start">
            <div class="mc-empty-ico"><i class="bi bi-chat-dots"></i></div>
            <div>
              <div class="fw-bold mb-1">Belum ada pertanyaan</div>
              <div class="text-muted mb-3">Jadilah yang pertama bertanya agar diskusi mulai berjalan.</div>
              <a href="/IMK/discussion/create.php" class="btn btn-brand">
                <i class="bi bi-plus-circle me-1"></i> Buat Pertanyaan
              </a>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- LIST -->
      <?php while($q = mysqli_fetch_assoc($res)): ?>
        <?php
          $qid      = (int)$q['question_id'];
          $title    = htmlspecialchars($q['title'] ?? '');
          $snippet  = htmlspecialchars(mb_strimwidth(strip_tags((string)$q['content']), 0, 170, '...'));
          $author   = $q['is_anonymous'] ? 'Anonim' : htmlspecialchars($q['username'] ?? 'Pengguna');
          $created  = !empty($q['created_at']) ? date('d M Y • H:i', strtotime($q['created_at'])) : '-';
          $answers  = (int)($q['total_answers'] ?? 0);
          $category = trim((string)($q['category'] ?? ''));
        ?>

        <div class="card mc-card mc-card-accent mb-3 mc-thread-card">
          <div class="card-body">

            <!-- Title -->
            <div class="mc-thread-title">
              <a class="mc-thread-link" href="/IMK/discussion/detail.php?question_id=<?= $qid; ?>">
                <?= $title; ?>
              </a>
            </div>

            <!-- Meta -->
            <div class="mc-thread-meta">
              <span><i class="bi bi-person-circle me-1"></i>oleh <strong><?= $author; ?></strong></span>
              <span class="dot">•</span>
              <span><i class="bi bi-clock me-1"></i><?= $created; ?></span>

              <?php if ($category !== ''): ?>
                <span class="dot">•</span>
                <span class="mc-chip">
                  <i class="bi bi-tag me-1"></i><?= htmlspecialchars($category); ?>
                </span>
              <?php endif; ?>
            </div>

            <!-- Snippet -->
            <div class="mc-thread-snippet">
              <?= $snippet; ?>
            </div>

            <!-- Footer actions -->
            <div class="mc-thread-footer">
              <div class="mc-pill">
                <i class="bi bi-chat-left-text me-1"></i><?= $answers; ?> jawaban
              </div>

              <a class="btn btn-sm btn-outline-brand"
                 href="/IMK/discussion/detail.php?question_id=<?= $qid; ?>">
                Lihat Diskusi <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>

          </div>
        </div>
      <?php endwhile; ?>

    </main>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
