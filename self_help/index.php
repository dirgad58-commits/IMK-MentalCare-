<?php
// self_help/index.php — MentalCare Self-Help (INTERAKTIF + UI rapih + aksesibel)
// 1 file program: PHP + CSS + JS + AJAX save journaling ke DB

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

/**
 * AJAX: Quick Journal Save
 * POST: action=quick_journal, mood, title(optional), content
 * Response: JSON
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'quick_journal') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'message' => 'Sesi berakhir. Silakan login ulang.']);
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!$token || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'CSRF token tidak valid. Refresh halaman.']);
            exit;
        }

        $user_id = (int)$_SESSION['user_id'];
        $title   = trim((string)($_POST['title'] ?? ''));
        $content = trim((string)($_POST['content'] ?? ''));
        $mood    = trim((string)($_POST['mood'] ?? ''));

        $allowed_moods = ['senang','netral','sedih','cemas','marah'];

        if (!in_array($mood, $allowed_moods, true)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Mood tidak valid.']);
            exit;
        }
        if (mb_strlen($title) > 100) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Judul maksimal 100 karakter.']);
            exit;
        }
        if (mb_strlen($content) < 3) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Isi jurnal minimal 3 karakter.']);
            exit;
        }
        if (mb_strlen($content) > 5000) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Isi jurnal terlalu panjang (maks 5000 karakter).']);
            exit;
        }

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO journals (user_id, title, content, mood, created_at) VALUES (?, ?, ?, ?, NOW())"
        );
        if (!$stmt) throw new Exception('Prepare statement gagal.');

        mysqli_stmt_bind_param($stmt, 'isss', $user_id, $title, $content, $mood);
        if (!mysqli_stmt_execute($stmt)) throw new Exception('Execute gagal.');

        $new_id = mysqli_insert_id($conn);

        echo json_encode([
            'ok' => true,
            'message' => 'Jurnal tersimpan.',
            'journal_id' => (int)$new_id
        ]);
        exit;

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Terjadi error server.']);
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
  :root{
    --sh-radius: 18px;
    --sh-border: rgba(0,0,0,.08);
    --sh-soft: rgba(13,110,253,.08);
    --sh-soft2: rgba(111,66,193,.06);
  }

  :focus-visible{
    outline: 3px solid rgba(var(--bs-primary-rgb), .35);
    outline-offset: 2px;
    border-radius: 12px;
  }

  .badge-soft{
    background: rgba(var(--bs-primary-rgb), .10);
    color: var(--bs-primary);
    border: 1px solid rgba(var(--bs-primary-rgb), .18);
  }

  .selfhelp-hero{
    border-radius: var(--sh-radius);
    background: linear-gradient(135deg, var(--sh-soft), var(--sh-soft2));
    border: 1px solid var(--sh-border);
  }

  .selfhelp-card{
    border-radius: 16px;
    border: 1px solid var(--sh-border);
    transition: transform .12s ease, box-shadow .12s ease;
  }
  .selfhelp-card:hover{
    transform: translateY(-2px);
    box-shadow: 0 .75rem 1.25rem rgba(0,0,0,.08)!important;
  }

  .sh-box{
    border: 1px solid var(--sh-border);
    border-radius: var(--sh-radius);
    background: #fff;
    padding: 16px;
  }

  .sh-head{
    display:flex;
    justify-content: space-between;
    align-items:flex-start;
    gap: 12px;
    flex-wrap: wrap;
  }

  .sh-title{
    margin:0;
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -.01em;
  }

  .sh-sub{
    margin: 6px 0 0 0;
    color: #6c757d;
    max-width: 760px;
  }

  .sh-actions{
    display:flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items:center;
  }

  .sh-actions .btn{
    border-radius: 999px;
    padding: .45rem .9rem;
  }

  .sh-meta{
    display:flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items:center;
    margin-top: 12px;
  }

  .sh-chip{
    border: 1px solid var(--sh-border);
    background: #fff;
    border-radius: 999px;
    padding: 6px 10px;
    font-size: .9rem;
    display:inline-flex;
    align-items:center;
    gap: 8px;
  }

  .sh-phase{
    padding: 14px 16px;
    border-radius: 16px;
    background: rgba(0,0,0,.02);
    border: 1px solid var(--sh-border);
  }

  .sh-phase .label{
    color:#6c757d;
    font-weight: 700;
    font-size: .92rem;
    margin-bottom: 6px;
  }

  .sh-phase .value{
    font-weight: 900;
    letter-spacing: -.02em;
    font-size: clamp(1.6rem, 3.4vw, 2.3rem);
    line-height: 1.05;
    margin: 0 0 6px 0;
  }

  .sh-phase .hint{
    color:#6c757d;
    font-size: .95rem;
  }

  .mono{
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  }

  .ring{
    width: 96px; height: 96px;
    border-radius: 999px;
    background: conic-gradient(rgba(var(--bs-primary-rgb),.85) 0deg, rgba(var(--bs-primary-rgb),.12) 0deg);
    display:flex; align-items:center; justify-content:center;
    border: 1px solid var(--sh-border);
  }
  .ring-inner{
    width: 74px; height: 74px;
    border-radius: 999px;
    background: #fff;
    display:flex; align-items:center; justify-content:center;
    text-align:center;
    border: 1px solid rgba(0,0,0,.06);
    padding: 6px;
  }

  .sh-right{
    border-left: 1px dashed rgba(0,0,0,.10);
    padding-left: 16px;
  }
  @media (max-width: 991px){
    .sh-right{ border-left: none; padding-left: 0; border-top: 1px dashed rgba(0,0,0,.10); padding-top: 12px; }
  }

  .sh-note{
    border: 1px solid rgba(0,0,0,.08);
    background: rgba(0,0,0,.02);
    border-radius: 14px;
    padding: 12px 14px;
    color: #6c757d;
  }

  .sh-kpi{
    border: 1px solid var(--sh-border);
    border-radius: 16px;
    padding: 14px 16px;
    background: #fff;
  }

  .sh-kpi .label{ color:#6c757d; font-weight:700; font-size:.92rem; }
  .sh-kpi .value{ font-weight:900; letter-spacing:-.02em; font-size:1.15rem; }

  @media (prefers-reduced-motion: reduce){
    .selfhelp-card{ transition:none; }
  }
</style>

<div class="container-fluid">
  <div class="row">

    <!-- SIDEBAR -->
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <!-- MAIN -->
    <main class="col-md-9 col-lg-10 p-4">

      <!-- HERO -->
      <div class="selfhelp-hero card shadow-sm mb-4">
        <div class="card-body p-4 p-lg-5">
          <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
            <div>
              <div class="d-inline-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="badge badge-soft">Self-Help Toolkit</span>
                <span class="badge text-bg-light border">Interaktif</span>
                <span class="badge text-bg-light border">3–10 menit</span>
                <span class="badge text-bg-light border">Aksesibel</span>
              </div>

              <h2 class="fw-bold mb-2">Self-Help</h2>
              <p class="text-muted mb-0" style="max-width: 780px;">
                Latihan singkat yang bisa Anda jalankan langsung di web (timer, form, rangkuman, dan simpan jurnal).
                Ini bersifat pendukung, bukan pengganti bantuan profesional.
              </p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
              <a href="#toolkit" class="btn btn-primary rounded-pill">
                <i class="bi bi-grid-3x3-gap me-1"></i> Mulai Toolkit
              </a>
              <a href="#safety" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-shield-check me-1"></i> Catatan Keselamatan
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- PROGRESS + QUICK START -->
      <div class="row g-3 mb-4">
        <div class="col-12 col-lg-7">
          <div class="card shadow-sm h-100">
            <div class="card-body p-4">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-check2-circle text-primary fs-4"></i>
                <h5 class="fw-semibold mb-0">Progress hari ini</h5>
              </div>
              <p class="text-muted mb-3">
                Tandai modul yang selesai. Progress disimpan di perangkat Anda (tidak membebani database).
              </p>

              <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <div class="sh-kpi">
                  <div class="label">Status</div>
                  <div class="value mono" id="shProgressLabel">0/5 selesai</div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                  <button class="btn btn-outline-secondary rounded-pill" id="shResetProgress">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset hari ini
                  </button>
                </div>
              </div>

              <div class="progress mt-3" style="height: 10px;">
                <div class="progress-bar" role="progressbar" id="shProgressBar" style="width:0%"></div>
              </div>

              <hr class="my-4">

              <div class="sh-box">
                <div class="sh-head">
                  <div>
                    <h6 class="sh-title mb-0">Mode mulai cepat</h6>
                    <p class="sh-sub">Pilih kondisi Anda, sistem akan membuka modul yang sesuai.</p>
                  </div>

                  <div class="sh-actions">
                    <select class="form-select form-select-sm rounded-pill" id="shQuickSelect" style="min-width:240px;">
                      <option value="breathing">Cemas / gelisah → Pernapasan 4–7–8</option>
                      <option value="grounding">Panik ringan → Grounding 5–4–3–2–1</option>
                      <option value="overthinking">Overthinking → Pecah fakta & langkah kecil</option>
                      <option value="journaling">Pikiran penuh → Quick journaling</option>
                      <option value="sleep">Sulit tidur → Ritual tidur</option>
                    </select>
                    <button class="btn btn-sm btn-primary" id="shQuickStart">
                      <i class="bi bi-play-fill me-1"></i> Mulai
                    </button>
                  </div>
                </div>

                <div class="sh-note mt-3 mb-0" id="shQuickHint">
                  Rekomendasi: mulai dari <strong>Pernapasan</strong> bila tubuh terasa tegang.
                </div>
              </div>

            </div>
          </div>
        </div>

        <div class="col-12 col-lg-5">
          <div class="card shadow-sm h-100 border-warning">
            <div class="card-body p-4">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-info-circle text-warning fs-4"></i>
                <h5 class="fw-semibold mb-0">Cara pakai (ringkas)</h5>
              </div>
              <ul class="mb-0 text-muted">
                <li>Pilih 1 modul, jalankan sampai selesai.</li>
                <li>Jika Anda bingung, gunakan “Mode mulai cepat”.</li>
                <li>Tandai selesai agar progress terlihat.</li>
                <li>Jika kondisi memburuk, baca “Catatan keselamatan”.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- TOOLKIT CARDS -->
      <div id="toolkit" class="mb-3">
        <h4 class="fw-bold mb-1">Toolkit</h4>
        <p class="text-muted mb-3">Klik kartu untuk membuka modul. Interaksi terjadi di bagian “Latihan Terstruktur”.</p>

        <div class="row g-3">
          <div class="col-12 col-md-6 col-xl-4">
            <a class="card shadow-sm h-100 text-decoration-none selfhelp-card sh-jump" data-module="breathing" href="#breathing">
              <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-wind text-primary fs-4"></i>
                  <div class="fw-semibold">Pernapasan 4–7–8</div>
                </div>
                <div class="text-muted small">Timer terpandu + pause/resume + ring progress.</div>
                <div class="mt-3 badge text-bg-light border">± 3 menit</div>
              </div>
            </a>
          </div>

          <div class="col-12 col-md-6 col-xl-4">
            <a class="card shadow-sm h-100 text-decoration-none selfhelp-card sh-jump" data-module="grounding" href="#grounding">
              <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-compass text-primary fs-4"></i>
                  <div class="fw-semibold">Grounding 5–4–3–2–1</div>
                </div>
                <div class="text-muted small">Form isi contoh nyata + rangkuman otomatis + copy.</div>
                <div class="mt-3 badge text-bg-light border">± 2–5 menit</div>
              </div>
            </a>
          </div>

          <div class="col-12 col-md-6 col-xl-4">
            <a class="card shadow-sm h-100 text-decoration-none selfhelp-card sh-jump" data-module="overthinking" href="#overthinking">
              <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-brain text-primary fs-4"></i>
                  <div class="fw-semibold">Overthinking</div>
                </div>
                <div class="text-muted small">Tulis worry → sistem susun “fakta vs langkah kecil”.</div>
                <div class="mt-3 badge text-bg-light border">± 3 menit</div>
              </div>
            </a>
          </div>

          <div class="col-12 col-md-6 col-xl-4">
            <a class="card shadow-sm h-100 text-decoration-none selfhelp-card sh-jump" data-module="journaling" href="#journaling">
              <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-journal-text text-primary fs-4"></i>
                  <div class="fw-semibold">Quick Journaling</div>
                </div>
                <div class="text-muted small">Form singkat + simpan langsung ke database (AJAX).</div>
                <div class="mt-3 badge text-bg-light border">± 5 menit</div>
              </div>
            </a>
          </div>

          <div class="col-12 col-md-6 col-xl-4">
            <a class="card shadow-sm h-100 text-decoration-none selfhelp-card sh-jump" data-module="sleep" href="#sleep">
              <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-moon-stars text-primary fs-4"></i>
                  <div class="fw-semibold">Ritual Tidur</div>
                </div>
                <div class="text-muted small">Countdown 5/10 menit + checklist langkah.</div>
                <div class="mt-3 badge text-bg-light border">± 5–10 menit</div>
              </div>
            </a>
          </div>

          <div class="col-12 col-md-6 col-xl-4">
            <a class="card shadow-sm h-100 text-decoration-none selfhelp-card sh-jump" data-module="safety" href="#safety">
              <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bi bi-shield-check text-primary fs-4"></i>
                  <div class="fw-semibold">Catatan keselamatan</div>
                </div>
                <div class="text-muted small">Kapan harus mencari bantuan profesional.</div>
                <div class="mt-3 badge text-bg-light border">Wajib dibaca</div>
              </div>
            </a>
          </div>
        </div>
      </div>

      <!-- ACCORDION MODULES -->
      <div class="card shadow-sm mt-4">
        <div class="card-body p-4">
          <h4 class="fw-bold mb-1">Latihan Terstruktur</h4>
          <p class="text-muted mb-3">Buka modul, lakukan langkahnya. Jika sudah, tekan “Tandai selesai”.</p>

          <div class="accordion" id="selfHelpAccordion">

            <!-- BREATHING -->
            <div class="accordion-item" id="breathing">
              <h2 class="accordion-header" id="h1">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#c1" aria-expanded="true" aria-controls="c1">
                  <i class="bi bi-wind me-2"></i> Pernapasan 4–7–8 (Interaktif)
                </button>
              </h2>

              <div id="c1" class="accordion-collapse collapse show" aria-labelledby="h1" data-bs-parent="#selfHelpAccordion">
                <div class="accordion-body">

                  <div class="sh-box">
                    <div class="sh-head">
                      <div>
                        <h5 class="sh-title mb-0">Timer Pernapasan 4–7–8</h5>
                        <p class="sh-sub">
                          Tekan <strong>Mulai</strong>, ikuti instruksi. Anda bisa pause kapan pun. Fokus stabil, bukan cepat.
                        </p>
                      </div>

                      <div class="sh-actions">
                        <button class="btn btn-primary btn-sm" id="shBreathStart">
                          <i class="bi bi-play-fill me-1"></i> Mulai
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="shBreathPause" disabled>
                          <i class="bi bi-pause-fill me-1"></i> Pause
                        </button>
                        <button class="btn btn-outline-danger btn-sm" id="shBreathReset" disabled>
                          <i class="bi bi-stop-fill me-1"></i> Reset
                        </button>
                      </div>
                    </div>

                    <div class="sh-meta">
                      <label class="sh-chip mb-0">
                        <input class="form-check-input m-0" type="checkbox" id="shBreathEasy">
                        <span>Mode mudah <span class="mono">(4–4–6)</span></span>
                      </label>

                      <span class="sh-chip">
                        <i class="bi bi-repeat"></i>
                        <span id="shBreathCycle">Siklus 0/4</span>
                      </span>

                      <span class="sh-chip" role="status" aria-live="polite">
                        <i class="bi bi-activity"></i>
                        <span id="shBreathState">Siap</span>
                      </span>
                    </div>

                    <div class="row g-3 mt-2 align-items-stretch">
                      <div class="col-12 col-lg-8">
                        <div class="sh-phase h-100">
                          <div class="label">Instruksi saat ini</div>
                          <div class="value" id="shBreathPhase" aria-live="polite">Siap</div>
                          <div class="hint">
                            Sisa <span class="mono fw-bold" id="shBreathRemain">00</span> detik
                          </div>

                          <div class="sh-note mt-3">
                            Tip: bila pusing, hentikan dan kembali ke napas normal.
                          </div>
                        </div>
                      </div>

                      <div class="col-12 col-lg-4">
                        <div class="sh-right h-100 d-flex flex-column justify-content-between">
                          <div class="d-flex align-items-center gap-3">
                            <div class="ring" id="shBreathRing" aria-hidden="true">
                              <div class="ring-inner">
                                <div>
                                  <div class="mono fw-bold" id="shBreathRingPct">0%</div>
                                  <div class="text-muted" style="font-size:.85rem; line-height:1.15;">fase</div>
                                </div>
                              </div>
                            </div>

                            <div>
                              <div class="fw-semibold mb-1">Langkah ringkas</div>
                              <ol class="mb-0 ps-3">
                                <li>Tarik</li>
                                <li>Tahan</li>
                                <li>Hembus</li>
                                <li>Ulangi</li>
                              </ol>
                            </div>
                          </div>

                          <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                            <button class="btn btn-outline-success btn-sm sh-mark-done" data-module="breathing">
                              <i class="bi bi-check2 me-1"></i> Tandai selesai
                            </button>
                            <span class="text-muted" style="font-size:.92rem;">Saran: lanjut Grounding bila masih gelisah.</span>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>

                </div>
              </div>
            </div>

            <!-- GROUNDING -->
            <div class="accordion-item" id="grounding">
              <h2 class="accordion-header" id="h2">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c2" aria-expanded="false" aria-controls="c2">
                  <i class="bi bi-compass me-2"></i> Grounding 5–4–3–2–1 (Interaktif)
                </button>
              </h2>
              <div id="c2" class="accordion-collapse collapse" aria-labelledby="h2" data-bs-parent="#selfHelpAccordion">
                <div class="accordion-body">

                  <div class="sh-box">
                    <div class="sh-head">
                      <div>
                        <h5 class="sh-title mb-0">Grounding 5–4–3–2–1</h5>
                        <p class="sh-sub">
                          Isi contoh nyata di sekitar Anda. Setelah itu klik “Buat rangkuman” untuk merapikan fokus.
                        </p>
                      </div>

                      <div class="sh-actions">
                        <button class="btn btn-outline-success btn-sm sh-mark-done" data-module="grounding">
                          <i class="bi bi-check2 me-1"></i> Tandai selesai
                        </button>
                      </div>
                    </div>

                    <div class="row g-3 mt-2">
                      <div class="col-12 col-lg-6">
                        <div class="sh-phase">
                          <div class="label">Form 5–4–3–2–1</div>
                          <div id="shGroundingForm"></div>

                          <div class="d-flex gap-2 flex-wrap mt-2">
                            <button class="btn btn-primary btn-sm rounded-pill" id="shGroundingSummarize">
                              <i class="bi bi-magic me-1"></i> Buat rangkuman
                            </button>
                            <button class="btn btn-outline-secondary btn-sm rounded-pill" id="shGroundingClear">
                              <i class="bi bi-eraser me-1"></i> Bersihkan
                            </button>
                          </div>
                        </div>
                      </div>

                      <div class="col-12 col-lg-6">
                        <div class="sh-phase h-100">
                          <div class="label">Rangkuman (bisa di-copy)</div>
                          <textarea class="form-control" rows="12" id="shGroundingOutput" placeholder="Rangkuman akan muncul di sini..." readonly></textarea>

                          <div class="d-flex gap-2 flex-wrap mt-2">
                            <button class="btn btn-outline-primary btn-sm rounded-pill" id="shCopyGrounding">
                              <i class="bi bi-clipboard me-1"></i> Copy
                            </button>
                          </div>

                          <div class="sh-note mt-3">
                            Tip: jika sedang panik, cukup isi 1–2 item saja. Yang penting kembali ke “saat ini”.
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <!-- OVERTHINKING -->
            <div class="accordion-item" id="overthinking">
              <h2 class="accordion-header" id="h3">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c3" aria-expanded="false" aria-controls="c3">
                  <i class="bi bi-brain me-2"></i> Overthinking (Interaktif)
                </button>
              </h2>
              <div id="c3" class="accordion-collapse collapse" aria-labelledby="h3" data-bs-parent="#selfHelpAccordion">
                <div class="accordion-body">

                  <div class="sh-box">
                    <div class="sh-head">
                      <div>
                        <h5 class="sh-title mb-0">Overthinking → pecah jadi tindakan kecil</h5>
                        <p class="sh-sub">
                          Tulis 1 kekhawatiran. Sistem akan bantu membuat “fakta vs asumsi” dan 1 langkah ≤ 10 menit.
                        </p>
                      </div>

                      <div class="sh-actions">
                        <button class="btn btn-outline-success btn-sm sh-mark-done" data-module="overthinking">
                          <i class="bi bi-check2 me-1"></i> Tandai selesai
                        </button>
                      </div>
                    </div>

                    <div class="row g-3 mt-2">
                      <div class="col-12 col-lg-6">
                        <div class="sh-phase">
                          <div class="label">Kekhawatiran Anda</div>
                          <input type="text" class="form-control" id="shWorryInput" placeholder="Contoh: takut presentasi besok berantakan...">

                          <div class="d-flex gap-2 flex-wrap mt-2">
                            <button class="btn btn-primary btn-sm rounded-pill" id="shWorryGenerate">
                              <i class="bi bi-lightning-charge me-1"></i> Buat 1 langkah kecil
                            </button>
                            <button class="btn btn-outline-secondary btn-sm rounded-pill" id="shWorryClear">
                              <i class="bi bi-eraser me-1"></i> Reset
                            </button>
                          </div>

                          <div class="sh-note mt-3">
                            Tip: jangan cari solusi sempurna. Cari “langkah pertama” yang menurunkan beban mental.
                          </div>
                        </div>
                      </div>

                      <div class="col-12 col-lg-6">
                        <div class="sh-phase h-100">
                          <div class="label">Hasil</div>
                          <textarea class="form-control" rows="12" id="shWorryOutput" readonly
                                    placeholder="Hasil akan muncul di sini..."></textarea>

                          <div class="d-flex gap-2 flex-wrap mt-2">
                            <button class="btn btn-outline-primary btn-sm rounded-pill" id="shCopyWorry">
                              <i class="bi bi-clipboard me-1"></i> Copy
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>

                </div>
              </div>
            </div>

            <!-- JOURNALING -->
            <div class="accordion-item" id="journaling">
              <h2 class="accordion-header" id="h4">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c4" aria-expanded="false" aria-controls="c4">
                  <i class="bi bi-journal-text me-2"></i> Quick Journaling (Simpan ke DB)
                </button>
              </h2>
              <div id="c4" class="accordion-collapse collapse" aria-labelledby="h4" data-bs-parent="#selfHelpAccordion">
                <div class="accordion-body">

                  <div class="sh-box">
                    <div class="sh-head">
                      <div>
                        <h5 class="sh-title mb-0">Quick Journaling 3 kalimat</h5>
                        <p class="sh-sub">
                          Tulis singkat. Tekan “Simpan” untuk menyimpan ke tabel <span class="mono">journals</span> tanpa reload halaman.
                        </p>
                      </div>

                      <div class="sh-actions">
                        <button class="btn btn-outline-success btn-sm sh-mark-done" type="button" data-module="journaling">
                          <i class="bi bi-check2 me-1"></i> Tandai selesai
                        </button>
                      </div>
                    </div>

                    <form id="shQuickJournalForm" class="mt-3">
                      <input type="hidden" name="action" value="quick_journal">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf); ?>">

                      <div class="row g-3">
                        <div class="col-12 col-lg-4">
                          <label class="fw-semibold mb-2">Mood</label>
                          <select class="form-select" name="mood" required>
                            <option value="">Pilih mood...</option>
                            <option value="senang">Senang</option>
                            <option value="netral">Netral</option>
                            <option value="sedih">Sedih</option>
                            <option value="cemas">Cemas</option>
                            <option value="marah">Marah</option>
                          </select>
                        </div>

                        <div class="col-12 col-lg-8">
                          <label class="fw-semibold mb-2">Judul (opsional)</label>
                          <input type="text" class="form-control" name="title" maxlength="100" placeholder="Contoh: Pikiran hari ini...">
                        </div>

                        <div class="col-12">
                          <label class="fw-semibold mb-2">Isi jurnal</label>
                          <textarea class="form-control" name="content" rows="6" required
                                    placeholder="Template: Hari ini saya merasa..., Pemicu..., Langkah kecil besok..."></textarea>
                          <div class="text-muted mt-2" style="font-size:.92rem;">
                            Minimal 3 karakter. Maks 5000 karakter.
                          </div>
                        </div>
                      </div>

                      <div class="d-flex gap-2 flex-wrap mt-3">
                        <button class="btn btn-primary rounded-pill" type="submit" id="shJournalSaveBtn">
                          <i class="bi bi-save me-1"></i> Simpan
                        </button>

                        <button class="btn btn-outline-secondary rounded-pill" type="button" id="shJournalFillTemplate">
                          <i class="bi bi-pencil-square me-1"></i> Isi template
                        </button>
                      </div>

                      <div class="sh-note mt-3" id="shJournalStatus">Status: belum ada penyimpanan.</div>
                    </form>

                  </div>

                </div>
              </div>
            </div>

            <!-- SLEEP -->
            <div class="accordion-item" id="sleep">
              <h2 class="accordion-header" id="h5">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c5" aria-expanded="false" aria-controls="c5">
                  <i class="bi bi-moon-stars me-2"></i> Ritual Tidur (Interaktif)
                </button>
              </h2>
              <div id="c5" class="accordion-collapse collapse" aria-labelledby="h5" data-bs-parent="#selfHelpAccordion">
                <div class="accordion-body">

                  <div class="sh-box">
                    <div class="sh-head">
                      <div>
                        <h5 class="sh-title mb-0">Ritual tidur singkat</h5>
                        <p class="sh-sub">
                          Jalankan langkah sederhana. Tekan “Mulai” untuk countdown. Tidak perlu sempurna.
                        </p>
                      </div>

                      <div class="sh-actions">
                        <select class="form-select form-select-sm rounded-pill" id="shSleepMinutes" style="min-width:140px;">
                          <option value="5">5 menit</option>
                          <option value="10" selected>10 menit</option>
                        </select>

                        <button class="btn btn-primary btn-sm" id="shSleepStart">
                          <i class="bi bi-play-fill me-1"></i> Mulai
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="shSleepPause" disabled>
                          <i class="bi bi-pause-fill me-1"></i> Pause
                        </button>
                        <button class="btn btn-outline-danger btn-sm" id="shSleepReset" disabled>
                          <i class="bi bi-stop-fill me-1"></i> Reset
                        </button>

                        <button class="btn btn-outline-success btn-sm sh-mark-done" data-module="sleep">
                          <i class="bi bi-check2 me-1"></i> Tandai selesai
                        </button>
                      </div>
                    </div>

                    <div class="row g-3 mt-2">
                      <div class="col-12 col-lg-6">
                        <div class="sh-phase">
                          <div class="label">Checklist</div>

                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="sl1">
                            <label class="form-check-label" for="sl1">Jauhkan layar (bila memungkinkan)</label>
                          </div>

                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="sl2">
                            <label class="form-check-label" for="sl2">Napas 6 kali (tarik 4, hembus 6)</label>
                          </div>

                          <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="sl3">
                            <label class="form-check-label" for="sl3">Relaksasi bahu (tegang 3 detik, lepas)</label>
                          </div>

                          <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="sl4">
                            <label class="form-check-label" for="sl4">Tulis 1 kalimat “besok saya urus”</label>
                          </div>

                          <textarea class="form-control" id="shTomorrowLine" rows="2" placeholder="Besok saya urus: ..."></textarea>

                          <div class="d-flex gap-2 flex-wrap mt-2">
                            <button class="btn btn-outline-primary btn-sm rounded-pill" id="shCopyTomorrow">
                              <i class="bi bi-clipboard me-1"></i> Copy kalimat
                            </button>
                          </div>

                          <div class="sh-note mt-3">
                            Kalimat “besok saya urus” memberi sinyal ke otak untuk berhenti “problem solving” malam hari.
                          </div>
                        </div>
                      </div>

                      <div class="col-12 col-lg-6">
                        <div class="sh-phase h-100">
                          <div class="label">Timer</div>
                          <div class="value mono" style="font-size:2rem;" id="shSleepTimer">10:00</div>

                          <div class="progress" style="height: 10px;">
                            <div class="progress-bar" id="shSleepBar" style="width:0%"></div>
                          </div>

                          <div class="sh-note mt-3" id="shSleepHint">
                            Tekan “Mulai” untuk menjalankan countdown.
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>

                </div>
              </div>
            </div>

          </div> <!-- /accordion -->
        </div>
      </div>

      <!-- SAFETY -->
      <div id="safety" class="card shadow-sm border-warning mt-4">
        <div class="card-body p-4">
          <div class="d-flex align-items-start gap-3">
            <i class="bi bi-shield-check text-warning fs-3"></i>
            <div>
              <h4 class="fw-bold mb-1">Catatan Keselamatan</h4>
              <p class="text-muted mb-3">
                Self-help bersifat pendukung. Bila Anda merasa kondisi memburuk, kehilangan kendali,
                atau berada dalam situasi darurat, prioritaskan bantuan profesional.
              </p>
              <div class="alert alert-warning mb-0">
                Jika ini situasi darurat, segera hubungi layanan darurat setempat atau orang terdekat yang bisa membantu.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TOAST -->
      <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
        <div id="shToast" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body" id="shToastBody">Notifikasi</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<script>
(function(){
  // NOTE:
  // Halaman ini butuh Bootstrap Bundle JS (bootstrap.Collapse + bootstrap.Toast).
  // Pastikan footer.php memuat bootstrap.bundle.min.js.

  const $ = (id) => document.getElementById(id);

  function toast(msg){
    const el = $('shToast');
    const body = $('shToastBody');
    if (!el || !body) return alert(msg);
    body.textContent = msg;
    if (!window.bootstrap) return alert(msg);
    bootstrap.Toast.getOrCreateInstance(el, { delay: 1700 }).show();
  }

  // ===== Progress (localStorage) =====
  const MODULES = ['breathing','grounding','overthinking','journaling','sleep'];

  function todayKey(){
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const day = String(d.getDate()).padStart(2,'0');
    return `mc_sh_done_${y}-${m}-${day}`;
  }

  function getDone(){
    try { return JSON.parse(localStorage.getItem(todayKey()) || '[]'); }
    catch(e){ return []; }
  }

  function setDone(arr){
    localStorage.setItem(todayKey(), JSON.stringify(arr));
  }

  function updateProgress(){
    const done = getDone();
    const doneCount = MODULES.filter(m => done.includes(m)).length;
    const pct = Math.round((doneCount / MODULES.length) * 100);

    if ($('shProgressLabel')) $('shProgressLabel').textContent = `${doneCount}/${MODULES.length} selesai`;
    if ($('shProgressBar')) $('shProgressBar').style.width = pct + '%';
  }

  function markDone(module){
    if (!MODULES.includes(module)) return;
    const done = getDone();
    if (!done.includes(module)) done.push(module);
    setDone(done);
    updateProgress();
    toast(`Modul "${module}" ditandai selesai.`);
  }

  $('shResetProgress')?.addEventListener('click', () => {
    localStorage.removeItem(todayKey());
    updateProgress();
    toast('Progress hari ini direset.');
  });

  document.querySelectorAll('.sh-mark-done').forEach(btn => {
    btn.addEventListener('click', () => markDone(btn.dataset.module));
  });

  // ===== Jump + Open Accordion =====
  const mapCollapse = { breathing:'c1', grounding:'c2', overthinking:'c3', journaling:'c4', sleep:'c5' };

  function openModule(module){
    const target = document.getElementById(module);
    if (target) target.scrollIntoView({ behavior:'smooth', block:'start' });

    if (window.bootstrap && mapCollapse[module] && $(mapCollapse[module])) {
      bootstrap.Collapse.getOrCreateInstance($(mapCollapse[module])).show();
    }

    const hintMap = {
      breathing: 'Rekomendasi: mulai dari <strong>Pernapasan</strong> bila tubuh terasa tegang.',
      grounding: 'Rekomendasi: gunakan <strong>Grounding</strong> bila Anda merasa panik ringan / “melayang”.',
      overthinking: 'Rekomendasi: tulis 1 worry → fokus 1 langkah kecil (≤ 10 menit).',
      journaling: 'Rekomendasi: cukup 3 kalimat. Konsisten lebih penting.',
      sleep: 'Rekomendasi: jalankan ritual 10 menit sebelum tidur.'
    };
    if ($('shQuickHint') && hintMap[module]) $('shQuickHint').innerHTML = hintMap[module];
  }

  document.querySelectorAll('.sh-jump').forEach(a => {
    a.addEventListener('click', (e) => {
      const module = a.dataset.module;
      if (module && mapCollapse[module]) {
        e.preventDefault();
        openModule(module);
      }
    });
  });

  $('shQuickStart')?.addEventListener('click', () => {
    const mod = $('shQuickSelect')?.value || 'breathing';
    openModule(mod);
    toast('Modul dibuka. Jalankan langkahnya.');
  });

  $('shQuickSelect')?.addEventListener('change', () => {
    const mod = $('shQuickSelect').value;
    const hintMap = {
      breathing: 'Rekomendasi: mulai dari <strong>Pernapasan</strong> bila tubuh terasa tegang.',
      grounding: 'Rekomendasi: gunakan <strong>Grounding</strong> bila panik ringan.',
      overthinking: 'Rekomendasi: pecah jadi <strong>1 langkah kecil</strong>.',
      journaling: 'Rekomendasi: tulis singkat, jangan menilai.',
      sleep: 'Rekomendasi: ritual 10 menit untuk menutup hari.'
    };
    if ($('shQuickHint') && hintMap[mod]) $('shQuickHint').innerHTML = hintMap[mod];
  });

  // ===== Grounding Form =====
  function buildGrounding(){
    const container = $('shGroundingForm');
    if (!container) return;

    const groups = [
      { n: 5, label: '5 hal yang Anda lihat', key: 'see' },
      { n: 4, label: '4 hal yang Anda rasakan', key: 'feel' },
      { n: 3, label: '3 hal yang Anda dengar', key: 'hear' },
      { n: 2, label: '2 hal yang Anda cium', key: 'smell' },
      { n: 1, label: '1 hal di mulut (air/minum)', key: 'taste' },
    ];

    container.innerHTML = '';
    groups.forEach(g => {
      const wrap = document.createElement('div');
      wrap.className = 'mb-3';

      const title = document.createElement('div');
      title.className = 'fw-semibold mb-2';
      title.textContent = g.label;
      wrap.appendChild(title);

      for (let i=1; i<=g.n; i++){
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm mb-2';
        input.placeholder = `Contoh ${i}...`;
        input.dataset.group = g.key;
        wrap.appendChild(input);
      }

      container.appendChild(wrap);
    });
  }

  function groundingValues(){
    const inputs = Array.from(document.querySelectorAll('#shGroundingForm input'));
    const data = { see:[], feel:[], hear:[], smell:[], taste:[] };
    inputs.forEach(inp => {
      const v = (inp.value || '').trim();
      if (v) data[inp.dataset.group].push(v);
    });
    return data;
  }

  $('shGroundingSummarize')?.addEventListener('click', () => {
    const d = groundingValues();
    const out = [];
    out.push('RANGKUMAN GROUNDING 5–4–3–2–1');
    out.push('--------------------------------');
    out.push(`5 lihat: ${d.see.length ? d.see.join(', ') : '-'}`);
    out.push(`4 rasa: ${d.feel.length ? d.feel.join(', ') : '-'}`);
    out.push(`3 dengar: ${d.hear.length ? d.hear.join(', ') : '-'}`);
    out.push(`2 cium: ${d.smell.length ? d.smell.join(', ') : '-'}`);
    out.push(`1 mulut: ${d.taste.length ? d.taste.join(', ') : '-'}`);

    if ($('shGroundingOutput')) $('shGroundingOutput').value = out.join('\n');
    toast('Rangkuman dibuat.');
  });

  $('shGroundingClear')?.addEventListener('click', () => {
    document.querySelectorAll('#shGroundingForm input').forEach(i => i.value = '');
    if ($('shGroundingOutput')) $('shGroundingOutput').value = '';
    toast('Form dibersihkan.');
  });

  $('shCopyGrounding')?.addEventListener('click', async () => {
    const text = ($('shGroundingOutput')?.value || '').trim();
    if (!text) return toast('Belum ada rangkuman.');
    try{
      await navigator.clipboard.writeText(text);
      toast('Rangkuman dicopy.');
    }catch(e){
      toast('Gagal copy. Copy manual dari textarea.');
    }
  });

  // ===== Overthinking generator =====
  $('shWorryGenerate')?.addEventListener('click', () => {
    const worry = ($('shWorryInput')?.value || '').trim();
    if (!worry) return toast('Tulis dulu kekhawatiran Anda.');

    const output =
`FAKTA vs ASUMSI
- Fakta yang saya tahu (1 kalimat): ...
- Asumsi yang muncul: "${worry}"

LANGKAH KECIL (≤ 10 menit)
- Pilih 1:
  1) Buat 3 bullet poin inti
  2) Latihan 60 detik (rekam / baca)
  3) Tanyakan 1 hal yang paling jelas ke orang terkait

KALIMAT JANGKAR
- "Saya cukup melakukan langkah kecil sekarang."`;

    if ($('shWorryOutput')) $('shWorryOutput').value = output;
    toast('Rencana dibuat.');
  });

  $('shWorryClear')?.addEventListener('click', () => {
    if ($('shWorryInput')) $('shWorryInput').value = '';
    if ($('shWorryOutput')) $('shWorryOutput').value = '';
    toast('Reset selesai.');
  });

  $('shCopyWorry')?.addEventListener('click', async () => {
    const text = ($('shWorryOutput')?.value || '').trim();
    if (!text) return toast('Belum ada hasil.');
    try{
      await navigator.clipboard.writeText(text);
      toast('Hasil dicopy.');
    }catch(e){
      toast('Gagal copy. Copy manual dari textarea.');
    }
  });

  // ===== Quick journaling =====
  $('shJournalFillTemplate')?.addEventListener('click', () => {
    const ta = document.querySelector('#shQuickJournalForm textarea[name="content"]');
    if (!ta) return;
    const template =
`Hari ini saya merasa: ...
Pemicu utamanya: ...
Langkah kecil besok: ...`;
    if (!ta.value.trim()) ta.value = template;
    toast('Template diisi.');
  });

  $('shQuickJournalForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const btn = $('shJournalSaveBtn');
    const status = $('shJournalStatus');

    const url = window.location.href.split('#')[0];
    const fd = new FormData(form);

    try{
      btn.disabled = true;
      btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Menyimpan...';
      status.textContent = 'Status: menyimpan...';

      const res = await fetch(url, { method:'POST', body: fd });
      const data = await res.json().catch(() => null);

      if (!res.ok || !data || !data.ok){
        const msg = (data && data.message) ? data.message : 'Gagal menyimpan.';
        status.textContent = 'Status: ' + msg;
        toast(msg);
        return;
      }

      status.textContent = `Status: ${data.message} (ID: ${data.journal_id})`;
      toast('Jurnal tersimpan.');
      markDone('journaling');

    }catch(err){
      status.textContent = 'Status: error jaringan/server.';
      toast('Error jaringan/server.');
    }finally{
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan';
    }
  });

  // ===== Breathing timer (fix siklus & UI) =====
  let breath = {
    running:false, paused:false,
    phaseIndex:0, phaseRemain:0, phaseTotal:0,
    cycle:0, cyclesTotal:4,
    tickHandle:null,
    phases:[]
  };

  function buildBreathPhases(){
    const easy = $('shBreathEasy')?.checked;
    breath.phases = easy
      ? [{label:'Tarik napas', sec:4},{label:'Tahan',sec:4},{label:'Hembuskan',sec:6}]
      : [{label:'Tarik napas', sec:4},{label:'Tahan',sec:7},{label:'Hembuskan',sec:8}];
  }

  function setRingProgress(pct){
    const ring = $('shBreathRing');
    const pctEl = $('shBreathRingPct');
    const deg = Math.max(0, Math.min(100, pct)) * 3.6;
    if (ring) ring.style.background = `conic-gradient(rgba(var(--bs-primary-rgb),.85) ${deg}deg, rgba(var(--bs-primary-rgb),.12) 0deg)`;
    if (pctEl) pctEl.textContent = `${Math.round(pct)}%`;
  }

  function breathUI(){
    if ($('shBreathCycle')) $('shBreathCycle').textContent = `Siklus ${breath.cycle}/${breath.cyclesTotal}`;
    if ($('shBreathPhase')) $('shBreathPhase').textContent = breath.running ? breath.phases[breath.phaseIndex].label : 'Siap';
    if ($('shBreathState')) $('shBreathState').textContent = breath.running ? (breath.paused ? 'Pause' : 'Berjalan') : 'Siap';
    if ($('shBreathRemain')) $('shBreathRemain').textContent = String(breath.phaseRemain).padStart(2,'0');

    const pct = breath.phaseTotal ? (100 * (breath.phaseTotal - breath.phaseRemain) / breath.phaseTotal) : 0;
    setRingProgress(breath.running ? pct : 0);
  }

  function breathFinish(){
    clearInterval(breath.tickHandle);
    breath.tickHandle = null;
    breath.running = false;
    breath.paused = false;

    $('shBreathStart').disabled = false;
    $('shBreathPause').disabled = true;
    $('shBreathReset').disabled = true;

    breathUI();
    toast('Selesai. Anda bisa tandai modul selesai.');
  }

  function breathResetAll(){
    clearInterval(breath.tickHandle);
    breath.tickHandle = null;
    breath.running = false;
    breath.paused = false;
    breath.phaseIndex = 0;
    breath.phaseTotal = 0;
    breath.phaseRemain = 0;
    breath.cycle = 0;

    $('shBreathStart').disabled = false;
    $('shBreathPause').disabled = true;
    $('shBreathReset').disabled = true;

    const pauseBtn = $('shBreathPause');
    if (pauseBtn) pauseBtn.innerHTML = '<i class="bi bi-pause-fill me-1"></i> Pause';

    breathUI();
  }

  function breathStart(){
    buildBreathPhases();
    breath.running = true;
    breath.paused = false;

    breath.cycle = 1;
    breath.phaseIndex = 0;
    breath.phaseTotal = breath.phases[0].sec;
    breath.phaseRemain = breath.phaseTotal;

    $('shBreathStart').disabled = true;
    $('shBreathPause').disabled = false;
    $('shBreathReset').disabled = false;

    breathUI();

    breath.tickHandle = setInterval(() => {
      if (!breath.running || breath.paused) return;

      breath.phaseRemain -= 1;

      if (breath.phaseRemain <= 0){
        const lastPhaseIndex = breath.phases.length - 1;

        if (breath.phaseIndex === lastPhaseIndex) {
          // selesai 1 siklus
          if (breath.cycle === breath.cyclesTotal) {
            breath.phaseRemain = 0;
            breathUI();
            breathFinish();
            return;
          }
          breath.cycle += 1;
          breath.phaseIndex = 0;
        } else {
          breath.phaseIndex += 1;
        }

        breath.phaseTotal = breath.phases[breath.phaseIndex].sec;
        breath.phaseRemain = breath.phaseTotal;
        breathUI();
      } else {
        breathUI();
      }
    }, 1000);
  }

  $('shBreathStart')?.addEventListener('click', () => {
    if (breath.running) return;
    breathStart();
    toast('Timer napas dimulai.');
  });

  $('shBreathPause')?.addEventListener('click', () => {
    if (!breath.running) return;
    breath.paused = !breath.paused;

    const btn = $('shBreathPause');
    if (btn) {
      btn.innerHTML = breath.paused
        ? '<i class="bi bi-play-fill me-1"></i> Lanjut'
        : '<i class="bi bi-pause-fill me-1"></i> Pause';
    }
    breathUI();
  });

  $('shBreathReset')?.addEventListener('click', () => {
    breathResetAll();
    toast('Timer direset.');
  });

  $('shBreathEasy')?.addEventListener('change', () => {
    toast('Mode napas diubah. Berlaku saat mulai ulang.');
  });

  // ===== Sleep timer =====
  let sleep = { running:false, paused:false, totalSec:600, remainSec:600, handle:null };

  function fmt(sec){
    const m = Math.floor(sec/60);
    const s = sec%60;
    return `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  }

  function sleepUI(){
    if ($('shSleepTimer')) $('shSleepTimer').textContent = fmt(sleep.remainSec);
    const pct = sleep.totalSec ? (100 * (sleep.totalSec - sleep.remainSec) / sleep.totalSec) : 0;
    if ($('shSleepBar')) $('shSleepBar').style.width = Math.max(0, Math.min(100, pct)) + '%';
  }

  function sleepStop(reset=true){
    sleep.running = false;
    sleep.paused = false;
    if (sleep.handle) clearInterval(sleep.handle);
    sleep.handle = null;

    $('shSleepStart').disabled = false;
    $('shSleepPause').disabled = true;
    $('shSleepReset').disabled = true;
    $('shSleepPause').innerHTML = '<i class="bi bi-pause-fill me-1"></i> Pause';

    if (reset){
      const mins = parseInt($('shSleepMinutes')?.value || '10', 10);
      sleep.totalSec = mins*60;
      sleep.remainSec = sleep.totalSec;
      sleepUI();
    }
  }

  function sleepStart(){
    const mins = parseInt($('shSleepMinutes')?.value || '10', 10);
    sleep.totalSec = mins*60;
    sleep.remainSec = sleep.totalSec;

    sleep.running = true;
    sleep.paused = false;

    $('shSleepStart').disabled = true;
    $('shSleepPause').disabled = false;
    $('shSleepReset').disabled = false;

    if ($('shSleepHint')) $('shSleepHint').textContent = 'Ikuti checklist pelan-pelan. Tidak perlu cepat.';
    sleepUI();

    sleep.handle = setInterval(() => {
      if (!sleep.running || sleep.paused) return;

      sleep.remainSec -= 1;
      if (sleep.remainSec <= 0){
        sleep.remainSec = 0;
        sleepUI();
        sleepStop(false);
        if ($('shSleepHint')) $('shSleepHint').textContent = 'Selesai. Anda bisa tandai modul selesai.';
        toast('Countdown selesai.');
        return;
      }
      sleepUI();
    }, 1000);
  }

  $('shSleepMinutes')?.addEventListener('change', () => {
    if (sleep.running) return toast('Ubah durasi setelah reset.');
    sleepStop(true);
    toast('Durasi diubah.');
  });

  $('shSleepStart')?.addEventListener('click', () => {
    if (sleep.running) return;
    sleepStart();
    toast('Countdown dimulai.');
  });

  $('shSleepPause')?.addEventListener('click', () => {
    if (!sleep.running) return;
    sleep.paused = !sleep.paused;
    $('shSleepPause').innerHTML = sleep.paused
      ? '<i class="bi bi-play-fill me-1"></i> Lanjut'
      : '<i class="bi bi-pause-fill me-1"></i> Pause';
    toast(sleep.paused ? 'Pause.' : 'Lanjut.');
  });

  $('shSleepReset')?.addEventListener('click', () => {
    if (!sleep.running) return;
    sleepStop(true);
    toast('Countdown direset.');
  });

  $('shCopyTomorrow')?.addEventListener('click', async () => {
    const t = ($('shTomorrowLine')?.value || '').trim();
    if (!t) return toast('Kalimat masih kosong.');
    try{
      await navigator.clipboard.writeText(t);
      toast('Kalimat dicopy.');
    }catch(e){
      toast('Gagal copy. Copy manual dari textarea.');
    }
  });

  // ===== Init =====
  buildGrounding();
  updateProgress();
  sleepStop(true);
  breathUI();

})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
