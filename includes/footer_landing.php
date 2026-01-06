<?php
$BASE_URL = $BASE_URL ?? '/IMK/';
?>
<footer class="footer">
  <div class="container">
    <div class="row g-3 align-items-center">
      <div class="col-md-6">
        <div class="fw-bold font-head">MentalCare — Journal & Support</div>
        <div class="small text-white-50">© <?= date('Y'); ?> — MentalCare</div>
      </div>
      <div class="col-md-6 text-md-end">
        <a class="me-3" href="<?= $BASE_URL ?>login.php">Masuk</a>
        <a class="me-3" href="<?= $BASE_URL ?>register.php">Daftar</a>
        <a href="#alur">Cara Kerja</a>
      </div>
    </div>
  </div>
</footer>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/landing.js"></script>
</body>
</html>
