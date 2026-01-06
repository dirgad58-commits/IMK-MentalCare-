
<?php
$username = $_SESSION['username'] ?? 'Pengguna';
$initial  = strtoupper(substr($username, 0, 1));

$currentPage = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>MentalCare</title>


  <!-- Bootstrap 5 + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">


  <!-- App CSS -->
   <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
  <link href="/IMK-MentalCare-/assets/css/app.css" rel="stylesheet">
  <link rel="stylesheet" href="/IMK-MentalCare-/assets/css/landing-teal-solid.css">
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/mentalcare.css" rel="stylesheet">







</head>



<body>
