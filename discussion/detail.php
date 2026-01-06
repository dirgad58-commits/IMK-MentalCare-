<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

if (!isset($_GET['question_id']) || !is_numeric($_GET['question_id'])) {
    die('Pertanyaan tidak valid.');
}

$question_id = (int)$_GET['question_id'];
$current_user_id = (int)$_SESSION['user_id'];

// Question
$stmtQ = mysqli_prepare($conn,
    "SELECT q.question_id, q.user_id, q.title, q.content, q.category, q.is_anonymous, q.created_at, u.username
     FROM questions q
     JOIN users u ON u.user_id=q.user_id
     WHERE q.question_id=? LIMIT 1"
);
mysqli_stmt_bind_param($stmtQ, 'i', $question_id);
mysqli_stmt_execute($stmtQ);
$qRes = mysqli_stmt_get_result($stmtQ);
$question = mysqli_fetch_assoc($qRes);

if (!$question) {
    die('Pertanyaan tidak ditemukan.');
}

$is_owner = ($current_user_id === (int)$question['user_id']);

// Answers with vote count + has_voted
$stmtA = mysqli_prepare($conn,
    "SELECT 
        a.answer_id, a.user_id, a.content, a.is_best, a.created_at,
        u.username,
        (SELECT COUNT(*) FROM answer_votes v WHERE v.answer_id=a.answer_id) AS vote_count,
        (SELECT COUNT(*) FROM answer_votes v2 WHERE v2.answer_id=a.answer_id AND v2.user_id=?) AS has_voted
     FROM answers a
     JOIN users u ON u.user_id=a.user_id
     WHERE a.question_id=?
     ORDER BY a.is_best DESC, vote_count DESC, a.created_at ASC"
);
mysqli_stmt_bind_param($stmtA, 'ii', $current_user_id, $question_id);
mysqli_stmt_execute($stmtA);
$answers = mysqli_stmt_get_result($stmtA);

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4">

      <div class="mb-3">
        <a href="/IMK-MentalCare-/discussion/index.php" class="text-decoration-none">
          <i class="bi bi-arrow-left"></i> Kembali
        </a>
      </div>

      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h4 class="fw-bold mb-2"><?= htmlspecialchars($question['title']); ?></h4>

          <div class="muted-small mb-3">
            oleh <strong><?= $question['is_anonymous'] ? 'Anonim' : htmlspecialchars($question['username']); ?></strong>
            • <?= date('d M Y H:i', strtotime($question['created_at'])); ?>
            <?php if (!empty($question['category'])): ?>
              • <span class="badge badge-soft"><?= htmlspecialchars($question['category']); ?></span>
            <?php endif; ?>
          </div>

          <div><?= nl2br(htmlspecialchars($question['content'])); ?></div>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="fw-semibold mb-0">Jawaban</h5>
        <span class="muted-small">Gunakan bahasa suportif.</span>
      </div>

      <?php if (mysqli_num_rows($answers) === 0): ?>
        <div class="alert alert-info">Belum ada jawaban. Jadilah yang pertama menjawab.</div>
      <?php endif; ?>

      <?php while($a = mysqli_fetch_assoc($answers)): ?>
        <div class="card shadow-sm mb-3 <?= $a['is_best'] ? 'best-border' : ''; ?>">
          <div class="card-body">

            <?php if ($a['is_best']): ?>
              <span class="badge bg-success mb-2">
                <i class="bi bi-check-circle me-1"></i> Jawaban Terbaik
              </span>
            <?php endif; ?>

            <div class="mb-2"><?= nl2br(htmlspecialchars($a['content'])); ?></div>

            <div class="d-flex justify-content-between align-items-center">
              <div class="muted-small">
                Dijawab oleh <strong><?= htmlspecialchars($a['username']); ?></strong>
                • <?= date('d M Y H:i', strtotime($a['created_at'])); ?>
              </div>

              <div class="d-flex align-items-center gap-2">
                <span class="vote-pill">
                  <i class="bi bi-hand-thumbs-up"></i> <?= (int)$a['vote_count']; ?>
                </span>

                <!-- Upvote (sekali per user per answer) -->
                <form action="/IMK-MentalCare-/process/vote_process.php" method="POST" class="m-0">
                  <input type="hidden" name="answer_id" value="<?= (int)$a['answer_id']; ?>">
                  <input type="hidden" name="question_id" value="<?= (int)$question_id; ?>">
                  <button class="btn btn-sm <?= ((int)$a['has_voted'] > 0) ? 'btn-secondary' : 'btn-outline-secondary'; ?>"
                          type="submit"
                          <?= ((int)$a['has_voted'] > 0) ? 'disabled' : ''; ?>>
                    <i class="bi bi-hand-thumbs-up"></i>
                    <?= ((int)$a['has_voted'] > 0) ? 'Sudah vote' : 'Upvote'; ?>
                  </button>
                </form>

                <!-- Best Answer (hanya pemilik pertanyaan) -->
                <?php if ($is_owner): ?>
                  <form action="/IMK-MentalCare-/process/best_answer_process.php" method="POST" class="m-0">
                    <input type="hidden" name="answer_id" value="<?= (int)$a['answer_id']; ?>">
                    <input type="hidden" name="question_id" value="<?= (int)$question_id; ?>">
                    <button class="btn btn-sm btn-outline-success" type="submit">
                      <i class="bi bi-star"></i> Jadikan Terbaik
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>

          </div>
        </div>
      <?php endwhile; ?>

      <div class="card shadow-sm mt-4">
        <div class="card-body">
          <h6 class="fw-semibold mb-3">Tulis Jawaban</h6>

          <form action="/IMK-MentalCare-/process/answer_process.php" method="POST">
            <input type="hidden" name="question_id" value="<?= (int)$question_id; ?>">

            <textarea name="content" class="form-control mb-3" rows="4"
                      placeholder="Tulis jawaban dengan bahasa yang sopan dan suportif..." required></textarea>

            <button class="btn btn-primary">
              <i class="bi bi-send me-1"></i> Kirim Jawaban
            </button>
          </form>
        </div>
      </div>

    </main>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
