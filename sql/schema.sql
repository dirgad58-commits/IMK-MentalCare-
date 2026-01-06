CREATE DATABASE IF NOT EXISTS mental_checkin
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mental_checkin;

-- Optional: tabel pengguna (di sini kita pakai guest_id saja, jadi tidak wajib)
CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  display_name VARCHAR(100) NULL,
  guest_token VARCHAR(64) UNIQUE NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS coping_suggestions (
  coping_id INT AUTO_INCREMENT PRIMARY KEY,
  mood_type ENUM('happy','neutral','sad','stressed') NOT NULL,
  title VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  duration_seconds INT NOT NULL DEFAULT 60
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mood_entries (
  mood_id INT AUTO_INCREMENT PRIMARY KEY,
  guest_token VARCHAR(64) NOT NULL,
  mood_type ENUM('happy','neutral','sad','stressed') NOT NULL,
  intensity TINYINT NOT NULL CHECK (intensity BETWEEN 1 AND 5),
  trigger_tag VARCHAR(50) NULL,
  notes VARCHAR(255) NULL,
  entry_date DATE NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_guest_date (guest_token, entry_date),
  INDEX idx_date (entry_date)
) ENGINE=InnoDB;

-- Seed coping suggestions (contoh, bisa Anda tambah)
INSERT INTO coping_suggestions (mood_type, title, description, duration_seconds) VALUES
('happy',   'Syukuri 3 Hal', 'Tulis 3 hal kecil yang membuatmu bersyukur hari ini.', 120),
('happy',   'Bagikan Kebaikan', 'Kirim pesan singkat yang positif ke seseorang.', 60),

('neutral', 'Tarik Napas 60 Detik', 'Tarik napas 4 detik, tahan 2 detik, hembuskan 6 detik. Ulangi 5 kali.', 60),
('neutral', 'Jeda Layar', 'Istirahat dari layar selama 5 menit. Lihat jauh, relaksasikan bahu.', 300),

('sad',     'Jurnal 3 Baris', 'Tulis 3 baris: apa yang kamu rasakan, apa penyebabnya, dan satu langkah kecil berikutnya.', 180),
('sad',     'Gerak Ringan', 'Lakukan peregangan ringan 2–3 menit untuk membantu tubuh rileks.', 180),

('stressed','Grounding 5-4-3-2-1', 'Sebutkan 5 hal yang kamu lihat, 4 yang kamu rasakan, 3 yang kamu dengar, 2 yang kamu cium, 1 yang kamu cicipi.', 180),
('stressed','Prioritas 1 Hal', 'Pilih 1 hal paling penting untuk diselesaikan sekarang. Pecah jadi langkah kecil.', 120);
