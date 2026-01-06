<?php
// IMK/index.php - Landing Page MentalCare (HCI-ready)
// Aman untuk XAMPP + project /IMK

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';

$BASE_URL = '/IMK/';

function getCount(mysqli $conn, string $sql): int {
    $res = mysqli_query($conn, $sql);
    if (!$res) return 0;
    $row = mysqli_fetch_row($res);
    return (int)($row[0] ?? 0);
}

$totalQuestions = getCount($conn, "SELECT COUNT(*) FROM questions");
$totalAnswers   = getCount($conn, "SELECT COUNT(*) FROM answers");
$totalJournals  = getCount($conn, "SELECT COUNT(*) FROM journals");

$isLoggedIn = isset($_SESSION['user_id']);

// CSRF token untuk form landing
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Alert setelah submit (query string)
$contact_success = $_GET['contact_success'] ?? '';
$contact_error   = $_GET['contact_error'] ?? '';

$page_title = 'MentalCare — Journal & Support';
?>
<?php include __DIR__ . '/includes/header_landing.php'; ?>
<?php include __DIR__ . '/includes/navbar_landing.php'; ?>

<a class="skip-link" href="#main">Lewati ke konten</a>

<main id="main" tabindex="-1">

  <!-- ================= HERO ================= -->
  <section id="beranda" class="hero">
    <div class="container">
      <div class="row align-items-center g-4">

        <div class="col-lg-6">
          <div class="hero-card">

            <div class="badge-soft mb-3">
              <i class="bi bi-shield-check"></i>
              Ruang refleksi dan dukungan yang bertanggung jawab
            </div>

            <h1 class="font-head mb-3">
              MentalCare Journal & Support
            </h1>

            <p class="lead mb-4">
              Platform pendamping kesehatan mental untuk membantu Anda menulis jurnal,
              berdiskusi secara suportif, dan mengakses toolkit self-help.
              MentalCare bersifat pendukung dan tidak menggantikan layanan profesional.
            </p>

            <div class="d-flex flex-wrap gap-2">
              <?php if($isLoggedIn): ?>
                <a href="<?= $BASE_URL ?>discussion/index.php" class="btn btn-light btn-lg fw-semibold">
                  <i class="bi bi-chat-dots me-1"></i>Buka Diskusi
                </a>
                <a href="<?= $BASE_URL ?>journal/create.php" class="btn btn-outline-ghost btn-lg fw-semibold">
                  <i class="bi bi-journal-text me-1"></i>Tulis Jurnal
                </a>
              <?php else: ?>
                <a href="<?= $BASE_URL ?>register.php" class="btn btn-light btn-lg fw-semibold">
                  <i class="bi bi-rocket-takeoff me-1"></i>Mulai Sekarang
                </a>
                <a href="<?= $BASE_URL ?>login.php" class="btn btn-outline-ghost btn-lg fw-semibold">
                  <i class="bi bi-box-arrow-in-right me-1"></i>Saya sudah punya akun
                </a>
              <?php endif; ?>
            </div>

            <div class="mt-4 d-flex flex-wrap gap-2">
              <span class="badge rounded-pill text-bg-light text-dark">
                <i class="bi bi-incognito me-1"></i>Opsi anonim di diskusi
              </span>
              <span class="badge rounded-pill text-bg-light text-dark">
                <i class="bi bi-lock me-1"></i>Jurnal pribadi milik Anda
              </span>
              <span class="badge rounded-pill text-bg-light text-dark">
                <i class="bi bi-clock-history me-1"></i>Latihan 3–10 menit
              </span>
            </div>

          </div>
        </div>

        <div class="col-lg-6">
          <div class="stats-box">

            <div class="text-center mb-3">
              <div class="icon-pulse mx-auto">
                <i class="bi bi-heart-pulse"></i>
              </div>
            </div>

            <h3 class="font-head text-center mb-2">Ringkas, jelas, dan relevan</h3>
            <p class="text-center text-muted">
              Mulai dari check-in singkat, tulis jurnal, atau ajukan pertanyaan.
              Semua fitur dirancang untuk alur yang sederhana.
            </p>

            <div class="row g-3 mt-2">
              <div class="col-4">
                <div class="stat-mini text-center">
                  <div class="num"><?= $totalQuestions; ?></div>
                  <div class="lbl">Pertanyaan</div>
                </div>
              </div>
              <div class="col-4">
                <div class="stat-mini text-center">
                  <div class="num"><?= $totalAnswers; ?></div>
                  <div class="lbl">Jawaban</div>
                </div>
              </div>
              <div class="col-4">
                <div class="stat-mini text-center">
                  <div class="num"><?= $totalJournals; ?></div>
                  <div class="lbl">Jurnal</div>
                </div>
              </div>
            </div>

            <div class="notice mt-4" role="note" aria-label="Peringatan keamanan">
              <div class="d-flex gap-2 align-items-start">
                <i class="bi bi-info-circle" style="color:var(--dark);"></i>
                <div>
                  Jika Anda sedang dalam kondisi darurat atau merasa tidak aman,
                  segera hubungi layanan darurat setempat atau tenaga profesional.
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </section>
  <!-- ================= END HERO ================= -->


  <!-- ================= FITUR ================= -->
  <section id="fitur" class="section">
    <div class="container">
      <div class="row align-items-end mb-4">
        <div class="col-lg-7">
          <h2 class="section-title font-head mb-2">Fitur Utama</h2>
          <p class="section-sub">
            Fokus pada refleksi diri, dukungan komunitas, dan latihan singkat yang mudah dipraktikkan.
          </p>
        </div>
        <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
          <?php if($isLoggedIn): ?>
            <a href="<?= $BASE_URL ?>dashboard/index.php" class="btn btn-brand">
              <i class="bi bi-speedometer2 me-1"></i>Masuk Dashboard
            </a>
          <?php else: ?>
            <a href="<?= $BASE_URL ?>register.php" class="btn btn-brand">
              <i class="bi bi-rocket-takeoff me-1"></i>Mulai Sekarang
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="icon-pill"><i class="bi bi-journal-text"></i></div>
            <h6>Jurnal Pribadi</h6>
            <p>Catat perasaan dan refleksi harian dengan pilihan mood yang terstruktur.</p>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="icon-pill"><i class="bi bi-chat-dots"></i></div>
            <h6>Diskusi Q&A</h6>
            <p>Ajukan pertanyaan seperti forum/Quora dan dapatkan jawaban yang suportif.</p>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="icon-pill"><i class="bi bi-incognito"></i></div>
            <h6>Mode Anonim</h6>
            <p>Posting pertanyaan sebagai anonim untuk kenyamanan dan privasi pengguna.</p>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="icon-pill"><i class="bi bi-lightbulb"></i></div>
            <h6>Self-Help Toolkit</h6>
            <p>Latihan singkat 3–10 menit: napas, grounding, dan panduan coping harian.</p>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="icon-pill"><i class="bi bi-shield-check"></i></div>
            <h6>Prinsip Aman</h6>
            <p>Menjaga pengalaman tetap bertanggung jawab (bukan pengganti profesional).</p>
          </div>
        </div>

        <div class="col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="icon-pill"><i class="bi bi-graph-up"></i></div>
            <h6>Ringkasan Aktivitas</h6>
            <p>Lihat statistik sederhana agar pengguna paham progres dan kebiasaan.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ================= END FITUR ================= -->


  <!-- ================= CARA KERJA ================= -->
  <section id="alur" class="section pt-0">
    <div class="container">
      <div class="row g-4 align-items-start">

        <div class="col-lg-5">
          <div class="soft-card p-4">
            <h2 class="section-title font-head mb-2">Cara Kerja</h2>
            <p class="section-sub mb-3">
              Alur sederhana yang dapat diikuti pengguna dari awal sampai terbiasa.
            </p>

            <div class="d-flex flex-wrap gap-2">
              <span class="badge rounded-pill text-bg-primary">Ringkas</span>
              <span class="badge rounded-pill text-bg-success">Terstruktur</span>
              <span class="badge rounded-pill text-bg-secondary">Ramah Pengguna</span>
            </div>

            <hr class="my-4">

            <p class="mb-0 text-muted">
              Mulai dari jurnal untuk refleksi, lanjut diskusi untuk dukungan,
              dan gunakan self-help saat butuh penguatan emosi harian.
            </p>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="steps">
            <div class="step">
              <p class="k">1. Daftar / Login</p>
              <p class="d">Buat akun untuk mengakses jurnal dan diskusi.</p>
            </div>
            <div class="step">
              <p class="k">2. Tulis Jurnal</p>
              <p class="d">Catat perasaan, pemicu, dan refleksi singkat.</p>
            </div>
            <div class="step">
              <p class="k">3. Diskusi Q&A</p>
              <p class="d">Ajukan pertanyaan, baca jawaban komunitas.</p>
            </div>
            <div class="step">
              <p class="k">4. Self-Help</p>
              <p class="d">Lakukan latihan singkat untuk menenangkan dan mengelola emosi.</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
  <!-- ================= END CARA KERJA ================= -->


  <!-- ================= ABOUT ================= -->
  <section id="about" class="section pt-0">
    <div class="container">
      <div class="soft-card p-4 p-lg-5">
        <div class="row g-4 align-items-center">
          <div class="col-lg-8">
            <h2 class="section-title font-head mb-2">About</h2>
            <p class="section-sub mb-3">
              MentalCare adalah ruang pendamping untuk refleksi dan dukungan. Platform ini
              tidak melakukan diagnosis dan tidak menggantikan layanan tenaga profesional.
            </p>

            <div class="row g-2">
              <div class="col-md-6">
                <div class="p-3 rounded-4 border bg-white">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-lock" style="color:var(--dark)"></i>
                    <div>
                      <div class="fw-bold">Privasi</div>
                      <div class="text-muted small">Jurnal bersifat pribadi, diskusi bisa anonim.</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-4 border bg-white">
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check" style="color:var(--dark)"></i>
                    <div>
                      <div class="fw-bold">Bertanggung Jawab</div>
                      <div class="text-muted small">Panduan aman, bukan pengganti profesional.</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <div class="col-lg-4">
            <div class="p-4 rounded-4 cta-box">
              <div class="font-head fw-bold mb-2">Mulai Gunakan</div>
              <p class="text-muted mb-3">Masuk untuk mengakses fitur sesuai akun Anda.</p>
              <div class="d-grid gap-2">
                <a href="<?= $BASE_URL ?>login.php" class="btn btn-brand">
                  <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                </a>
                <a href="<?= $BASE_URL ?>register.php" class="btn btn-outline-brand">
                  <i class="bi bi-person-plus me-1"></i>Daftar
                </a>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>
  <!-- ================= END ABOUT ================= -->


  <!-- ================= FAQ ================= -->
  <section id="faq" class="section pt-0">
    <div class="container">
      <div class="row align-items-end mb-3">
        <div class="col-lg-8">
          <h2 class="section-title font-head mb-2">FAQ</h2>
          <p class="section-sub">Pertanyaan umum seputar MentalCare.</p>
        </div>
      </div>

      <div class="accordion" id="faqAcc">
        <div class="accordion-item mb-2">
          <h2 class="accordion-header" id="q1">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#a1" aria-expanded="true">
              Apakah MentalCare menggantikan psikolog/psikiater?
            </button>
          </h2>
          <div id="a1" class="accordion-collapse collapse show" data-bs-parent="#faqAcc">
            <div class="accordion-body text-muted">
              Tidak. MentalCare adalah platform pendukung. Untuk diagnosis dan penanganan klinis, tetap memerlukan tenaga profesional.
            </div>
          </div>
        </div>

        <div class="accordion-item mb-2">
          <h2 class="accordion-header" id="q2">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a2">
              Apakah saya bisa bertanya secara anonim?
            </button>
          </h2>
          <div id="a2" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
            <div class="accordion-body text-muted">
              Bisa. Saat membuat pertanyaan di menu Diskusi, aktifkan opsi “Posting sebagai anonim”.
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header" id="q3">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a3">
              Apakah jurnal saya bisa dilihat orang lain?
            </button>
          </h2>
          <div id="a3" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
            <div class="accordion-body text-muted">
              Jurnal bersifat pribadi dan dikelola dari akun Anda.
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ================= END FAQ ================= -->


  <!-- ================= KONTAK ================= -->
  <section id="kontak" class="section pt-0">
    <div class="container">
      <div class="soft-card p-4 p-lg-5">
        <div class="row g-4 align-items-start">

          <!-- LEFT -->
          <div class="col-lg-7">
            <h2 class="section-title font-head mb-2">Kontak</h2>
            <p class="section-sub mb-3">
              Hubungi tim MentalCare untuk pertanyaan teknis, masukan fitur, atau pelaporan konten.
              Untuk kebutuhan klinis/krisis, gunakan layanan profesional setempat.
            </p>

            <div class="row g-3">
              <div class="col-md-6">
                <div class="feature-card">
                  <div class="icon-pill"><i class="bi bi-envelope"></i></div>
                  <h6>Email Admin</h6>
                  <p class="mb-2">Respon dalam 1–2 hari kerja.</p>
                  <a class="fw-semibold text-decoration-none" href="mailto:admin@mentalcare.local">admin@mentalcare.local</a>
                </div>
              </div>

              <div class="col-md-6">
                <div class="feature-card">
                  <div class="icon-pill"><i class="bi bi-whatsapp"></i></div>
                  <h6>WhatsApp</h6>
                  <p class="mb-2">Untuk bantuan penggunaan aplikasi.</p>
                  <a class="fw-semibold text-decoration-none" href="https://wa.me/6281234567890" target="_blank" rel="noopener">
                    +62 812-3456-7890
                  </a>
                </div>
              </div>

              <div class="col-md-6">
                <div class="feature-card">
                  <div class="icon-pill"><i class="bi bi-instagram"></i></div>
                  <h6>Media Sosial</h6>
                  <p class="mb-2">Update fitur & edukasi singkat.</p>
                  <a class="fw-semibold text-decoration-none" href="#" target="_blank" rel="noopener">@mentalcare_id</a>
                </div>
              </div>

              <div class="col-md-6">
                <div class="feature-card">
                  <div class="icon-pill"><i class="bi bi-clock"></i></div>
                  <h6>Jam Layanan</h6>
                  <p class="mb-0">
                    Senin–Jumat: 09.00–17.00<br>
                    Sabtu: 09.00–12.00<br>
                    Minggu & libur: Tutup
                  </p>
                </div>
              </div>
            </div>

            <div class="notice mt-3">
              <div class="d-flex gap-2 align-items-start">
                <i class="bi bi-info-circle" style="color:var(--dark)"></i>
                <div>
                  <strong>Catatan:</strong> MentalCare adalah platform pendukung (bukan layanan diagnosis/terapi).
                  Jika Anda merasa tidak aman atau dalam kondisi darurat, segera hubungi layanan darurat setempat
                  atau tenaga profesional terdekat.
                </div>
              </div>
            </div>

            <div class="mt-3">
              <div class="d-flex gap-2 align-items-center">
                <i class="bi bi-flag" style="color:var(--dark)"></i>
                <div class="fw-semibold">Pelaporan konten</div>
              </div>
              <div class="text-muted mt-1">
                Laporkan konten yang bersifat merugikan/pelecehan/penipuan melalui form laporan agar cepat ditinjau.
              </div>

              <div class="mt-2">
                <a href="#kontakFormBox" id="openReportLink" class="btn btn-outline-danger btn-sm fw-semibold">
                  <i class="bi bi-flag me-1"></i>Laporkan Konten
                </a>
              </div>
            </div>

          </div>

          <!-- RIGHT -->
          <div class="col-lg-5" id="kontakFormBox">
            <div class="p-4 rounded-4 help-box">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-chat-left-text" style="color:var(--dark)"></i>
                <div class="font-head fw-bold">Pusat Bantuan</div>
              </div>

              <p class="text-muted small mb-3">
                Pilih form sesuai kebutuhan: pesan teknis/masukan, atau pelaporan konten.
              </p>

              <?php if (!empty($contact_success)): ?>
                <div class="alert alert-success py-2" role="alert" aria-live="polite">
                  <?= htmlspecialchars($contact_success); ?>
                </div>
              <?php endif; ?>
              <?php if (!empty($contact_error)): ?>
                <div class="alert alert-danger py-2" role="alert" aria-live="assertive">
                  <?= htmlspecialchars($contact_error); ?>
                </div>
              <?php endif; ?>

              <!-- Tab buttons (HCI: standard + aksesibel) -->
              <div class="d-flex gap-2 mb-3" role="tablist" aria-label="Pilihan form bantuan">
                <button type="button" class="btn btn-brand w-50" id="tabPesanBtn"
                        role="tab" aria-selected="true" aria-controls="tabPesan">
                  <i class="bi bi-send me-1"></i>Kirim Pesan
                </button>
                <button type="button" class="btn btn-outline-danger w-50" id="tabReportBtn"
                        role="tab" aria-selected="false" aria-controls="tabReport">
                  <i class="bi bi-flag me-1"></i>Laporkan
                </button>
              </div>

              <!-- ================= FORM: PESAN ================= -->
              <div id="tabPesan" role="tabpanel" aria-labelledby="tabPesanBtn">
                <div class="p-3 rounded-4 mb-2 bg-white border-soft">
                  <div class="fw-semibold mb-1">Kirim Pesan</div>
                  <div class="text-muted small">Untuk pertanyaan teknis & masukan fitur.</div>
                </div>

                <form action="<?= $BASE_URL ?>process/contact_process.php" method="POST" novalidate>
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                  <input type="hidden" name="type" value="pesan">

                  <div class="hp-field">
                    <label>Website</label>
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Nama</label>
                    <input type="text" name="name" class="form-control form-control-lg" placeholder="Nama Anda" required maxlength="80">
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control form-control-lg" placeholder="nama@email.com" required maxlength="120">
                  </div>

                  <div class="mb-2">
                    <label class="form-label fw-semibold">Pesan</label>
                    <textarea name="message" id="msgText" rows="4" class="form-control form-control-lg"
                              placeholder="Tulis pesan Anda..." required maxlength="2000"></textarea>
                    <div class="char-counter" data-for="msgText"></div>
                  </div>

                  <button type="submit" class="btn btn-brand w-100 btn-lg">
                    <i class="bi bi-send me-1"></i>Kirim
                  </button>
                </form>
              </div>

              <!-- ================= FORM: REPORT ================= -->
              <div id="tabReport" role="tabpanel" aria-labelledby="tabReportBtn" hidden>
                <div class="p-3 rounded-4 mb-2 bg-white border-danger-soft">
                  <div class="fw-semibold mb-1">Pelaporan Konten</div>
                  <div class="text-muted small">
                    Laporkan spam, penipuan, pelecehan, atau konten berbahaya. Isi minimal ID atau URL konten.
                  </div>
                </div>

                <form id="reportForm" action="<?= $BASE_URL ?>process/report_process.php" method="POST" novalidate>
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                  <input type="hidden" name="type" value="laporan_konten">

                  <div class="hp-field">
                    <label>Website</label>
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                  </div>

                  <div class="alert alert-danger py-2 d-none" id="reportValidation" role="alert"></div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Nama</label>
                    <input type="text" name="reporter_name" class="form-control form-control-lg" placeholder="Nama Anda" required maxlength="80">
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="reporter_email" class="form-control form-control-lg" placeholder="nama@email.com" required maxlength="120">
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Jenis Konten</label>
                    <select name="target_type" class="form-select form-select-lg" required>
                      <option value="question">Posting / Pertanyaan</option>
                      <option value="answer">Jawaban</option>
                      <option value="journal">Jurnal</option>
                      <option value="other">Lainnya</option>
                    </select>
                  </div>

                  <div class="row g-2">
                    <div class="col-6">
                      <label class="form-label fw-semibold">ID Konten (opsional)</label>
                      <input type="number" id="target_id" name="target_id" class="form-control form-control-lg" min="0" placeholder="Mis: 12">
                    </div>
                    <div class="col-6">
                      <label class="form-label fw-semibold">URL (opsional)</label>
                      <input type="text" id="target_url" name="target_url" class="form-control form-control-lg" maxlength="255" placeholder="Tempel link">
                    </div>
                  </div>

                  <div class="mb-3 mt-2">
                    <label class="form-label fw-semibold">Alasan</label>
                    <input type="text" name="reason" class="form-control form-control-lg"
                           placeholder="Mis: spam / penipuan / pelecehan" required maxlength="150">
                  </div>

                  <div class="mb-2">
                    <label class="form-label fw-semibold">Detail Laporan</label>
                    <textarea name="details" id="reportDetails" rows="4" class="form-control form-control-lg"
                              placeholder="Jelaskan konteks & bukti singkat..." required maxlength="2000"></textarea>
                    <div class="char-counter" data-for="reportDetails"></div>
                  </div>

                  <button type="submit" class="btn btn-danger w-100 btn-lg">
                    <i class="bi bi-flag me-1"></i>Kirim Laporan
                  </button>

                  <div class="text-muted small mt-2">
                    Jika kondisi darurat/krisis, segera hubungi layanan darurat setempat atau tenaga profesional.
                  </div>
                </form>
              </div>

              <hr class="my-3">

              <div class="d-grid gap-2">
                <?php if($isLoggedIn): ?>
                  <a class="btn btn-outline-brand fw-semibold" href="<?= $BASE_URL ?>dashboard/index.php">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                  </a>
                <?php else: ?>
                  <a class="btn btn-outline-brand fw-semibold" href="<?= $BASE_URL ?>login.php">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                  </a>
                  <a class="btn btn-brand fw-semibold" href="<?= $BASE_URL ?>register.php">
                    <i class="bi bi-person-plus me-1"></i>Daftar
                  </a>
                <?php endif; ?>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </section>
  <!-- ================= END KONTAK ================= -->

</main>

<button type="button" class="back-to-top" id="backToTop" aria-label="Kembali ke atas">
  <i class="bi bi-arrow-up"></i>
</button>

<?php include __DIR__ . '/includes/footer_landing.php'; ?>
