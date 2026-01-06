<?php
require_once __DIR__ . '/../config/session.php';
if (!isset($conn)) {
  require_once __DIR__ . '/../config/database.php';
}

$user_id  = (int)($_SESSION['user_id'] ?? 0);
$username = $_SESSION['username'] ?? 'Pengguna';
$initial  = strtoupper(substr($username, 0, 1));

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function is_active($needle, $path) {
  return str_contains($path, $needle) ? 'active' : '';
}

$APP_BASE = '/IMK/'; // sesuaikan jika folder project berbeda

// Ambil foto dari session dulu. Jika belum ada, ambil dari DB lalu cache ke session.
$photoRel = $_SESSION['photo'] ?? '';

if ($user_id && $photoRel === '') {
  $stmt = mysqli_prepare($conn, "SELECT photo FROM users WHERE user_id=? LIMIT 1");
  mysqli_stmt_bind_param($stmt, 'i', $user_id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($res);
  $photoRel = $row['photo'] ?? '';
  $_SESSION['photo'] = $photoRel; // cache
}

$photoUrl = '';
if (!empty($photoRel)) {
  $rel = ltrim(trim($photoRel), '/');

  // jika ada yang tersimpan "IMK/..." di DB, rapikan
  if (str_starts_with($rel, 'IMK/')) {
    $rel = substr($rel, 4);
  }

  // validasi file fisik untuk menghindari broken image
  $fsPath = __DIR__ . '/../' . $rel;
  if (is_file($fsPath)) {
    $v = @filemtime($fsPath) ?: time(); // cache busting
    $photoUrl = $APP_BASE . $rel . '?v=' . $v;
  }
}
?>

<aside class="col-md-3 col-lg-2 min-vh-100 d-flex flex-column p-3 sidebar-wrapper">

  <div class="sidebar-brand mb-3">
    <a class="navbar-brand d-flex align-items-center gap-2" href="/IMK/index.php">
      <div class="brand-badge"><i class="bi bi-heart-pulse-fill"></i></div>
      <div>
        <div class="fw-bold font-head" style="line-height:1.1">MentalCare</div>
        <small class="text-muted" style="font-size:.85rem">Journal & Support</small>
      </div>
    </a>
  </div>

  <!-- USER PANEL -->
  <div class="mc-user-panel">
    <div class="mc-sidebar-avatar" aria-label="Avatar pengguna">
      <?php if (!empty($photoUrl)): ?>
        <img
          src="<?= htmlspecialchars($photoUrl); ?>"
          alt="Foto profil <?= htmlspecialchars($username); ?>"
          class="mc-sidebar-avatar-img"
          loading="lazy"
        >
      <?php else: ?>
        <span class="mc-sidebar-initial"><?= htmlspecialchars($initial); ?></span>
      <?php endif; ?>
    </div>

    <div class="mc-user-meta">
      <div class="mc-user-name"><?= htmlspecialchars($username); ?></div>
      <div class="mc-user-role">Pengguna</div>
    </div>
  </div>

  <nav class="sidebar-nav mb-auto">
    <ul class="nav nav-pills flex-column gap-1 sidebar-menu">

      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 <?= is_active('/dashboard/', $path); ?>"
           href="/IMK/dashboard/index.php">
          <i class="bi bi-speedometer2 fs-5"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 <?= is_active('/journal/', $path); ?>"
           href="/IMK/journal/index.php">
          <i class="bi bi-journal-text fs-5"></i>
          <span>Jurnal Pribadi</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 <?= is_active('/discussion/', $path); ?>"
           href="/IMK/discussion/index.php">
          <i class="bi bi-chat-dots fs-5"></i>
          <span>Diskusi</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 <?= is_active('/self_help/', $path); ?>"
           href="/IMK/self_help/index.php">
          <i class="bi bi-lightbulb fs-5"></i>
          <span>Self Help</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-2 <?= is_active('/profile/', $path); ?>"
           href="/IMK/profile/index.php">
          <i class="bi bi-person-circle fs-5"></i>
          <span>Profil</span>
        </a>
      </li>

    </ul>
  </nav>

  <div class="sidebar-footer pt-3 mt-3 border-top">
    <a href="/IMK/logout.php"
       class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
      <i class="bi bi-box-arrow-right fs-5"></i>
      <span>Logout</span>
    </a>
  </div>

</aside>
