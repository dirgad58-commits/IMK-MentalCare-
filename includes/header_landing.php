<?php
// includes/header_landing.php
$BASE_URL = $BASE_URL ?? '/IMK/';
$page_title = $page_title ?? 'MentalCare — Journal & Support';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page_title) ?></title>

  <base href="<?= htmlspecialchars($BASE_URL) ?>">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap -->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/bootstrap-icons.css" rel="stylesheet">

  <!-- Global (jika Anda pakai) -->
  <link href="assets/css/app.css" rel="stylesheet">

  <!-- MentalCare theme -->
  <link href="assets/css/mentalcare.css" rel="stylesheet">
</head>
<body>
