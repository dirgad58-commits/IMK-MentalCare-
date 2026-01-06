<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$user_id  = (int)$_SESSION['user_id'];
$APP_BASE = '/IMK-MentalCare-/';

function scalar($conn, $sql, $types = '', $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_row($res);
    mysqli_stmt_close($stmt);
    return $row ? (int)$row[0] : 0;
}

function fetch_all($conn, $sql, $types = '', $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    mysqli_stmt_close($stmt);
    return $rows;
}

function fetch_one($conn, $sql, $types = '', $params = []) {
    $rows = fetch_all($conn, $sql, $types, $params);
    return $rows ? $rows[0] : null;
}

/* =========================
   KPI GLOBAL
========================= */
$total_questions = scalar($conn, "SELECT COUNT(*) FROM questions");
$total_answers   = scalar($conn, "SELECT COUNT(*) FROM answers");

$unanswered_questions = scalar($conn,
    "SELECT COUNT(*)
     FROM questions q
     WHERE NOT EXISTS (
        SELECT 1 FROM answers a WHERE a.question_id = q.question_id
     )"
);

/* =========================
   KPI PERSONAL
========================= */
$my_questions = scalar($conn, "SELECT COUNT(*) FROM questions WHERE user_id = ?", 'i', [$user_id]);
$my_answers   = scalar($conn, "SELECT COUNT(*) FROM answers WHERE user_id = ?", 'i', [$user_id]);
$my_journals  = scalar($conn, "SELECT COUNT(*) FROM journals WHERE user_id = ?", 'i', [$user_id]);

$my_journals_7d = scalar($conn,
    "SELECT COUNT(*)
     FROM journals
     WHERE user_id = ?
       AND created_at >= (NOW() - INTERVAL 7 DAY)",
    'i',
    [$user_id]
);

$target_7d   = 7;
$progress_7d = min(100, (int) round(($my_journals_7d / max(1, $target_7d)) * 100));

/* =========================
   DATA: DISKUSI TERBARU
========================= */
$recent_discussions = fetch_all($conn,
    "SELECT q.question_id, q.title, q.created_at, q.is_anonymous, u.username,
            (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.question_id) AS total_answers
     FROM questions q
     JOIN users u ON u.user_id = q.user_id
     ORDER BY q.created_at DESC
     LIMIT 5"
);

/* =========================
   DATA: DISKUSI POPULER
========================= */
$popular_discussions = fetch_all($conn,
    "SELECT q.question_id, q.title, q.created_at, q.is_anonymous, u.username,
            (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.question_id) AS total_answers
     FROM questions q
     JOIN users u ON u.user_id = q.user_id
     ORDER BY total_answers DESC, q.created_at DESC
     LIMIT 5"
);

/* =========================
   DATA: JURNAL TERAKHIR & LIST
========================= */
$last_journal = fetch_one($conn,
    "SELECT journal_id, title, content, created_at
     FROM journals
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 1",
    'i',
    [$user_id]
);

$recent_journals = fetch_all($conn,
    "SELECT journal_id, title, created_at
     FROM journals
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 5",
    'i',
    [$user_id]
);

/* =========================
   DATA: AKTIVITAS TERBARU SAYA
========================= */
$my_recent_activity = fetch_all($conn,
    "(
        SELECT 'question' AS item_type,
               q.question_id AS item_id,
               q.title AS item_title,
               q.created_at AS item_time
        FROM questions q
        WHERE q.user_id = ?
     )
     UNION ALL
     (
        SELECT 'journal' AS item_type,
               j.journal_id AS item_id,
               COALESCE(j.title, 'Jurnal') AS item_title,
               j.created_at AS item_time
        FROM journals j
        WHERE j.user_id = ?
     )
     ORDER BY item_time DESC
     LIMIT 8",
    'ii',
    [$user_id, $user_id]
);

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4">

      <!-- PAGE HEADER -->
      <div class="mc-page-head mb-4">
        <div>
          <h3 class="mc-page-title mb-1">Dashboard</h3>
          <p class="mc-page-sub mb-0">Ringkasan aktivitas, insight mingguan, dan pintasan fitur utama.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
          <a href="<?= $APP_BASE; ?>discussion/create.php" class="btn btn-brand">
            <i class="bi bi-plus-circle me-1"></i>Pertanyaan Baru
          </a>
          <a href="<?= $APP_BASE; ?>journal/create.php" class="btn btn-outline-brand">
            <i class="bi bi-journal-plus me-1"></i>Tulis Jurnal
          </a>
        </div>
      </div>

      <!-- SEARCH + NOTE -->
      <div class="row g-3 mb-4">
        <div class="col-12 col-lg-8">
          <div class="card mc-card h-100">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="mc-ico accent-blue"><i class="bi bi-search"></i></span>
                <div class="mc-card-title">Cari diskusi</div>
              </div>

              <form class="d-flex gap-2" method="get" action="<?= $APP_BASE; ?>discussion/index.php">
                <input type="text" name="q" class="form-control mc-input"
                       placeholder="Ketik kata kunci (mis. cemas, burnout, fokus, tidur)...">
                <button class="btn btn-brand" type="submit">Cari</button>
              </form>

              <div class="muted-small mt-2">Tip: gunakan kata kunci spesifik agar hasil lebih relevan.</div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-4">
          <div class="card mc-card h-100 mc-soft-warning">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="mc-ico accent-amber"><i class="bi bi-shield-check"></i></span>
                <div class="mc-card-title">Catatan penting</div>
              </div>
              <div class="text-muted" style="font-size:.95rem">
                Jika Anda merasa tidak aman atau butuh bantuan segera, hubungi layanan darurat setempat atau orang tepercaya.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPI GLOBAL -->
      <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
          <div class="card mc-card mc-kpi h-100">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="mc-ico accent-teal"><i class="bi bi-chat-dots"></i></span>
                <div class="mc-kpi-label">Pertanyaan (Total)</div>
              </div>
              <div class="mc-kpi-number"><?= (int)$total_questions; ?></div>
              <div class="muted-small">Semua pertanyaan di forum.</div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-4">
          <div class="card mc-card mc-kpi h-100">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="mc-ico accent-blue"><i class="bi bi-reply"></i></span>
                <div class="mc-kpi-label">Jawaban (Total)</div>
              </div>
              <div class="mc-kpi-number"><?= (int)$total_answers; ?></div>
              <div class="muted-small">Total jawaban komunitas.</div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-4">
          <div class="card mc-card mc-kpi h-100">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="mc-ico accent-amber"><i class="bi bi-question-circle"></i></span>
                <div class="mc-kpi-label">Belum Terjawab</div>
              </div>
              <div class="mc-kpi-number"><?= (int)$unanswered_questions; ?></div>
              <div class="muted-small">Pertanyaan yang belum memiliki jawaban.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPI PERSONAL + INSIGHT -->
      <div class="row g-3 mb-4">
        <div class="col-12 col-lg-8">
          <div class="card mc-card h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                <div>
                  <h5 class="mc-section-title mb-1">Aktivitas Anda</h5>
                  <div class="text-muted" style="font-size:.95rem">Statistik kontribusi dan catatan pribadi.</div>
                </div>
                <span class="mc-chip mc-chip-info"><i class="bi bi-check-circle me-1"></i>Akun aktif</span>
              </div>

              <div class="row g-3">
                <div class="col-12 col-md-4">
                  <div class="mc-mini">
                    <div class="d-flex align-items-center gap-2 mb-1">
                      <span class="mc-ico sm accent-teal"><i class="bi bi-chat-square-text"></i></span>
                      <div class="fw-semibold">Pertanyaan Saya</div>
                    </div>
                    <div class="mc-mini-num"><?= (int)$my_questions; ?></div>
                    <div class="muted-small">Jumlah diskusi yang Anda buat.</div>
                  </div>
                </div>

                <div class="col-12 col-md-4">
                  <div class="mc-mini">
                    <div class="d-flex align-items-center gap-2 mb-1">
                      <span class="mc-ico sm accent-blue"><i class="bi bi-send-check"></i></span>
                      <div class="fw-semibold">Jawaban Saya</div>
                    </div>
                    <div class="mc-mini-num"><?= (int)$my_answers; ?></div>
                    <div class="muted-small">Respon yang Anda berikan di forum.</div>
                  </div>
                </div>

                <div class="col-12 col-md-4">
                  <div class="mc-mini">
                    <div class="d-flex align-items-center gap-2 mb-1">
                      <span class="mc-ico sm accent-purple"><i class="bi bi-journal-text"></i></span>
                      <div class="fw-semibold">Jurnal Saya</div>
                    </div>
                    <div class="mc-mini-num"><?= (int)$my_journals; ?></div>
                    <div class="muted-small">Total catatan pribadi.</div>
                  </div>
                </div>
              </div>

              <hr class="my-3">

              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold">Insight 7 hari terakhir</div>
                <span class="mc-chip"><?= (int)$my_journals_7d; ?>/<?= $target_7d; ?> jurnal</span>
              </div>

              <div class="progress mc-progress" role="progressbar" aria-label="Progress jurnal 7 hari"
                   aria-valuenow="<?= $progress_7d; ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: <?= $progress_7d; ?>%"></div>
              </div>

              <div class="muted-small mt-2">
                <?php if ($my_journals_7d == 0): ?>
                  Anda belum menulis jurnal dalam 7 hari terakhir. Menulis 3–5 menit dapat membantu merangkum pikiran dan emosi.
                <?php elseif ($my_journals_7d < 3): ?>
                  Konsistensi mulai terbentuk. Pertahankan ritme yang nyaman bagi Anda.
                <?php else: ?>
                  Aktivitas Anda cukup konsisten. Pertahankan kebiasaan ini agar refleksi diri lebih terarah.
                <?php endif; ?>
              </div>

            </div>
          </div>
        </div>

        <div class="col-12 col-lg-4">
          <div class="card mc-card h-100">
            <div class="card-body">
              <h5 class="mc-section-title mb-2">Jurnal Terakhir</h5>

              <?php if ($last_journal): ?>
                <div class="fw-semibold mb-1"><?= htmlspecialchars($last_journal['title'] ?? 'Jurnal'); ?></div>
                <div class="muted-small mb-2"><?= date('d M Y H:i', strtotime($last_journal['created_at'])); ?></div>

                <div class="text-muted" style="font-size:.95rem">
                  <?php
                    $content = (string)($last_journal['content'] ?? '');
                    $snippet = mb_substr(strip_tags($content), 0, 140);
                    echo htmlspecialchars($snippet) . (mb_strlen(strip_tags($content)) > 140 ? '...' : '');
                  ?>
                </div>

                <div class="d-flex gap-2 mt-3">
                  <a class="btn btn-sm btn-brand" href="<?= $APP_BASE; ?>journal/detail.php?journal_id=<?= (int)$last_journal['journal_id']; ?>">Buka</a>
                  <a class="btn btn-sm btn-outline-brand" href="<?= $APP_BASE; ?>journal/create.php">Tulis lagi</a>
                </div>
              <?php else: ?>
                <div class="text-muted" style="font-size:.95rem">
                  Anda belum memiliki jurnal. Mulai dengan 3 kalimat: “Hari ini saya merasa…”, “Penyebabnya…”, “Langkah kecil saya…”.
                </div>
                <a class="btn btn-brand mt-3" href="<?= $APP_BASE; ?>journal/create.php">
                  <i class="bi bi-journal-plus me-1"></i>Buat Jurnal Pertama
                </a>
              <?php endif; ?>

              <?php if (!empty($recent_journals)): ?>
                <hr class="my-3">
                <div class="fw-semibold mb-2">Jurnal Terbaru</div>

                <div class="list-group list-group-flush mc-list">
                  <?php foreach ($recent_journals as $j): ?>
                    <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-start"
                       href="<?= $APP_BASE; ?>journal/detail.php?journal_id=<?= (int)$j['journal_id']; ?>">
                      <div class="me-2">
                        <div class="fw-semibold"><?= htmlspecialchars($j['title'] ?? 'Jurnal'); ?></div>
                        <div class="muted-small"><?= date('d M Y', strtotime($j['created_at'])); ?></div>
                      </div>
                      <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>

      <!-- DISKUSI TERBARU + POPULER -->
      <div class="row g-3 mb-4">
        <div class="col-12 col-lg-7">
          <div class="card mc-card h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mc-section-title mb-0">Diskusi Terbaru</h5>
                <a href="<?= $APP_BASE; ?>discussion/index.php" class="btn btn-sm btn-outline-brand">Lihat semua</a>
              </div>

              <?php if (empty($recent_discussions)): ?>
                <div class="text-muted py-3">Belum ada diskusi. Anda bisa memulai pertanyaan pertama.</div>
              <?php else: ?>
                <?php foreach ($recent_discussions as $r): ?>
                  <div class="py-3 border-top mc-rowlink">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                      <div>
                        <div class="fw-semibold">
                          <a class="text-decoration-none" href="<?= $APP_BASE; ?>discussion/detail.php?question_id=<?= (int)$r['question_id']; ?>">
                            <?= htmlspecialchars($r['title']); ?>
                          </a>
                        </div>
                        <div class="muted-small">
                          oleh <strong><?= $r['is_anonymous'] ? 'Anonim' : htmlspecialchars($r['username']); ?></strong>
                          • <?= date('d M Y H:i', strtotime($r['created_at'])); ?>
                        </div>
                      </div>
                      <span class="mc-chip">
                        <i class="bi bi-chat-left-text me-1"></i><?= (int)$r['total_answers']; ?>
                      </span>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>

            </div>
          </div>
        </div>

        <div class="col-12 col-lg-5">
          <div class="card mc-card h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mc-section-title mb-0">Diskusi Populer</h5>
                <span class="mc-chip">Top 5</span>
              </div>

              <?php if (empty($popular_discussions)): ?>
                <div class="text-muted py-3">Belum ada diskusi populer untuk ditampilkan.</div>
              <?php else: ?>
                <div class="list-group list-group-flush mc-list">
                  <?php foreach ($popular_discussions as $p): ?>
                    <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-start"
                       href="<?= $APP_BASE; ?>discussion/detail.php?question_id=<?= (int)$p['question_id']; ?>">
                      <div class="me-2">
                        <div class="fw-semibold"><?= htmlspecialchars($p['title']); ?></div>
                        <div class="muted-small">
                          <?= $p['is_anonymous'] ? 'Anonim' : htmlspecialchars($p['username']); ?> • <?= date('d M', strtotime($p['created_at'])); ?>
                        </div>
                      </div>
                      <span class="mc-chip mc-chip-info"><?= (int)$p['total_answers']; ?></span>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>

      <!-- AKTIVITAS TERBARU -->
      <div class="card mc-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mc-section-title mb-0">Aktivitas Terbaru Anda</h5>
            <span class="text-muted" style="font-size:.9rem">Gabungan: pertanyaan & jurnal</span>
          </div>

          <?php if (empty($my_recent_activity)): ?>
            <div class="text-muted py-3">Belum ada aktivitas. Mulai dari menulis jurnal atau membuat pertanyaan.</div>
          <?php else: ?>
            <div class="list-group list-group-flush mc-list">
              <?php foreach ($my_recent_activity as $a): ?>
                <?php
                  $isQuestion = ($a['item_type'] === 'question');
                  $url = $isQuestion
                      ? $APP_BASE . "discussion/detail.php?question_id=" . (int)$a['item_id']
                      : $APP_BASE . "journal/detail.php?journal_id=" . (int)$a['item_id'];
                ?>
                <a class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-start"
                   href="<?= $url; ?>">
                  <div class="me-2">
                    <div class="fw-semibold">
                      <?php if ($isQuestion): ?>
                        <span class="mc-ico sm accent-blue me-1"><i class="bi bi-chat-square-text"></i></span>
                      <?php else: ?>
                        <span class="mc-ico sm accent-purple me-1"><i class="bi bi-journal-text"></i></span>
                      <?php endif; ?>
                      <?= htmlspecialchars($a['item_title']); ?>
                    </div>
                    <div class="muted-small">
                      <?= $isQuestion ? 'Pertanyaan' : 'Jurnal'; ?> • <?= date('d M Y H:i', strtotime($a['item_time'])); ?>
                    </div>
                  </div>
                  <i class="bi bi-chevron-right text-muted"></i>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

        </div>
      </div>

    </main>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
