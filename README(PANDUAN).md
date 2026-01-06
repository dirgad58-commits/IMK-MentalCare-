# MentalCare — Journal & Support (Web IMK)

MentalCare adalah aplikasi web pendamping kesehatan mental berbasis PHP dan MySQL/MariaDB.
Aplikasi ini digunakan untuk menulis jurnal harian, berdiskusi secara suportif melalui sistem
tanya jawab (Q&A), serta menyediakan fitur kontak dan pelaporan konten.

MentalCare bersifat pendukung dan tidak menggantikan layanan profesional
(psikolog atau psikiater).

======================================================================

FITUR UTAMA
- Login dan Register pengguna
- Dashboard ringkasan (jumlah jurnal, pertanyaan, dan jawaban)
- Jurnal harian dengan mood terstruktur (senang, netral, sedih, cemas, marah)
- Diskusi Q&A dengan opsi anonim
- Kontak dan pelaporan konten bermasalah

======================================================================

REQUIREMENT
- Sistem Operasi: Windows 10 / Windows 11
- XAMPP (Apache + MySQL/MariaDB) + phpMyAdmin
- PHP versi 8.x (disarankan)
- Browser (Chrome atau Edge)
- Git dan VS Code (opsional)

======================================================================

CARA MENJALANKAN APLIKASI (PALING CEPAT)

TAHAP 1 – AMBIL PROJECT DARI GITHUB
Anda bisa menggunakan salah satu cara berikut:
1. Clone repository dengan Git
   git clone <URL_REPO_GITHUB>/buka link->Tekan <>Code ->open with GitHub Desktop(instal dulu GitHub Desktop di Laptop)

2. Atau download ZIP dari GitHub lalu extract folder project

======================================================================

TAHAP 2 – NYALAKAN SERVER LOKAL
Buka XAMPP Control Panel kemudian:
- Klik Start pada Apache
- Klik Start pada MySQL

Pastikan kedua service berjalan tanpa error.

======================================================================

TAHAP 3 – AGAR PROJECT BISA DIAKSES DI LOCALHOST

OPS I (PALING MUDAH – DISARANKAN)
Pindahkan folder project ke dalam:
C:\xampp\htdocs\IMK-MentalCare-

Lalu buka di browser:
http://localhost/IMK-MentalCare-/

OPS II (TANPA PINDAH FOLDER – APACHE ALIAS)
Jika folder project berada di luar htdocs, misalnya:
C:\Users\ADVAN\Documents\GitHub\IMK-MentalCare-

Lakukan langkah berikut:
1. Buka file:
   C:\xampp\apache\conf\httpd.conf

2. Tambahkan konfigurasi berikut di bagian bawah file:

   Alias "/IMK-MentalCare-/" "C:/Users/ADVAN/Documents/GitHub/IMK-MentalCare-/"

   <Directory "C:/Users/ADVAN/Documents/GitHub/IMK-MentalCare-/">
     Options Indexes FollowSymLinks
     AllowOverride All
     Require all granted
   </Directory>

3. Simpan file dan restart Apache

4. Buka di browser:
   http://localhost/IMK-MentalCare-/

======================================================================

TAHAP 4 – IMPORT DATABASE
1. Buka phpMyAdmin melalui:
   http://localhost/phpmyadmin

2. Buat database baru dengan nama:
   mentalcare_db

3. Import file SQL database ke database mentalcare_db

======================================================================

TAHAP 5 – KONFIGURASI KONEKSI DATABASE
Edit file:
config/database.php

Sesuaikan dengan konfigurasi XAMPP (default):
host     = localhost
username = root
password = (kosong)
database = mentalcare_db

======================================================================

TAHAP 6 – PASTIKAN BASE URL BENAR
Di file utama (misalnya index.php), pastikan BASE_URL diset sebagai berikut:

BASE_URL = /IMK-MentalCare-/

Ini penting agar link, CSS, dan JavaScript tidak rusak.

======================================================================

AKUN DEMO (UNTUK PRESENTASI / UJI COBA)

Email    : demo@mentalcare.local
Password : demo123

Akun demo digunakan untuk melihat seluruh fitur tanpa harus mendaftar manual.

======================================================================

CATATAN PENTING
- Akses aplikasi harus melalui http://localhost, bukan file:///.
- Jika tampilan CSS tidak muncul, lakukan hard refresh (Ctrl + F5).
- Database pada project ini masih menggunakan password plaintext
  (disesuaikan dengan kondisi database saat ini).

======================================================================

STRUKTUR FOLDER UTAMA
- index.php        → Landing page
- login.php        → Halaman login
- register.php     → Halaman registrasi
- dashboard/       → Dashboard pengguna
- journal/         → Fitur jurnal
- discussion/      → Fitur diskusi Q&A
- config/          → Konfigurasi database dan session
- includes/        → Header, navbar, footer
- process/         → Proses form dan aksi database
- assets/          → CSS, JS, icon, gambar

======================================================================

PROYEK INI DIGUNAKAN UNTUK:
- Tugas kuliah IMK (Interaksi Manusia dan Komputer)
- Demo aplikasi web berbasis PHP
- Pembelajaran UI/UX dan alur sistem web

======================================================================
