CREATE DATABASE IF NOT EXISTS perpustakaan_lsp;

USE perpustakaan_lsp;

CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  nama VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  peran ENUM('member','staff') NOT NULL DEFAULT 'member',
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

-- BUKU 
CREATE TABLE buku (
  id_buku BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  judul VARCHAR(200) NOT NULL,
  stok INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

-- PEMINJAMAN 
CREATE TABLE peminjaman (
  id_peminjaman BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_member BIGINT UNSIGNED NOT NULL,
  tanggal_pinjam DATE NOT NULL,
  tanggal_jatuh_tempo DATE NOT NULL,
  status ENUM('dipinjam','dikembalikan') NOT NULL DEFAULT 'dipinjam',
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT fk_peminjaman_member
    FOREIGN KEY (id_member) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
);

-- PEMINJAMAN_DETAIL 
CREATE TABLE peminjaman_detail (
  id_detail BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_peminjaman BIGINT UNSIGNED NOT NULL,
  id_buku BIGINT UNSIGNED NOT NULL,
  qty INT UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT fk_detail_peminjaman
    FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_detail_buku
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  UNIQUE KEY uq_pinjam_buku (id_peminjaman, id_buku)
);

-- DATA BUKU CONTOH
INSERT INTO buku (judul, stok, created_at, updated_at) VALUES
('Atomic Habits', 3, NOW(), NOW()),
('Deep Work', 2, NOW(), NOW()),
('Clean Code', 1, NOW(), NOW());


INSERT INTO users (nama, email, password, peran, created_at, updated_at) VALUES
('Admin Petugas', 'staff@demo.com', '{HASH_STAFF}', 'staff', NOW(), NOW()),
('Budi Member',  'member@demo.com','{HASH_MEMBER}','member', NOW(), NOW());

-- Edit password (password123)
-- UPDATE users
-- SET password = '$2y$12$HELG/Z9Hkz/JfElvzEpH/utkUReGAETb9L6kUH2nYMyQSJtm1ymNi',
--     updated_at = NOW()
-- WHERE email = 'staff@demo.com';

-- UPDATE users
-- SET password = '$2y$12$HELG/Z9Hkz/JfElvzEpH/utkUReGAETb9L6kUH2nYMyQSJtm1ymNi',
--     updated_at = NOW()
-- WHERE email = 'member@demo.com';

-- select * from users;

-- SELECT email, peran, LEFT(password, 10) AS awal_hash, LENGTH(password) AS panjang FROM users;

