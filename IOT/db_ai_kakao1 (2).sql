-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 01 Sep 2026 pada 01.38
-- Versi server: 8.4.3
-- Versi PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_ai_kakao1`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_sensor`
--

CREATE TABLE `data_sensor` (
  `id` int NOT NULL,
  `id_lahan` int DEFAULT NULL,
  `nitrogen` float DEFAULT NULL,
  `fosfor` float DEFAULT NULL,
  `kalium` float DEFAULT NULL,
  `kalsium` float DEFAULT NULL,
  `waktu` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `lahan`
--

CREATE TABLE `lahan` (
  `id` int NOT NULL,
  `id_pengguna` int DEFAULT NULL,
  `api_token` varchar(64) DEFAULT NULL,
  `nama_lahan` varchar(100) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `luas` decimal(5,2) DEFAULT NULL,
  `tanggal_dibuat` datetime DEFAULT CURRENT_TIMESTAMP,
  `jenis_tanaman` varchar(100) DEFAULT NULL,
  `luas_lahan` float DEFAULT NULL,
  `jumlah_sensor` int DEFAULT NULL,
  `rekomendasi_sensor` int DEFAULT NULL,
  `kondisi` text,
  `panjang_atas` float DEFAULT NULL,
  `panjang_bawah` float DEFAULT NULL,
  `sisi_kiri` float DEFAULT NULL,
  `sisi_kanan` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `lahan`
--

INSERT INTO `lahan` (`id`, `id_pengguna`, `api_token`, `nama_lahan`, `lokasi`, `luas`, `tanggal_dibuat`, `jenis_tanaman`, `luas_lahan`, `jumlah_sensor`, `rekomendasi_sensor`, `kondisi`, `panjang_atas`, `panjang_bawah`, `sisi_kiri`, `sisi_kanan`) VALUES
(17, 7, 'lahan1', 'kakao', 'dekat', NULL, '2026-07-14 21:00:06', 'Kakao', 144, 1, 2, 'datar', 12, 12, 12, 12);

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int NOT NULL,
  `id_prediksi` int DEFAULT NULL,
  `tanggal_laporan` date DEFAULT (curdate()),
  `isi_laporan` text,
  `dibuat_oleh` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_pemupukan`
--

CREATE TABLE `laporan_pemupukan` (
  `id` int NOT NULL,
  `id_lahan` int DEFAULT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `jenis_pupuk` varchar(100) DEFAULT NULL,
  `jumlah_pupuk` varchar(50) DEFAULT NULL,
  `metode` varchar(50) DEFAULT NULL,
  `kondisi_tanah` varchar(255) DEFAULT NULL,
  `rekomendasi` text,
  `nitrogen` float DEFAULT NULL,
  `fosfor` float DEFAULT NULL,
  `kalium` float DEFAULT NULL,
  `ph` float DEFAULT NULL,
  `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `laporan_pemupukan`
--

INSERT INTO `laporan_pemupukan` (`id`, `id_lahan`, `tanggal`, `jenis_pupuk`, `jumlah_pupuk`, `metode`, `kondisi_tanah`, `rekomendasi`, `nitrogen`, `fosfor`, `kalium`, `ph`, `catatan`) VALUES
(15, NULL, '2026-02-01 16:05:00', 'pospor', '150g', 'tanam', NULL, NULL, NULL, NULL, NULL, NULL, 'dilakukan pagi hari '),
(16, NULL, '2026-02-01 16:08:00', 'npk', '150g', 'tabur', NULL, NULL, NULL, NULL, NULL, NULL, 'sore hari');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int NOT NULL,
  `parameter` varchar(50) DEFAULT NULL,
  `nilai` varchar(100) DEFAULT NULL,
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `parameter`, `nilai`, `keterangan`) VALUES
(1, 'batas_suhu', '35', 'Suhu maksimal optimal tanaman kakao (°C)'),
(2, 'batas_kelembapan', '85', 'Kelembapan maksimal optimal (%)'),
(3, 'batas_ph', '7', 'PH tanah netral'),
(4, 'model_ai', 'model_pupuk.pkl', 'File model AI untuk prediksi pemupukan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','operator') DEFAULT 'operator',
  `tanggal_daftar` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `nama`, `username`, `password`, `role`, `tanggal_daftar`) VALUES
(2, 'Operator Lapangan', 'operator', '2407bd807d6ca01d1bcd766c730cec9a', 'operator', '2026-04-08 20:20:42'),
(3, 'Edi Kurniawan', 'edikurniawanajja24@gmail.com', '4297f44b13955235245b2497399d7a93', 'operator', '2026-04-08 21:45:01'),
(4, 'Edi Purnomo', 'edip@gmail.com', '4297f44b13955235245b2497399d7a93', 'operator', '2026-04-08 21:47:42'),
(6, 'indriyani', 'indri@gmail.com', '4297f44b13955235245b2497399d7a93', 'admin', '2026-04-09 11:27:31'),
(7, 'Edi Kurniawan', 'admin@gmail.com', '$2y$10$Rq1LGhw1ebHr81Y1OEOpzeXZYvY3ukB077rY955NfJOrSvceoPzZi', 'operator', '2026-06-29 19:53:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `prediksi_ai`
--

CREATE TABLE `prediksi_ai` (
  `id` int NOT NULL,
  `id_sensor` int DEFAULT NULL,
  `status_tanaman` varchar(50) DEFAULT NULL,
  `rekomendasi_pupuk` text,
  `confidence` float DEFAULT NULL,
  `waktu_prediksi` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_lahan` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pupuk`
--

CREATE TABLE `pupuk` (
  `id` int NOT NULL,
  `nama_pupuk` varchar(100) DEFAULT NULL,
  `kandungan` varchar(100) DEFAULT NULL,
  `jenis` varchar(100) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `deskripsi` text,
  `dosis_anjuran` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `pupuk`
--

INSERT INTO `pupuk` (`id`, `nama_pupuk`, `kandungan`, `jenis`, `gambar`, `deskripsi`, `dosis_anjuran`) VALUES
(1, 'Pupuk NPK 16-16-16', 'n16, f16, k16', NULL, 'pupuk1.jpg', 'Komposisi seimbang untuk pertumbuhan vegetatif dan generatif.', NULL),
(2, 'Pupuk Urea', 'f100', NULL, 'pupuk2.jpg', 'Meningkatkan kadar Nitrogen.', NULL),
(3, 'Pupuk KCL', 'k20, f50', NULL, 'pupuk3.jpg', 'Memperkuat ketahanan tanaman.', NULL),
(4, 'Pupuk SP-36', 'f65, k21', NULL, 'pupuk4.jpg', 'Kaya Fosfor untuk pembentukan akar dan bunga.', NULL),
(5, 'urea 16-17-8', 'n20, f42, k21', NULL, NULL, 'pertumbuahan', '200gram');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sensor_data`
--

CREATE TABLE `sensor_data` (
  `id` int NOT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `suhu` float DEFAULT NULL,
  `kelembapan` float DEFAULT NULL,
  `ph_tanah` float DEFAULT NULL,
  `kelembapan_tanah` float DEFAULT NULL,
  `cahaya` float DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `id_lahan` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `sensor_data`
--

INSERT INTO `sensor_data` (`id`, `tanggal`, `suhu`, `kelembapan`, `ph_tanah`, `kelembapan_tanah`, `cahaya`, `lokasi`, `id_lahan`) VALUES
(207, '2026-07-15 11:06:08', 0, 0, 6.5, 0, 500, NULL, 17),
(208, '2026-07-15 11:06:18', 0, 0, 6.5, 0, 500, NULL, 17),
(209, '2026-07-15 11:06:28', 0, 0, 6.5, 0, 500, NULL, 17),
(210, '2026-07-15 11:06:38', 0, 0, 6.5, 0, 500, NULL, 17),
(211, '2026-07-15 11:06:49', 0, 0, 6.5, 0, 500, NULL, 17),
(212, '2026-07-15 11:06:59', 0, 0, 6.5, 0, 500, NULL, 17);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `data_sensor`
--
ALTER TABLE `data_sensor`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `lahan`
--
ALTER TABLE `lahan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_token` (`api_token`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_prediksi` (`id_prediksi`);

--
-- Indeks untuk tabel `laporan_pemupukan`
--
ALTER TABLE `laporan_pemupukan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `prediksi_ai`
--
ALTER TABLE `prediksi_ai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sensor` (`id_sensor`);

--
-- Indeks untuk tabel `pupuk`
--
ALTER TABLE `pupuk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `data_sensor`
--
ALTER TABLE `data_sensor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `lahan`
--
ALTER TABLE `lahan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `laporan_pemupukan`
--
ALTER TABLE `laporan_pemupukan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `prediksi_ai`
--
ALTER TABLE `prediksi_ai`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pupuk`
--
ALTER TABLE `pupuk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `lahan`
--
ALTER TABLE `lahan`
  ADD CONSTRAINT `lahan_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_prediksi`) REFERENCES `prediksi_ai` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `prediksi_ai`
--
ALTER TABLE `prediksi_ai`
  ADD CONSTRAINT `prediksi_ai_ibfk_1` FOREIGN KEY (`id_sensor`) REFERENCES `sensor_data` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
