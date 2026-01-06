<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 p-4">

            <!-- HERO / HEADER -->
            <div class="selfhelp-hero card shadow-sm mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
                        <div>
                            <div class="d-inline-flex align-items-center gap-2 mb-2">
                                <span class="badge badge-soft">Self-Help Toolkit</span>
                                <span class="badge text-bg-light border">3–10 menit</span>
                                <span class="badge text-bg-light border">Tanpa alat</span>
                            </div>

                            <h2 class="fw-bold mb-2">Self-Help</h2>
                            <p class="text-muted mb-0" style="max-width: 720px;">
                                Kumpulan latihan singkat yang disusun seperti “toolkit” untuk membantu mengelola cemas,
                                overthinking, stres, dan meningkatkan ketenangan harian. Bukan pengganti tenaga profesional.
                            </p>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="#toolkit" class="btn btn-primary">
                                <i class="bi bi-grid-3x3-gap me-1"></i> Mulai Toolkit
                            </a>
                            <a href="#safety" class="btn btn-outline-secondary">
                                <i class="bi bi-shield-check me-1"></i> Catatan Keselamatan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QUICK CHECK-IN -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-lg-7">
                    <div class="card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-activity text-primary fs-4"></i>
                                <h5 class="fw-semibold mb-0">Check-in singkat</h5>
                            </div>
                            <p class="text-muted mb-3">
                                Pilih kondisi Anda saat ini, lalu gunakan latihan yang direkomendasikan.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-primary" href="#breathing">
                                    <i class="bi bi-wind me-1"></i> Cemas / gelisah
                                </a>
                                <a class="btn btn-outline-primary" href="#grounding">
                                    <i class="bi bi-compass me-1"></i> Panik ringan
                                </a>
                                <a class="btn btn-outline-primary" href="#overthinking">
                                    <i class="bi bi-brain me-1"></i> Overthinking
                                </a>
                                <a class="btn btn-outline-primary" href="#journaling">
                                    <i class="bi bi-journal-text me-1"></i> Pikiran penuh
                                </a>
                                <a class="btn btn-outline-primary" href="#sleep">
                                    <i class="bi bi-moon-stars me-1"></i> Sulit tidur
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="card shadow-sm h-100 border-warning">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-info-circle text-warning fs-4"></i>
                                <h5 class="fw-semibold mb-0">Panduan penggunaan</h5>
                            </div>
                            <ul class="mb-0 text-muted">
                                <li>Pilih 1 latihan, lakukan sampai selesai.</li>
                                <li>Ulangi 1–2 kali bila diperlukan.</li>
                                <li>Jika gejala memburuk, prioritaskan bantuan profesional.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOOLKIT CARDS -->
            <div id="toolkit" class="mb-3">
                <h4 class="fw-bold mb-1">Toolkit</h4>
                <p class="text-muted mb-3">Modul latihan yang ringkas, terstruktur, dan mudah diulang.</p>

                <div class="row g-3">
                    <div class="col-12 col-md-6 col-xl-4">
                        <a class="card shadow-sm h-100 text-decoration-none selfhelp-card" href="#breathing">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-wind text-primary fs-4"></i>
                                    <div class="fw-semibold">Pernapasan 4–7–8</div>
                                </div>
                                <div class="text-muted small">
                                    Menurunkan ketegangan fisiologis, cocok saat cemas mendadak.
                                </div>
                                <div class="mt-3 badge text-bg-light border">± 3 menit</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-xl-4">
                        <a class="card shadow-sm h-100 text-decoration-none selfhelp-card" href="#grounding">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-compass text-primary fs-4"></i>
                                    <div class="fw-semibold">Grounding 5–4–3–2–1</div>
                                </div>
                                <div class="text-muted small">
                                    Mengembalikan fokus ke “saat ini” lewat panca indera.
                                </div>
                                <div class="mt-3 badge text-bg-light border">± 2–5 menit</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-xl-4">
                        <a class="card shadow-sm h-100 text-decoration-none selfhelp-card" href="#overthinking">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-brain text-primary fs-4"></i>
                                    <div class="fw-semibold">Overthinking: 2 pertanyaan</div>
                                </div>
                                <div class="text-muted small">
                                    Memisahkan fakta vs asumsi, lalu ambil langkah kecil.
                                </div>
                                <div class="mt-3 badge text-bg-light border">± 3 menit</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-xl-4">
                        <a class="card shadow-sm h-100 text-decoration-none selfhelp-card" href="#journaling">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-journal-text text-primary fs-4"></i>
                                    <div class="fw-semibold">Journaling 5 menit</div>
                                </div>
                                <div class="text-muted small">
                                    Merapikan isi pikiran, mengurangi beban mental.
                                </div>
                                <div class="mt-3 badge text-bg-light border">± 5 menit</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-xl-4">
                        <a class="card shadow-sm h-100 text-decoration-none selfhelp-card" href="#sleep">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-moon-stars text-primary fs-4"></i>
                                    <div class="fw-semibold">Ritual tidur singkat</div>
                                </div>
                                <div class="text-muted small">
                                    Menenangkan tubuh sebelum tidur (tanpa teori panjang).
                                </div>
                                <div class="mt-3 badge text-bg-light border">± 5–10 menit</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-xl-4">
                        <a class="card shadow-sm h-100 text-decoration-none selfhelp-card" href="#safety">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-shield-check text-primary fs-4"></i>
                                    <div class="fw-semibold">Catatan keselamatan</div>
                                </div>
                                <div class="text-muted small">
                                    Kapan perlu meminta bantuan profesional dan langkah aman.
                                </div>
                                <div class="mt-3 badge text-bg-light border">Wajib dibaca</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- DETAIL MODULES (ACCORDION) -->
            <div class="card shadow-sm mt-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-1">Latihan Terstruktur</h4>
                    <p class="text-muted mb-3">Ikuti langkahnya, jangan lompat. Satu latihan selesai dulu.</p>

                    <div class="accordion" id="selfHelpAccordion">

                        <!-- BREATHING -->
                        <div class="accordion-item" id="breathing">
                            <h2 class="accordion-header" id="h1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#c1" aria-expanded="true" aria-controls="c1">
                                    <i class="bi bi-wind me-2"></i> Pernapasan 4–7–8
                                </button>
                            </h2>
                            <div id="c1" class="accordion-collapse collapse show" aria-labelledby="h1" data-bs-parent="#selfHelpAccordion">
                                <div class="accordion-body">
                                    <p class="text-muted mb-2">
                                        Fokus pada ritme napas untuk menenangkan respons stres tubuh.
                                    </p>
                                    <ol class="mb-3">
                                        <li>Tarik napas 4 detik.</li>
                                        <li>Tahan 7 detik.</li>
                                        <li>Hembuskan perlahan 8 detik.</li>
                                        <li>Ulangi 4 kali.</li>
                                    </ol>
                                    <div class="alert alert-secondary mb-0">
                                        Tip: bila sulit menahan 7 detik, turunkan menjadi 4–4–6 terlebih dulu.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- GROUNDING -->
                        <div class="accordion-item" id="grounding">
                            <h2 class="accordion-header" id="h2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c2" aria-expanded="false" aria-controls="c2">
                                    <i class="bi bi-compass me-2"></i> Grounding 5–4–3–2–1
                                </button>
                            </h2>
                            <div id="c2" class="accordion-collapse collapse" aria-labelledby="h2" data-bs-parent="#selfHelpAccordion">
                                <div class="accordion-body">
                                    <p class="text-muted mb-2">
                                        Teknik untuk “menarik balik” pikiran ke keadaan sekarang melalui panca indera.
                                    </p>
                                    <ul class="mb-0">
                                        <li><strong>5</strong> hal yang Anda lihat</li>
                                        <li><strong>4</strong> hal yang Anda rasakan (sentuhan, kursi, lantai)</li>
                                        <li><strong>3</strong> hal yang Anda dengar</li>
                                        <li><strong>2</strong> hal yang Anda cium</li>
                                        <li><strong>1</strong> hal yang Anda rasakan di mulut (air/minum)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- OVERTHINKING -->
                        <div class="accordion-item" id="overthinking">
                            <h2 class="accordion-header" id="h3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c3" aria-expanded="false" aria-controls="c3">
                                    <i class="bi bi-brain me-2"></i> Overthinking: 2 pertanyaan inti
                                </button>
                            </h2>
                            <div id="c3" class="accordion-collapse collapse" aria-labelledby="h3" data-bs-parent="#selfHelpAccordion">
                                <div class="accordion-body">
                                    <p class="text-muted mb-2">
                                        Gunakan dua pertanyaan ini untuk memisahkan fakta dan asumsi:
                                    </p>
                                    <ul class="mb-3">
                                        <li><em>Apa fakta yang benar-benar saya ketahui?</em></li>
                                        <li><em>Langkah kecil apa yang bisa saya lakukan sekarang?</em></li>
                                    </ul>
                                    <div class="alert alert-secondary mb-0">
                                        Tip: tulis jawaban 1–2 kalimat saja, jangan panjang.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- JOURNALING -->
                        <div class="accordion-item" id="journaling">
                            <h2 class="accordion-header" id="h4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c4" aria-expanded="false" aria-controls="c4">
                                    <i class="bi bi-journal-text me-2"></i> Journaling 5 menit
                                </button>
                            </h2>
                            <div id="c4" class="accordion-collapse collapse" aria-labelledby="h4" data-bs-parent="#selfHelpAccordion">
                                <div class="accordion-body">
                                    <p class="text-muted mb-2">
                                        Tujuannya bukan menulis bagus, tetapi mengurangi beban mental.
                                    </p>
                                    <ul class="mb-0">
                                        <li>Perasaan utama hari ini (1–2 kata).</li>
                                        <li>Satu hal kecil yang Anda syukuri.</li>
                                        <li>Satu hal yang ingin Anda perbaiki besok (langkah kecil).</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- SLEEP -->
                        <div class="accordion-item" id="sleep">
                            <h2 class="accordion-header" id="h5">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c5" aria-expanded="false" aria-controls="c5">
                                    <i class="bi bi-moon-stars me-2"></i> Ritual tidur singkat
                                </button>
                            </h2>
                            <div id="c5" class="accordion-collapse collapse" aria-labelledby="h5" data-bs-parent="#selfHelpAccordion">
                                <div class="accordion-body">
                                    <ol class="mb-0">
                                        <li>Jauhkan layar 15 menit sebelum tidur (bila memungkinkan).</li>
                                        <li>Atur napas 6 kali perlahan (tarik 4, hembus 6).</li>
                                        <li>Relaksasi otot: tegangkan bahu 3 detik, lepas perlahan.</li>
                                        <li>Tulis 1 kalimat “besok saya urus” untuk hal yang mengganggu pikiran.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- SAFETY NOTE -->
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

        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
