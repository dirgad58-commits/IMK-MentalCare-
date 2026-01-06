<?php
// includes/navbar_landing.php
$BASE_URL = $BASE_URL ?? '/IMK/';
$isLoggedIn = $isLoggedIn ?? isset($_SESSION['user_id']);
?>
<nav class="navbar navbar-expand-lg navbar-blur navbar-light fixed-top" aria-label="Navigasi utama">
  <div class="container">

    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $BASE_URL ?>index.php">
      <div class="brand-badge"><i class="bi bi-heart-pulse-fill"></i></div>
      <div class="brand-text">
        <div class="brand-title font-head">MentalCare</div>
        <div class="brand-subtitle">Journal & Support</div>
      </div>
    </a>

    <!-- Action buttons (selalu terlihat) -->
    <div class="d-flex align-items-center gap-2 ms-auto order-lg-3">
      <?php if($isLoggedIn): ?>
        <a class="btn btn-outline-brand fw-semibold" href="<?= $BASE_URL ?>dashboard/index.php">
          <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
        <a class="btn btn-brand" href="<?= $BASE_URL ?>logout.php">
          <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
      <?php else: ?>
        <a class="btn btn-outline-brand fw-semibold" href="<?= $BASE_URL ?>login.php">
          <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
        </a>
        <a class="btn btn-brand" href="<?= $BASE_URL ?>register.php">
          <i class="bi bi-person-plus me-1"></i>Daftar
        </a>
      <?php endif; ?>
    </div>

    <button class="navbar-toggler ms-2 order-lg-2" type="button"
            data-bs-toggle="collapse" data-bs-target="#navMain"
            aria-controls="navMain" aria-expanded="false"
            aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse order-lg-1" id="navMain">
      <ul class="navbar-nav ms-lg-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
        <li class="nav-item"><a class="nav-link active" href="#beranda">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#fitur">Fitur</a></li>
        <li class="nav-item"><a class="nav-link" href="#alur">Cara Kerja</a></li>
        <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
        <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
      </ul>
    </div>

  </div>
</nav>
