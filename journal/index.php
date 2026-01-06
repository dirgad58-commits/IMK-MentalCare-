<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/* =========================
   Helpers
========================= */
function scalar($conn, $sql, $types = '', $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_row($res);
    return $row ? $row[0] : null;
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

/* =========================
   Filters (GET)
========================= */
$q     = trim($_GET['q'] ?? '');
$mood  = trim($_GET['mood'] ?? '');
$from  = trim($_GET['from'] ?? '');
$to    = trim($_GET['to'] ?? '');
$sort  = $_GET['sort'] ?? 'new'; // new|old|title
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

/* =========================
   KPI
========================= */
$total_journals = (int) (scalar($conn, "SELECT COUNT(*) FROM journals WHERE user_id=?", 'i', [$user_id]) ?? 0);

$journals_7d = (int) (scalar($conn,
    "SELECT COUNT(*) FROM journals WHERE user_id=? AND created_at >= (NOW() - INTERVAL 7 DAY)",
    'i', [$user_id]
) ?? 0);

$last_written = scalar($conn,
    "SELECT MAX(created_at) FROM journals WHERE user_id=?",
    'i', [$user_id]
);

$top_mood = scalar($conn,
    "SELECT mood
     FROM journals
     WHERE user_id=? AND mood IS NOT NULL AND mood <> ''
     GROUP BY mood
     ORDER BY COUNT(*) DESC
     LIMIT 1",
    'i', [$user_id]
);
$top_mood = $top_mood ?: '-';

/* daftar mood untuk dropdown filter */
$mood_options = fetch_all($conn,
    "SELECT DISTINCT mood FROM journals WHERE user_id=? AND mood IS NOT NULL AND mood<>'' ORDER BY mood ASC",
    'i', [$user_id]
);

/* =========================
   Build WHERE for listing
========================= */
$where = " WHERE user_id=? ";
$types = "i";
$params = [$user_id];

if ($q !== '') {
    $where .= " AND title LIKE ? ";
    $types .= "s";
    $params[] = "%{$q}%";
}

if ($mood !== '') {
    $where .= " AND mood = ? ";
    $types .= "s";
    $params[] = $mood;
}

if ($from !== '') {
    $where .= " AND DATE(created_at) >= ? ";
    $types .= "s";
    $params[] = $from;
}

if ($to !== '') {
    $where .= " AND DATE(created_at) <= ? ";
    $types .= "s";
    $params[] = $to;
}

/* sorting */
$orderBy = " ORDER BY created_at DESC ";
if ($sort === 'old') $orderBy = " ORDER BY created_at ASC ";
if ($sort === 'title') $orderBy = " ORDER BY title ASC, created_at DESC ";

/* count filtered */
$total_filtered = (int) (scalar($conn,
    "SELECT COUNT(*) FROM journals " . $where,
    $types, $params
) ?? 0);

$total_pages = max(1, (int) ceil($total_filtered / $limit));

/* fetch list */
$list = fetch_all($conn,
    "SELECT journal_id,
            COALESCE(title,'') AS title,
            COALESCE(content,'') AS content,
            COALESCE(mood,'') AS mood,
            created_at
     FROM journals
     {$where}
     {$orderBy}
     LIMIT {$limit} OFFSET {$offset}",
    $types, $params
);

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4 content-area">

      <!-- HEADER -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
          <h3 class="fw-bold mb-1">Jurnal Pribadi</h3>
          <p class="text-muted mb-0">Catat emosi, refleksi, dan kemajuan kecil Anda setiap hari.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <a href="/IMK/journal/create.php" class="btn btn-primary">
            <i class="bi bi-journal-plus me-1"></i> Tulis Jurnal
          </a>
          <a href="/IMK/journal/insight.php" class="btn btn-outline-primary">
            <i class="bi bi-graph-up me-1"></i> Insight
          </a>
        </div>
      </div>

      <!-- KPI CARDS -->
      <div class="row g-3 mb-4">
        <div class="col-12 col-md-3">
          <div class="card shadow-sm h-100" style="border-radius:18px;">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-journal-text text-primary fs-5"></i>
                <div class="fw-semibold">Total Jurnal</div>
              </div>
              <div class="display-6 fw-bold"><?= (int)$total_journals; ?></div>
              <div class="muted-small">Semua catatan Anda.</div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-3">
          <div class="card shadow-sm h-100" style="border-radius:18px;">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-calendar2-week text-primary fs-5"></i>
                <div class="fw-semibold">7 Hari Terakhir</div>
              </div>
              <div class="display-6 fw-bold"><?= (int)$journals_7d; ?></div>
              <div class="muted-small">Frekuensi menulis terbaru.</div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-3">
          <div class="card shadow-sm h-100" style="border-radius:18px;">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-emoji-smile text-primary fs-5"></i>
                <div class="fw-semibold">Mood Terbanyak</div>
              </div>
              <div class="fs-2 fw-bold"><?= htmlspecialchars($top_mood); ?></div>
              <div class="muted-small">Mood yang paling sering muncul.</div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-3">
          <div class="card shadow-sm h-100" style="border-radius:18px;">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-clock-history text-primary fs-5"></i>
                <div class="fw-semibold">Terakhir Menulis</div>
              </div>
              <div class="fw-bold" style="font-size:1.25rem;">
                <?= $last_written ? date('d M Y H:i', strtotime($last_written)) : '-'; ?>
              </div>
              <div class="muted-small">Waktu catatan terakhir dibuat.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- FILTER BAR -->
      <div class="card shadow-sm mb-4" style="border-radius:18px;">
        <div class="card-body">
          <form class="row g-2 align-items-end" method="get">
            <div class="col-12 col-md-4">
              <label class="form-label fw-semibold">Cari Judul</label>
              <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($q); ?>" placeholder="mis. Overthinking, tidur, fokus...">
            </div>

            <div class="col-12 col-md-2">
              <label class="form-label fw-semibold">Mood</label>
              <select name="mood" class="form-select">
                <option value="">Semua</option>
                <?php foreach ($mood_options as $mo): ?>
                  <?php $val = $mo['mood']; ?>
                  <option value="<?= htmlspecialchars($val); ?>" <?= ($mood===$val?'selected':''); ?>>
                    <?= htmlspecialchars($val); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-6 col-md-2">
              <label class="form-label fw-semibold">Dari</label>
              <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from); ?>">
            </div>

            <div class="col-6 col-md-2">
              <label class="form-label fw-semibold">Sampai</label>
              <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to); ?>">
            </div>

            <div class="col-12 col-md-2">
              <label class="form-label fw-semibold">Urutkan</label>
              <select name="sort" class="form-select">
                <option value="new" <?= $sort==='new'?'selected':''; ?>>Terbaru</option>
                <option value="old" <?= $sort==='old'?'selected':''; ?>>Terlama</option>
                <option value="title" <?= $sort==='title'?'selected':''; ?>>Judul A-Z</option>
              </select>
            </div>

            <div class="col-12 d-flex gap-2 mt-2">
              <button class="btn btn-primary" type="submit">
                <i class="bi bi-funnel me-1"></i>Terapkan
              </button>
              <a class="btn btn-outline-secondary" href="/IMK/journal/index.php">
                Reset
              </a>
              <div class="ms-auto text-muted small">
                Menampilkan <b><?= (int)$total_filtered; ?></b> jurnal
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- LIST -->
      <?php if (empty($list)): ?>
        <div class="card shadow-sm" style="border-radius:18px;">
          <div class="card-body">
            <div class="d-flex align-items-start gap-3">
              <div class="rounded-4 d-flex align-items-center justify-content-center"
                   style="width:54px;height:54px;background:rgba(13,110,253,.10);">
                <i class="bi bi-journal-plus text-primary fs-4"></i>
              </div>
              <div>
                <div class="fw-semibold">Belum ada jurnal yang cocok dengan filter.</div>
                <div class="text-muted">Coba reset filter atau buat jurnal baru untuk memulai.</div>
                <a href="/IMK/journal/create.php" class="btn btn-primary mt-2">
                  <i class="bi bi-journal-plus me-1"></i>Tulis Jurnal
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php else: ?>

       <?php foreach($list as $j): ?>
  <?php
    $title = trim($j['title']) !== '' ? $j['title'] : 'Tanpa Judul';
    $m = trim($j['mood']) !== '' ? $j['mood'] : '—';

    $content = strip_tags((string)$j['content']);
    $snippet = mb_substr($content, 0, 160);
    if (mb_strlen($content) > 160) $snippet .= '...';

    $moodClass = 'text-bg-light border';
    $moodLower = mb_strtolower($m);
    if (str_contains($moodLower, 'senang') || str_contains($moodLower, 'bahagia')) $moodClass = 'text-bg-success';
    elseif (str_contains($moodLower, 'sedih')) $moodClass = 'text-bg-primary';
    elseif (str_contains($moodLower, 'cemas') || str_contains($moodLower, 'khawatir')) $moodClass = 'text-bg-warning';
    elseif (str_contains($moodLower, 'marah')) $moodClass = 'text-bg-danger';
  ?>

  <div class="card shadow-sm mb-3 position-relative" style="border-radius:18px;">
    <div class="card-body">

      <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">

        <!-- KONTEN (klik ke detail) -->
        <div class="flex-grow-1">
          <!-- stretched-link membuat seluruh area konten bisa diklik -->
          <a class="stretched-link text-decoration-none"
             href="/IMK/journal/detail.php?journal_id=<?= (int)$j['journal_id']; ?>">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <div class="fw-semibold fs-5 text-dark">
                <?= htmlspecialchars($title); ?>
              </div>
              <span class="badge rounded-pill <?= $moodClass; ?>">
                <i class="bi bi-emoji-smile me-1"></i><?= htmlspecialchars($m); ?>
              </span>
            </div>

            <div class="muted-small mt-1 text-muted">
              <i class="bi bi-clock me-1"></i><?= date('d M Y H:i', strtotime($j['created_at'])); ?>
              <span class="mx-2">•</span>
              <i class="bi bi-hash me-1"></i>ID <?= (int)$j['journal_id']; ?>
            </div>

            <?php if (trim($snippet) !== ''): ?>
              <div class="text-muted mt-2" style="font-size:.95rem;">
                <?= htmlspecialchars($snippet); ?>
              </div>
            <?php endif; ?>
          </a>
        </div>

        <!-- AKSI (tidak ikut klik stretched-link) -->
        <div class="d-flex gap-2 position-relative" style="z-index: 2;">
          <a href="/IMK/journal/detail.php?journal_id=<?= (int)$j['journal_id']; ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-eye me-1"></i>Baca
          </a>
          <a href="/IMK/journal/edit.php?journal_id=<?= (int)$j['journal_id']; ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
          </a>
          <button class="btn btn-sm btn-outline-danger"
                  data-bs-toggle="modal"
                  data-bs-target="#delModal<?= (int)$j['journal_id']; ?>">
            <i class="bi bi-trash me-1"></i>Hapus
          </button>
        </div>

      </div>

    </div>
  </div>

  <!-- DELETE MODAL -->
  <div class="modal fade" id="delModal<?= (int)$j['journal_id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border-radius:16px;">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold">Hapus jurnal?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Anda yakin ingin menghapus jurnal <b><?= htmlspecialchars($title); ?></b>? Tindakan ini tidak bisa dibatalkan.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <a href="/IMK/journal/delete.php?journal_id=<?= (int)$j['journal_id']; ?>" class="btn btn-danger">
            <i class="bi bi-trash me-1"></i>Hapus
          </a>
        </div>
      </div>
    </div>
  </div>

<?php endforeach; ?>


        <!-- PAGINATION -->
        <?php if ($total_pages > 1): ?>
          <nav class="mt-3">
            <ul class="pagination">
              <?php
                // helper build query string
                $baseParams = $_GET;
                unset($baseParams['page']);
              ?>

              <li class="page-item <?= $page<=1?'disabled':''; ?>">
                <a class="page-link"
                   href="?<?= http_build_query(array_merge($baseParams, ['page'=>$page-1])); ?>">Sebelumnya</a>
              </li>

              <?php
                // tampilkan 1..N dengan pembatas sederhana
                $start = max(1, $page - 2);
                $end   = min($total_pages, $page + 2);
                for ($i=$start; $i<=$end; $i++):
              ?>
                <li class="page-item <?= $i===$page?'active':''; ?>">
                  <a class="page-link" href="?<?= http_build_query(array_merge($baseParams, ['page'=>$i])); ?>">
                    <?= $i; ?>
                  </a>
                </li>
              <?php endfor; ?>

              <li class="page-item <?= $page>=$total_pages?'disabled':''; ?>">
                <a class="page-link"
                   href="?<?= http_build_query(array_merge($baseParams, ['page'=>$page+1])); ?>">Berikutnya</a>
              </li>
            </ul>
          </nav>
        <?php endif; ?>

      <?php endif; ?>

    </main>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
