-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Jan 2026 pada 10.44
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mentalcare_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `answers`
--

CREATE TABLE `answers` (
  `answer_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_best` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `answers`
--

INSERT INTO `answers` (`answer_id`, `question_id`, `user_id`, `content`, `is_best`, `created_at`) VALUES
(8, 1, 1, 'dd', 0, '2026-01-06 05:26:14'),
(9, 3, 1, 'Coba mulai dari teknik napas 4-6: tarik 4 detik, hembus 6 detik, ulang 5 menit. Lalu tulis 3 hal yang kamu rasakan (fisik) + 3 pikiran yang muncul. Ini bantu “mengurai” cemas jadi lebih terstruktur.', 0, '2025-12-26 13:49:24'),
(10, 3, 1, 'Aku pernah di fase itu. Yang paling membantu: kurangi kafein 3–5 hari + jalan 15 menit sore. Bukan menyembuhkan, tapi menurunkan “intensitas” cemas. Kalau makin sering/berat, pertimbangkan konsultasi profesional ya.', 0, '2025-12-26 13:49:24'),
(11, 4, 1, 'Coba pakai “minimum viable task”: target 10 menit saja untuk mulai (buka materi, tulis outline). Setelah itu bebas stop. Biasanya inertia yang paling berat. Jangan target langsung besar.', 0, '2025-12-28 13:49:24'),
(12, 4, 1, 'Burnout sering karena beban + kurang pemulihan. Bikin jadwal 3 blok: fokus 45 menit, istirahat 10 menit, lalu blok ringan (rapikan catatan). Pastikan tidur dulu diperbaiki. Produktif tanpa tidur itu utang yang dibayar mahal.', 0, '2025-12-28 13:49:24'),
(13, 4, 1, 'Kalau sudah sampai “mati rasa” dan lama, bisa jadi kamu butuh jeda terencana. Pilih 1–2 tugas paling penting, sisanya negosiasi deadline/kurangi scope. Itu bukan malas—itu strategi.', 0, '2025-12-28 13:49:24'),
(14, 5, 2, 'Rutinitas yang cukup membantu: 30 menit sebelum tidur “brain dump” (tulis semua pikiran), lalu pilih 3 hal yang bisa dikerjakan besok. Setelah itu stop problem solving. Otak butuh sinyal “sudah ditampung”.', 0, '2025-12-29 13:49:24'),
(15, 5, 1, 'Coba “worry time” sore hari 15 menit: kamu jadwalkan waktu khusus untuk mikir. Malam jadi bukan tempatnya. Plus batasi layar 45 menit sebelum tidur.', 0, '2025-12-29 13:49:24'),
(16, 6, 1, 'Latihan paling efektif buatku: rekam 60 detik penjelasan inti, putar ulang, perbaiki. Ulang 3 kali. Fokus pada “pesan utama” bukan perfeksionisme.', 0, '2025-12-30 13:49:24'),
(17, 6, 1, 'Saat di depan: pegang pulpen kecil atau pointer. Bukan buat gaya, tapi memberi “anchor” biar tangan tidak liar. Lalu taruh 3 poin di kartu kecil. Itu mengurangi takut blank.', 0, '2025-12-30 13:49:24'),
(18, 7, 2, 'Normal kok. Kadang kita butuh koneksi yang “dalam”, bukan sekadar ramai. Coba pilih 1 orang yang cukup aman lalu ngobrol lebih jujur sedikit demi sedikit.', 0, '2025-12-31 13:49:24'),
(19, 7, 1, 'Aku juga pernah. Aku mulai dari aktivitas yang terasa “bermakna” (komunitas/organisasi kecil) daripada nongkrong tanpa arah. Setelah itu baru koneksi sosial terasa lebih nyata.', 0, '2025-12-31 13:49:24'),
(20, 8, 1, 'Coba latihan “bukti vs asumsi”: tulis pencapaian + bukti konkret (mis. tugas selesai, revisi berkurang). Lalu pisahkan dari asumsi “aku cuma beruntung”. Ini pelan-pelan melatih otak melihat fakta.', 0, '2026-01-01 13:49:24'),
(21, 8, 1, 'Kalau ada pikiran “aku gagal”, coba ubah jadi “aku sedang belajar”. Bukan kalimat motivasi kosong, tapi framing yang lebih realistis.', 0, '2026-01-01 13:49:24'),
(22, 9, 1, 'Mulai dari aturan kecil: medsos hanya setelah 1 tugas mini selesai (contoh: 1 paragraf). Lalu pasang timer 10 menit. Kalau perlu, hapus app dari home screen.', 0, '2026-01-02 13:49:24'),
(23, 9, 1, 'Aku pakai trik “ubah lingkungan”: kalau belajar, taruh HP di luar jangkauan (tas/lemari). Ini mengurangi impuls tanpa harus mengandalkan niat.', 0, '2026-01-02 13:49:24'),
(24, 10, 1, 'Coba pause 20 detik sebelum respon. Kedengarannya sepele, tapi itu memberi waktu otak pindah dari reaktif ke sadar. Kalau perlu, keluar ruangan sebentar untuk pendinginan.', 0, '2026-01-03 13:49:24'),
(25, 10, 1, 'Bikin “kalimat jangkar”: “Aku butuh waktu, kita lanjut nanti.” Ini bantu komunikasi tanpa memperkeruh. Setelah tenang, baru bahas inti masalah.', 0, '2026-01-03 13:49:24'),
(26, 11, 2, 'Jangan memaksa “lupa cepat”. Coba buat batas: jangan cek sosmed dia 7 hari. Isi waktu dengan rutinitas kecil (jalan, mandi rapi, makan teratur). Stabilitas fisik bantu stabilitas emosi.', 0, '2026-01-04 13:49:24'),
(27, 11, 1, 'Kalau ada trigger tertentu (lagu/tempat), kamu bisa pelan-pelan exposure: bukan menghindar terus, tapi pilih waktu saat kamu siap dan ditemani aktivitas yang menenangkan.', 0, '2026-01-04 13:49:24'),
(28, 12, 1, 'Saat terjadi: fokus ke grounding 5-4-3-2-1 (5 benda terlihat, 4 terasa, 3 terdengar, 2 tercium, 1 terasa di mulut). Itu bantu otak kembali ke “saat ini”.', 0, '2026-01-05 13:49:24'),
(29, 12, 1, 'Kalau sering berulang, sebaiknya cek medis juga untuk memastikan bukan masalah fisik. Setelah aman, baru lanjut ke manajemen cemas/panic. Ini bukan untuk menakut-nakuti, justru untuk memastikan aman.', 0, '2026-01-05 13:49:24'),
(30, 13, 1, 'Coba “deadline palsu”: tetapkan due date internal 2 hari lebih awal dan lapor progres ke teman (accountability). Prokrastinasi sering kalah oleh komitmen sosial.', 0, '2026-01-05 17:49:25'),
(31, 13, 1, 'Breakdown tugas jadi “langkah pertama super kecil”: buka file, tulis judul, buat 3 bullet. Jangan mulai dari “selesaikan semuanya”.', 0, '2026-01-05 19:49:25'),
(32, 14, 2, 'Template 3 kalimat: “Hari ini saya merasa…”, “Pemicu utamanya…”, “Langkah kecil besok…”. Jangan panjang, yang penting konsisten.', 0, '2026-01-06 03:49:25'),
(33, 14, 1, 'Kalau mau lebih terstruktur: nilai mood 1–10 + 1 hal yang kamu syukuri + 1 hal yang ingin kamu maafkan (diri sendiri/orang lain).', 0, '2026-01-06 04:49:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `answer_votes`
--

CREATE TABLE `answer_votes` (
  `vote_id` int(11) NOT NULL,
  `answer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote_type` enum('upvote') DEFAULT 'upvote',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `contact_messages`
--

CREATE TABLE `contact_messages` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(80) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `type` enum('teknis','masukan','laporan_konten','lainnya') NOT NULL DEFAULT 'teknis',
  `subject` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `target_type` enum('question','answer','journal','other') DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `status` enum('baru','diproses','selesai') NOT NULL DEFAULT 'baru',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `content_reports`
--

CREATE TABLE `content_reports` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reporter_name` varchar(80) NOT NULL,
  `reporter_email` varchar(120) NOT NULL,
  `reason` varchar(150) NOT NULL,
  `details` text NOT NULL,
  `target_type` enum('question','answer','journal','other') NOT NULL DEFAULT 'other',
  `target_id` int(11) DEFAULT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `status` enum('baru','diproses','selesai') NOT NULL DEFAULT 'baru',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `journals`
--

CREATE TABLE `journals` (
  `journal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `content` text NOT NULL,
  `mood` enum('senang','netral','sedih','cemas','marah') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `journals`
--

INSERT INTO `journals` (`journal_id`, `user_id`, `title`, `content`, `mood`, `created_at`) VALUES
(1, 2, 'Hari ini saya ingin lebih tenang', 'Hari ini saya merasa agak penuh pikiran.\nPemicu utamanya: banyak hal kecil menumpuk sekaligus.\nLangkah kecil saya: mulai dari satu tugas ringan dan istirahat 10 menit.', 'cemas', '2025-12-25 13:52:10'),
(2, 2, 'Progress kecil tetap progress', 'Hari ini saya berhasil menyelesaikan satu hal penting walau tidak sempurna.\nSaya belajar bahwa memulai lebih sulit daripada melanjutkan.\nBesok saya akan memulai lebih cepat 10 menit.', '', '2025-12-26 13:52:10'),
(3, 2, 'Malam ini saya butuh tidur lebih rapi', 'Saya sadar pola tidur saya berantakan beberapa hari terakhir.\nAkibatnya fokus menurun dan emosi lebih sensitif.\nMalam ini saya akan stop layar 45 menit sebelum tidur.', '', '2025-12-27 13:52:10'),
(4, 2, 'Mengurangi overthinking', 'Saya menangkap pikiran yang berulang-ulang tentang hal yang belum tentu terjadi.\nSaya coba menuliskan kekhawatiran lalu memilih mana yang bisa dikontrol.\nHari ini saya hanya fokus pada yang bisa saya lakukan.', '', '2025-12-28 13:52:10'),
(5, 2, 'Berani bilang “cukup” untuk hari ini', 'Hari ini saya merasa terdorong untuk menyelesaikan semuanya sekaligus.\nSaya ingat bahwa batas energi itu nyata.\nSaya pilih berhenti saat cukup dan lanjutkan besok.', '', '2025-12-29 13:52:10'),
(6, 2, 'Refleksi singkat setelah interaksi sosial', 'Tadi saya bertemu beberapa teman.\nSaya senang, tapi juga merasa agak terkuras.\nSaya belajar untuk memberi jeda dan tidak memaksa selalu “on”.', 'netral', '2025-12-30 13:52:10'),
(7, 2, 'Aku menunda lagi, tapi masih bisa diperbaiki', 'Saya menunda satu tugas dan merasa bersalah.\nSaya coba ganti rasa bersalah jadi rencana: mulai dari 10 menit pertama.\nSetelah mulai, ternyata tidak seberat yang saya bayangkan.', 'cemas', '2026-01-01 13:52:10'),
(8, 2, 'Hari yang cukup baik', 'Tidak ada hal besar, tapi hari ini terasa lebih ringan.\nSaya bersyukur untuk hal kecil: makan teratur dan pekerjaan selesai sebagian.\nSaya ingin mempertahankan ritme ini.', 'senang', '2026-01-03 13:52:10'),
(9, 2, 'Mengatur prioritas biar tidak tenggelam', 'Saya menuliskan 3 prioritas utama hari ini.\nSisanya saya taruh di daftar “nanti”, bukan untuk dilupakan, tapi untuk ditunda dengan sadar.\nSaya merasa lebih terkendali.', '', '2026-01-04 13:52:10'),
(10, 2, 'Penutup hari: evaluasi tanpa menghakimi', 'Saya mengevaluasi hari ini dengan lebih lembut.\nSaya masih punya kekurangan, tapi juga ada hal yang sudah saya usahakan.\nBesok saya ingin mulai dengan satu langkah kecil.', '', '2026-01-06 01:52:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `questions`
--

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `is_anonymous` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `questions`
--

INSERT INTO `questions` (`question_id`, `user_id`, `title`, `content`, `category`, `is_anonymous`, `created_at`) VALUES
(1, 1, 'dd', 'd', NULL, 0, '2026-01-06 05:26:04'),
(2, 1, 'm', 's', ',', 0, '2026-01-06 07:46:17'),
(3, 2, 'Sering cemas tanpa alasan jelas, mulai dari mana?', 'Akhir-akhir ini saya sering gelisah padahal tidak ada masalah besar. Detak jantung cepat, kepala penuh pikiran, dan jadi sulit fokus. Apa langkah kecil yang bisa saya coba untuk menenangkan diri?', 'Kecemasan', 0, '2025-12-25 13:49:24'),
(4, 1, 'Burnout kuliah: tugas banyak tapi badan “nggak mau jalan”', 'Saya merasa capek terus. Niat belajar ada, tapi ketika mulai, rasanya berat sekali dan akhirnya menunda. Bagaimana cara membagi energi dan ritme supaya tidak tumbang?', 'Burnout', 0, '2025-12-27 13:49:24'),
(5, 1, 'Susah tidur karena overthinking, sudah coba musik tapi tetap kepikiran', 'Setiap malam pikiran jalan terus tentang besok dan hal-hal yang belum selesai. Akhirnya tidur jam 2–3. Ada rutinitas malam yang efektif?', 'Tidur', 1, '2025-12-28 13:49:24'),
(6, 1, 'Takut presentasi: tangan dingin, suara gemetar', 'Setiap mau presentasi saya panik. Saya takut salah dan jadi blank. Ada latihan yang aman supaya lebih stabil?', 'Kepercayaan Diri', 0, '2025-12-29 13:49:24'),
(7, 1, 'Merasa kesepian walau punya teman', 'Saya sering kumpul, tapi tetap merasa kosong. Kadang capek “berakting” biar kelihatan baik-baik saja. Apa ini normal?', 'Relasi Sosial', 0, '2025-12-30 13:49:24'),
(8, 1, 'Ngerasa “nggak cukup baik” walau sudah berusaha', 'Nilai saya tidak buruk, tapi saya selalu merasa kurang. Kalau berhasil, rasanya cuma “kebetulan”. Bagaimana cara mengubah pola pikir ini?', 'Self-esteem', 0, '2025-12-31 13:49:24'),
(9, 2, 'Ketagihan scroll sampai lupa waktu, cara berhenti gimana?', 'Saya sering niat 5 menit buka medsos, tapi tiba-tiba 1 jam lewat. Jadinya tugas keteteran dan makin stres. Ada strategi yang benar-benar kepakai?', 'Produktivitas', 1, '2026-01-01 13:49:24'),
(10, 1, 'Sering konflik di rumah, jadi cepat emosi', 'Di rumah suasananya tegang, saya jadi gampang marah dan menyesal setelahnya. Bagaimana cara mengelola emosi tanpa meledak?', 'Keluarga', 0, '2026-01-02 13:49:24'),
(11, 1, 'Setelah putus, saya susah fokus dan kepikiran terus', 'Hubungan saya baru berakhir. Saya pengen move on, tapi tiap hari keinget. Ada cara sederhana untuk pulih tanpa memaksa?', 'Relasi', 1, '2026-01-03 13:49:24'),
(12, 1, 'Pernah sesak dan takut mati mendadak, itu panic attack kah?', 'Beberapa kali saya tiba-tiba sesak, pusing, tangan dingin, dan takut mati. Setelah 10–15 menit reda. Apa yang harus saya lakukan saat terjadi?', 'Kecemasan', 0, '2026-01-04 13:49:24'),
(13, 1, 'Prokrastinasi: selalu mulai mepet deadline, capek sendiri', 'Saya selalu mulai mepet deadline. Pas mepet malah panik dan hasil kurang maksimal. Ada metode yang cocok untuk kebiasaan ini?', 'Produktivitas', 0, '2026-01-05 13:49:24'),
(14, 1, 'Ingin mulai journaling tapi bingung harus nulis apa', 'Saya ingin mulai jurnal untuk merapikan pikiran, tapi setiap buka halaman kosong jadi bingung. Ada template yang gampang?', 'Refleksi', 0, '2026-01-06 01:49:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `full_name`, `email`, `phone`, `bio`, `photo`, `password`, `created_at`, `updated_at`) VALUES
(1, 'dirga', NULL, 'dirgad58@gmail.com', NULL, NULL, NULL, 'dirga123', '2026-01-05 13:12:31', NULL),
(2, 'reno44', 'La Ode Hasman', 'reno@gmail.com', '082282919102', '', 'uploads/avatars/u2_009a31ef87cf.jpg', 'reno123', '2026-01-06 07:51:13', '2026-01-06 17:04:22');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `answer_votes`
--
ALTER TABLE `answer_votes`
  ADD PRIMARY KEY (`vote_id`),
  ADD UNIQUE KEY `answer_id` (`answer_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `type` (`type`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indeks untuk tabel `content_reports`
--
ALTER TABLE `content_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `target_type` (`target_type`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indeks untuk tabel `journals`
--
ALTER TABLE `journals`
  ADD PRIMARY KEY (`journal_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `answers`
--
ALTER TABLE `answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `answer_votes`
--
ALTER TABLE `answer_votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `content_reports`
--
ALTER TABLE `content_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `journals`
--
ALTER TABLE `journals`
  MODIFY `journal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `answers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `answer_votes`
--
ALTER TABLE `answer_votes`
  ADD CONSTRAINT `answer_votes_ibfk_1` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`answer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `answer_votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `fk_contact_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `content_reports`
--
ALTER TABLE `content_reports`
  ADD CONSTRAINT `fk_reports_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `journals`
--
ALTER TABLE `journals`
  ADD CONSTRAINT `journals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
