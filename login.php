<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/index.php");
    exit;
}

$error = $_GET['error'] ?? '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<style>
:root{
  --mc-dark:#005461;
  --mc-teal:#018790;
  --mc-aqua:#00B7B5;
  --mc-bg:#f4f4f4;
  --mc-white:#ffffff;

  --mc-text:#0f172a;
  --mc-muted:#475569;
  --mc-border:rgba(15,23,42,.12);

  --mc-shadow: 0 18px 45px rgba(0,84,97,.10);
  --mc-shadow2: 0 24px 60px rgba(0,84,97,.16);

  --mc-r1:22px;
  --mc-r2:16px;
}

/* ====== PAGE BACKGROUND (SOLID, NO GRADIENT) ====== */
.auth-wrap{
  min-height: 100vh;
  background: var(--mc-bg);
  position: relative;
  overflow: hidden;
  padding: 84px 14px 44px;
}

/* dekor solid (seragam dengan register) */
.auth-wrap::before{
  content:"";
  position:absolute;
  inset:auto -140px -140px auto;
  width: 380px;
  height: 380px;
  background: rgba(0,183,181,.18);
  border-radius: 64px;
  transform: rotate(10deg);
  pointer-events:none;
}
.auth-wrap::after{
  content:"";
  position:absolute;
  inset:-140px auto auto -140px;
  width: 460px;
  height: 460px;
  background: rgba(1,135,144,.15);
  border-radius: 72px;
  transform: rotate(-12deg);
  pointer-events:none;
}
.btn-mc{
  background: #018790;
  border-color: #018790;
  color: #fff;
}
.btn-mc:hover,
.btn-mc:focus{
  color: #fff;
}
.btn-mc i{
  color: inherit; /* ikon ikut putih */
}


/* tombol kembali floating (di luar kotak) */
.auth-back{
  position: fixed;
  top: 18px;
  left: 18px;
  z-index: 1050;
  border-radius: 999px;
  padding: 10px 14px;
  border: 1px solid var(--mc-border);
  background: rgba(255,255,255,.92);
  backdrop-filter: blur(10px);
  box-shadow: 0 10px 25px rgba(0,0,0,.06);
  font-weight: 800;
}
.auth-back:hover{
  border-color: rgba(1,135,144,.35);
}

/* card utama */
.auth-shell{
  position: relative;
  max-width: 980px;
  margin: 0 auto;
}
.auth-card{
  border: 1px solid var(--mc-border);
  border-radius: var(--mc-r1);
  overflow: hidden;
  background: var(--mc-white);
  box-shadow: var(--mc-shadow2);
}

/* panel kiri (branding) */
.auth-side{
  background: var(--mc-teal);
  color: #fff;
  padding: 34px;
  height: 100%;
}
.auth-brand{
  display:flex;
  align-items:center;
  gap:12px;
  margin-bottom: 18px;
}
.auth-logo{
  width: 44px; height: 44px;
  border-radius: 14px;
  background: rgba(255,255,255,.18);
  border: 1px solid rgba(255,255,255,.28);
  display:flex; align-items:center; justify-content:center;
}
.auth-logo i{ font-size: 22px; color: #fff; }
.auth-brand .t1{ font-weight: 900; font-size: 18px; line-height:1.1; }
.auth-brand .t2{ opacity:.9; font-size: 13px; }

.auth-side h2{
  font-weight: 900;
  letter-spacing: -.3px;
  margin: 10px 0 10px;
}
.auth-side p{
  opacity: .92;
  margin-bottom: 16px;
}

/* list benefit */
.side-list{ display:grid; gap:10px; margin-top: 18px; }
.side-item{
  display:flex;
  gap:10px;
  align-items:flex-start;
  padding: 12px 12px;
  border-radius: 16px;
  background: rgba(255,255,255,.14);
  border: 1px solid rgba(255,255,255,.24);
}
.side-item i{ font-size: 18px; margin-top: 2px; opacity: .95; }
.side-item .k{ font-weight: 900; margin: 0 0 2px; }
.side-item .d{ margin: 0; opacity: .92; font-size: 13px; }

/* panel kanan (form) */
.auth-form{
  padding: 34px;
  background: #fff;
}
.form-title{
  text-align:center;
  margin-bottom: 18px;
}
.form-title h3{
  font-weight: 900;
  letter-spacing: .4px;
  margin: 0 0 6px;
}
.form-title p{
  margin:0;
  color: var(--mc-muted);
}

/* input style */
.mc-label{
  font-weight: 900;
  color: var(--mc-text);
  margin-bottom: 6px;
}
.mc-input{
  border-radius: 16px;
  border: 1px solid var(--mc-border);
  padding: 14px 14px;
}
.mc-input:focus{
  border-color: rgba(1,135,144,.45);
  box-shadow: 0 0 0 .2rem rgba(0,183,181,.18);
}

/* input group icon */
.input-icon{
  border-radius: 16px;
  border: 1px solid var(--mc-border);
  border-right: 0;
  background: rgba(0,183,181,.10);
  color: var(--mc-dark);
}
.input-group .mc-input{ border-left: 0; }

.btn-eye{
  border-radius: 16px;
  border: 1px solid var(--mc-border);
  border-left: 0;
  background: rgba(0,183,181,.10);
  color: var(--mc-dark);
  font-weight: 900;
}
.btn-eye:hover{
  background: rgba(0,183,181,.16);
}

/* tombol utama */
.btn-mc{
  background: var(--mc-teal);
  border: 0;
  border-radius: 16px;
  padding: 12px 14px;
  font-weight: 900;
  box-shadow: 0 16px 38px rgba(1,135,144,.22);
}
.btn-mc:hover{ background: var(--mc-dark); }

/* info box */
.helper-note{
  background: rgba(0,183,181,.10);
  border: 1px solid rgba(0,183,181,.18);
  border-radius: 16px;
  padding: 12px 12px;
  color: var(--mc-muted);
  font-size: 13px;
}

/* footer link */
.auth-foot{
  text-align:center;
  margin-top: 14px;
  color: var(--mc-muted);
}
.auth-foot a{
  font-weight: 900;
  color: var(--mc-teal);
  text-decoration: none;
}
.auth-foot a:hover{ text-decoration: underline; }

/* responsive */
@media (max-width: 991.98px){
  .auth-side{ display:none; }
  .auth-form{ padding: 28px; }
}
</style>

<!-- Tombol kembali (DI LUAR KOTAK) -->
<a href="/IMK-MentalCare-/index.php" class="btn auth-back">
  <i class="bi bi-arrow-left"></i> Kembali
</a>

<div class="auth-wrap d-flex align-items-center">
  <div class="container">
    <div class="auth-shell">
      <div class="auth-card">
        <div class="row g-0">

          <!-- LEFT PANEL -->
          <div class="col-lg-5">
            <div class="auth-side">
              <div class="auth-brand">
                <div class="auth-logo"><i class="bi bi-heart-pulse-fill"></i></div>
                <div>
                  <div class="t1">MentalCare</div>
                  <div class="t2">Journal & Support</div>
                </div>
              </div>

              <h2>Selamat datang kembali</h2>
              <p>
                Masuk untuk melanjutkan jurnal pribadi, diskusi Q&A, dan self-help toolkit.
                Platform ini bersifat pendukung dan tidak menggantikan layanan profesional.
              </p>

              <div class="side-list">
                <div class="side-item">
                  <i class="bi bi-journal-text"></i>
                  <div>
                    <p class="k">Jurnal</p>
                    <p class="d">Catat mood & refleksi harian dengan rapi.</p>
                  </div>
                </div>

                <div class="side-item">
                  <i class="bi bi-chat-dots"></i>
                  <div>
                    <p class="k">Diskusi</p>
                    <p class="d">Tanya-jawab seperti forum secara suportif.</p>
                  </div>
                </div>

                <div class="side-item">
                  <i class="bi bi-incognito"></i>
                  <div>
                    <p class="k">Anonim</p>
                    <p class="d">Opsi posting anonim untuk kenyamanan.</p>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- RIGHT FORM -->
          <div class="col-lg-7">
            <div class="auth-form">

              <div class="form-title">
                <h3>Login</h3>
                <p>Masuk untuk mengakses jurnal dan diskusi.</p>
              </div>

              <?php if ($error): ?>
                <div class="alert alert-danger text-center mb-3">
                  <?= htmlspecialchars($error); ?>
                </div>
              <?php endif; ?>

              <form action="/IMK-MentalCare-/process/auth_process.php" method="POST" autocomplete="off">

                <!-- Email -->
                <div class="mb-3">
                  <label class="mc-label">Email</label>
                  <div class="input-group">
                    <span class="input-group-text input-icon">
                      <i class="bi bi-envelope"></i>
                    </span>
                    <input
                      type="email"
                      name="email"
                      class="form-control mc-input"
                      placeholder="contoh@email.com"
                      required
                    >
                  </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                  <label class="mc-label">Password</label>
                  <div class="input-group">
                    <span class="input-group-text input-icon">
                      <i class="bi bi-shield-lock"></i>
                    </span>
                    <input
                      id="passwordField"
                      type="password"
                      name="password"
                      class="form-control mc-input"
                      placeholder="Masukkan password"
                      required
                    >
                    <button class="btn btn-eye" type="button" id="togglePass">
                      <i class="bi bi-eye" id="iconPass"></i>
                    </button>
                  </div>
                </div>

                <button type="submit" class="btn btn-mc w-100 btn-lg">
                  <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                  
                </button>

                <div class="helper-note mt-3">
                  <div class="d-flex gap-2 align-items-start">
                    <i class="bi bi-info-circle" style="color:var(--mc-dark)"></i>
                    <div>
                      Jika Anda sedang dalam kondisi darurat atau merasa tidak aman,
                      hubungi layanan darurat setempat atau tenaga profesional terdekat.
                    </div>
                  </div>
                </div>

              </form>

              <div class="auth-foot">
                Belum punya akun?
                <a href="/IMK-MentalCare-/register.php">Register</a>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const pass = document.getElementById('passwordField');
  const btn  = document.getElementById('togglePass');
  const icon = document.getElementById('iconPass');

  if(!pass || !btn || !icon) return;

  btn.addEventListener('click', function(){
    const isPwd = pass.type === 'password';
    pass.type = isPwd ? 'text' : 'password';
    icon.className = isPwd ? 'bi bi-eye-slash' : 'bi bi-eye';
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
=