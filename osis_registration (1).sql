-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2025 at 08:18 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `osis_registration`
--

-- --------------------------------------------------------

--
-- Table structure for table `dokumen`
--

CREATE TABLE `dokumen` (
  `id` int(11) NOT NULL,
  `pendaftaran_id` int(11) NOT NULL,
  `jenis_dokumen` enum('surat_izin','pas_foto','nilai_rapor','sertifikat','rekomendasi') NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `ukuran_file` int(11) NOT NULL,
  `path_file` varchar(500) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_seleksi`
--

CREATE TABLE `jadwal_seleksi` (
  `id` int(11) NOT NULL,
  `tahapan` varchar(50) NOT NULL,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_selesai` datetime NOT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_seleksi`
--

INSERT INTO `jadwal_seleksi` (`id`, `tahapan`, `tanggal_mulai`, `tanggal_selesai`, `lokasi`, `deskripsi`, `is_active`, `created_at`) VALUES
(1, 'Pendaftaran Online', '2024-06-14 08:00:00', '2024-06-15 17:00:00', 'Website OSIS', 'Periode pendaftaran calon pengurus OSIS secara online', 1, '2025-05-22 04:09:55'),
(2, 'Verifikasi Berkas', '2024-06-16 08:00:00', '2024-06-20 17:00:00', 'Ruang OSIS', 'Verifikasi dokumen dan berkas pendaftaran', 1, '2025-05-22 04:09:55'),
(3, 'Tes Tulis', '2024-06-22 08:00:00', '2024-06-22 10:00:00', 'Ruang Kelas', 'Tes tulis pengetahuan umum dan kepemimpinan', 1, '2025-05-22 04:09:55'),
(4, 'Wawancara', '2024-06-25 08:00:00', '2024-06-26 17:00:00', 'Ruang OSIS', 'Wawancara dengan panitia seleksi', 1, '2025-05-22 04:09:55'),
(5, 'Pengumuman Hasil', '2024-06-30 10:00:00', '2024-06-30 17:00:00', 'Website OSIS', 'Pengumuman hasil seleksi OSIS', 1, '2025-05-22 04:09:55');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran_osis`
--

CREATE TABLE `pendaftaran_osis` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `motivasi` text NOT NULL,
  `pengalaman_organisasi` text DEFAULT NULL,
  `visi_misi` text DEFAULT NULL,
  `program_kerja` text DEFAULT NULL,
  `status_pendaftaran` enum('draft','submitted','verified','rejected') DEFAULT 'draft',
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `isi` text NOT NULL,
  `tipe` enum('info','jadwal','hasil') NOT NULL,
  `target_audience` enum('all','siswa','admin') DEFAULT 'all',
  `is_published` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `judul`, `isi`, `tipe`, `target_audience`, `is_published`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Pendaftaran OSIS 2024 Dibuka', 'Pendaftaran calon pengurus OSIS tahun 2024 telah dibuka. Silakan lengkapi data dan upload dokumen yang diperlukan.', 'info', 'all', 1, 1, '2025-05-22 04:09:55', '2025-05-22 04:09:55'),
(2, 'Jadwal Seleksi OSIS 2024', 'Berikut adalah jadwal lengkap seleksi OSIS tahun 2024. Harap perhatikan setiap tahapan dengan baik.', 'jadwal', 'all', 1, 1, '2025-05-22 04:09:55', '2025-05-22 04:09:55');

-- --------------------------------------------------------

--
-- Table structure for table `penilaian`
--

CREATE TABLE `penilaian` (
  `id` int(11) NOT NULL,
  `pendaftaran_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `nilai_akademik` decimal(5,2) DEFAULT 0.00,
  `nilai_kepemimpinan` decimal(5,2) DEFAULT 0.00,
  `nilai_komunikasi` decimal(5,2) DEFAULT 0.00,
  `nilai_organisasi` decimal(5,2) DEFAULT 0.00,
  `nilai_wawancara` decimal(5,2) DEFAULT 0.00,
  `nilai_akhir` decimal(5,2) DEFAULT 0.00,
  `catatan` text DEFAULT NULL,
  `status_kelulusan` enum('pending','lulus','tidak_lulus') DEFAULT 'pending',
  `tanggal_penilaian` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `jurusan` varchar(50) NOT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `user_id`, `nis`, `kelas`, `jurusan`, `no_hp`, `alamat`) VALUES
(1, 2, '34028', 'X', 'TKJ', '098765432', 'ompen'),
(2, 4, '340506', 'XI', 'TSM', '08123456789', 'JL. Sukarno');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','siswa') NOT NULL DEFAULT 'siswa',
  `nama_lengkap` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `nama_lengkap`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'admin', 'admin@smkn2sampang.sch.id', '0192023a7bbd73250516f069df18b500', 'admin', 'Administrator OSIS', '2025-05-22 04:09:55', '2025-05-22 04:09:55', 1),
(2, 'budi', 'budi@gmail.com', '0f409098f2be3b1ac91ee09247ef303e', 'siswa', 'budi santoso', '2025-05-22 04:37:27', '2025-05-22 04:37:27', 1),
(3, 'admin2', 'admin2@gmail.com', 'c84258e9c39059a89ab77d846ddab909', 'admin', 'admin', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0),
(4, 'ivan', 'ivan@gmail.com', 'b7727d252be76bc6d142e19315cfc8fd', 'siswa', 'ivan sanjaya', '2025-05-22 06:23:12', '2025-05-22 06:23:12', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dokumen`
--
ALTER TABLE `dokumen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftaran_id` (`pendaftaran_id`);

--
-- Indexes for table `jadwal_seleksi`
--
ALTER TABLE `jadwal_seleksi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pendaftaran_osis`
--
ALTER TABLE `pendaftaran_osis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `penilaian`
--
ALTER TABLE `penilaian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftaran_id` (`pendaftaran_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dokumen`
--
ALTER TABLE `dokumen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal_seleksi`
--
ALTER TABLE `jadwal_seleksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pendaftaran_osis`
--
ALTER TABLE `pendaftaran_osis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `penilaian`
--
ALTER TABLE `penilaian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dokumen`
--
ALTER TABLE `dokumen`
  ADD CONSTRAINT `dokumen_ibfk_1` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_osis` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pendaftaran_osis`
--
ALTER TABLE `pendaftaran_osis`
  ADD CONSTRAINT `pendaftaran_osis_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD CONSTRAINT `pengumuman_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `penilaian`
--
ALTER TABLE `penilaian`
  ADD CONSTRAINT `penilaian_ibfk_1` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_osis` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penilaian_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
