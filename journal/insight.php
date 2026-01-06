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
function fetch_all($conn, $sql, $types = '', $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    return $rows;
}
function scalar($conn, $sql, $types = '', $params = []) {
    $rows = fetch_all($conn, $sql, $types, $params);
    if (!$rows) return null;
    $first = array_values($rows[0]);
    return $first[0] ?? null;
}

/* =========================
   Filter Range
   default: last 30 days
========================= */
$from = trim($_GET['from'] ?? '');
$to   = trim($_GET['to'] ?? '');

if ($to === '')   $to = date('Y-m-d');
if ($from === '') $from = date('Y-m-d', strtotime('-29 days'));

$rangeStart = $from . ' 00:00:00';
$rangeEnd   = $to   . ' 23:59:59';

/* guard basic */
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-d', strtotime('-29 days'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = date('Y-m-d');

/* =========================
   KPI
========================= */
$total_all = (int)(scalar($conn, "SELECT COUNT(*) FROM journals WHERE user_id=?", 'i', [$user_id]) ?? 0);

$total_range = (int)(scalar($conn,
    "SELECT COUNT(*) FROM journals
     WHERE user_id=? AND created_at BETWEEN ? AND ?",
    'iss', [$user_id, $rangeStart, $rangeEnd]
) ?? 0);

$total_7d = (int)(scalar($conn,
    "SELECT COUNT(*) FROM journals
     WHERE user_id=? AND created_at >= (NOW() - INTERVAL 7 DAY)",
    'i', [$user_id]
) ?? 0);

$last_written = scalar($conn,
    "SELECT MAX(created_at) FROM journals WHERE user_id=?",
    'i', [$user_id]
);

/* =========================
   Streak (consecutive days)
   - based on distinct DATE(created_at)
========================= */
$days = fetch_all($conn,
    "SELECT DISTINCT DATE(created_at) AS d
     FROM journals
     WHERE user_id=?
       AND created_at >= (NOW() - INTERVAL 120 DAY)
     ORDER BY d DESC",
    'i', [$user_id]
);

$datesSet = [];
foreach ($days as $row) $datesSet[$row['d']] = true;

$streak = 0;
$cursor = date('Y-m-d');
while (isset($datesSet[$cursor])) {
    $streak++;
    $cursor = date('Y-m-d', strtotime($cursor . ' -1 day'));
}

/* =========================
   Daily counts in range
========================= */
$daily = fetch_all($conn,
    "SELECT DATE(created_at) AS d, COUNT(*) AS c
     FROM journals
     WHERE user_id=? AND created_at BETWEEN ? AND ?
     GROUP BY DATE(created_at)
     ORDER BY d ASC",
    'iss', [$user_id, $rangeStart, $rangeEnd]
);

$dailyMap = [];
foreach ($daily as $r) $dailyMap[$r['d']] = (int)$r['c'];

/* build full date labels (include 0 days) */
$labels = [];
$values = [];
$dt = strtotime($from);
$dtEnd = strtotime($to);
while ($dt <= $dtEnd) {
    $d = date('Y-m-d', $dt);
    $labels[] = $d;
    $values[] = $dailyMap[$d] ?? 0;
    $dt = strtotime('+1 day', $dt);
}

/* =========================
   Mood distribution in range
========================= */
$moods = fetch_all($conn,
    "SELECT COALESCE(NULLIF(TRIM(mood),''), 'Tidak diisi') AS mood_label,
            COUNT(*) AS c
     FROM journals
     WHERE user_id=? AND created_at BETWEEN ? AND ?
     GROUP BY COALESCE(NULLIF(TRIM(mood),''), 'Tidak diisi')
     ORDER BY c DESC, mood_label ASC",
    'iss', [$user_id, $rangeStart, $rangeEnd]
);

$moodLabels = [];
$moodCounts = [];
foreach ($moods as $m) {
    $moodLabels[] = $m['mood_label'];
    $moodCounts[] = (int)$m['c'];
}

$topMood = $moodLabels[0] ?? '-';

/* =========================
   Top words (simple keyword freq)
   - last 200 journals in range
   - stopwords basic (ID)
========================= */
$rowsText = fetch_all($conn,
    "SELECT COALESCE(title,'') AS title, COALESCE(content,'') AS content
     FROM journals
     WHERE user_id=? AND created_at BETWEEN ? AND ?
     ORDER BY created_at DESC
     LIMIT 200",
    'iss', [$user_id, $rangeStart, $rangeEnd]
);

$stop = array_flip([
  'yang','dan','di','ke','dari','untuk','pada','itu','ini','atau','saya','aku','kamu','dia','kita','kami',
  'dengan','karena','jadi','akan','sudah','belum','lebih','saat','hari','minggu','bulan','tahun','dalam',
  'ada','tidak','iya','nya','se','para','oleh','juga','lagi','pun','bisa','harus','ingin','mau','tentang',
  'apa','kenapa','bagaimana','siapa','dimana','kapan','the','a','an','to','of','in','is','are'
]);

$freq = [];
foreach ($rowsText as $t) {
    $text = mb_strtolower($t['title'] . ' ' . $t['content']);
    $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);
    $parts = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($parts as $w) {
        if (mb_strlen($w) < 4) continue;
        if (isset($stop[$w])) continue;
        $freq[$w] = ($freq[$w] ?? 0) + 1;
    }
}
arsort($freq);
$topWords = array_slice($freq, 0, 10, true);

/* =========================
   Page
========================= */
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="col-md-9 col-lg-10 p-4 content-area">

      <!-- Header -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
          <h3 class="fw-bold mb-1">Insight Jurnal</h3>
          <p class="text-muted mb-0">
            Ringkasan pola menulis, tren harian, distribusi mood, dan kata yang sering muncul.
          </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <a href="/IMK-MentalCare-/journal/create.php" class="btn btn-primary">
            <i class="bi bi-journal-plus me-1"></i>Tulis Jurnal
          </a>
          <a href="/IMK-MentalCare-/journal/index.php" class="btn btn-outline-primary">
            <i class="bi bi-list-ul me-1"></i>Daftar Jurnal
          </a>
        </div>
      </div>

      <!-- Range Filter -->
      <div class="card shadow-sm mb-4" style="border-radius:18px;">
        <div class="card-body">
          <form class="row g-2 align-items-end" method="get">
            <div class="col-6 col-md-3">
              <label class="form-label fw-semibold">Dari</label>
              <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from); ?>">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label fw-semibold">Sampai</label>
              <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to); ?>">
            </div>
            <div class="col-12 col-md-6 d-flex gap-2">
              <button class="btn btn-primary" type="submit">
                <i class="bi bi-funnel me-1"></i>Terapkan
              </button>
              <a class="btn btn-outline-secondary" href="/IMK-MentalCare-/journal/insight.php">
                Reset
              </a>
              <div class="ms-auto text-muted small align-self-center">
                Rentang aktif: <b><?= htmlspecialchars($from); ?></b> s/d <b><?= htmlspecialchars($to); ?></b>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- KPI -->
      <div class="row g-3 mb-4">
        <div class="col-12 col-md-3">
          <div class="card shadow-sm h-100" style="border-radius:18px;">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-journal-text text-primary fs-5"></i>
                <div class="fw-semibold">Total Jurnal</div>
              </div>
              <div class="display-6 fw-bold"><?= (int)$total_all; ?></div>
              <div class="text-muted" style="font-size:.92rem;">Sepanjang waktu.</div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-3">
          <div class="card shadow-sm h-100" style="border-radius:18px;">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-calendar-range text-primary fs-5"></i>
                <div class="fw-semibold">Di Rentang Ini</div>
              </div>
              <div class="display-6 fw-bold"><?= (int)$total_range; ?></div>
              <div class="text-muted" style="font-size:.92rem;">Dari filter tanggal.</div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-3">
          <div class="card shadow-sm h-100" style="border-radius:18px;">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-lightning-charge text-primary fs-5"></i>
                <div class="fw-semibold">Streak</div>
              </div>
              <div class="display-6 fw-bold"><?= (int)$streak; ?></div>
              <div class="text-muted" style="font-size:.92rem;">Hari berturut-turut menulis.</div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-3">
          <div class="card shadow-sm h-100" style="border-radius:18px;">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-emoji-smile text-primary fs-5"></i>
                <div class="fw-semibold">Mood Dominan</div>
              </div>
              <div class="fs-2 fw-bold"><?= htmlspecialchars($topMood); ?></div>
              <div class="text-muted" style="font-size:.92rem;">
                Terakhir menulis: <?= $last_written ? date('d M Y H:i', strtotime($last_written)) : '-'; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts -->
      <div class="row g-3 mb-4">
        <div class="col-12 col-lg-7">
          <div class="card shadow-sm h-100" style="border-radius:18px;">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="fw-semibold mb-0"><i class="bi bi-graph-up me-2"></i>Jurnal per Hari</h5>
                <span class="badge text-bg-light border"><?= (int)array_sum($values); ?> entri</span>
              </div>

              <div class="text-muted small mb-2">
                Grafik ini menampilkan jumlah jurnal yang Anda tulis setiap hari dalam rentang yang dipilih.
              </div>

              <div style="height:320px;">
                <canvas id="dailyChart"></canvas>
              </div>

              <!-- fallback table -->
              <div class="mt-3">
                <details>
                  <summary class="text-muted small" style="cursor:pointer;">Lihat data tabel (fallback)</summary>
                  <div class="table-responsive mt-2">
                    <table class="table table-sm">
                      <thead>
                        <tr>
                          <th>Tanggal</th>
                          <th>Jumlah</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($labels as $i => $d): ?>
                          <tr>
                            <td><?= htmlspecialchars($d); ?></td>
                            <td><?= (int)$values[$i]; ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </details>
              </div>

            </div>
          </div>
        </div>

        <div class="col-12 col-lg-5">
          <div class="card shadow-sm h-100" style="border-radius:18px;">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="fw-semibold mb-0"><i class="bi bi-pie-chart me-2"></i>Distribusi Mood</h5>
                <span class="badge text-bg-light border"><?= (int)array_sum($moodCounts); ?> entri</span>
              </div>

              <div class="text-muted small mb-2">
                Mengelompokkan mood yang Anda pilih saat menulis jurnal.
              </div>

              <div style="height:320px;">
                <canvas id="moodChart"></canvas>
              </div>

              <div class="mt-3">
                <div class="fw-semibold mb-2">Ringkasan Mood</div>
                <?php if (empty($moods)): ?>
                  <div class="text-muted">Belum ada data mood pada rentang ini.</div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-sm align-middle">
                      <thead>
                        <tr>
                          <th>Mood</th>
                          <th class="text-end">Jumlah</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($moods as $m): ?>
                          <tr>
                            <td><?= htmlspecialchars($m['mood_label']); ?></td>
                            <td class="text-end fw-semibold"><?= (int)$m['c']; ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Top Words -->
      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="card shadow-sm h-100" style="border-radius:18px;">
            <div class="card-body">
              <h5 class="fw-semibold mb-2"><i class="bi bi-hash me-2"></i>Kata yang Sering Muncul</h5>
              <div class="text-muted small mb-3">
                Diambil dari judul + konten (hingga 200 jurnal) pada rentang yang dipilih.
              </div>

              <?php if (empty($topWords)): ?>
                <div class="text-muted">Belum cukup data teks untuk dianalisis.</div>
              <?php else: ?>
                <?php
                  $max = max($topWords);
                ?>
                <div class="d-flex flex-column gap-2">
                  <?php foreach ($topWords as $w => $c): ?>
                    <?php $pct = (int)round(($c / $max) * 100); ?>
                    <div>
                      <div class="d-flex justify-content-between small">
                        <div class="fw-semibold"><?= htmlspecialchars($w); ?></div>
                        <div class="text-muted"><?= (int)$c; ?></div>
                      </div>
                      <div class="progress" style="height:9px;">
                        <div class="progress-bar" role="progressbar" style="width: <?= $pct; ?>%"></div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

            </div>
          </div>
        </div>

        <div class="col-12 col-lg-6">
          <div class="card shadow-sm h-100" style="border-radius:18px;">
            <div class="card-body">
              <h5 class="fw-semibold mb-2"><i class="bi bi-check2-square me-2"></i>Rekomendasi Tindak Lanjut</h5>

              <div class="p-3 rounded-4 mb-3" style="background:rgba(13,110,253,.08);border:1px solid rgba(13,110,253,.18);">
                <div class="fw-semibold mb-1">Konsistensi kecil</div>
                <div class="text-muted small">
                  Jika streak Anda masih rendah, targetkan 3–5 menit menulis per hari. Fokus pada: peristiwa, emosi, dan satu langkah kecil.
                </div>
              </div>

              <div class="p-3 rounded-4" style="background:rgba(25,135,84,.08);border:1px solid rgba(25,135,84,.18);">
                <div class="fw-semibold mb-1">Pola mood</div>
                <div class="text-muted small">
                  Mood dominan di rentang ini: <b><?= htmlspecialchars($topMood); ?></b>. Coba tandai pemicu dan strategi coping yang membantu, lalu ulangi yang efektif.
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- Chart.js (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
(function(){
  const labels = <?= json_encode($labels, JSON_UNESCAPED_SLASHES); ?>;
  const values = <?= json_encode($values, JSON_UNESCAPED_SLASHES); ?>;

  const moodLabels = <?= json_encode($moodLabels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
  const moodCounts = <?= json_encode($moodCounts, JSON_UNESCAPED_SLASHES); ?>;

  // If Chart.js not loaded, stop gracefully
  if (typeof Chart === 'undefined') return;

  // Daily line chart
  const ctx1 = document.getElementById('dailyChart');
  if (ctx1) {
    new Chart(ctx1, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Jumlah Jurnal',
          data: values,
          tension: 0.35,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { mode: 'index', intersect: false }
        },
        scales: {
          x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } },
          y: { beginAtZero: true, ticks: { precision: 0 } }
        }
      }
    });
  }

  // Mood doughnut chart
  const ctx2 = document.getElementById('moodChart');
  if (ctx2) {
    new Chart(ctx2, {
      type: 'doughnut',
      data: {
        labels: moodLabels,
        datasets: [{
          data: moodCounts
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' }
        },
        cutout: '65%'
      }
    });
  }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
