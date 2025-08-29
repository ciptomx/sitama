-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------


-- Dumping structure for table ta_management.bimbingan
CREATE TABLE IF NOT EXISTS `bimbingan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tugas_akhir_id` int DEFAULT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL,
  `topik` varchar(255) NOT NULL,
  `catatan` text,
  `status` enum('Menunggu','Dikonfirmasi','Selesai','Dibatalkan') DEFAULT 'Menunggu',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tugas_akhir_id` (`tugas_akhir_id`),
  CONSTRAINT `bimbingan_ibfk_1` FOREIGN KEY (`tugas_akhir_id`) REFERENCES `tugas_akhir` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ta_management.bimbingan: ~1 rows (approximately)
DELETE FROM `bimbingan`;
INSERT INTO `bimbingan` (`id`, `tugas_akhir_id`, `tanggal`, `waktu`, `topik`, `catatan`, `status`, `created_at`) VALUES
	(1, 1, '2025-09-01', '16:30:00', 'BAB 1 Pak', 'Lanjut BAB 2', 'Selesai', '2025-08-29 09:30:30');

-- Dumping structure for table ta_management.dosen
CREATE TABLE IF NOT EXISTS `dosen` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `nidn` varchar(20) NOT NULL,
  `bidang_keahlian` varchar(100) NOT NULL,
  `peran` enum('Pembimbing','Penguji','Keduanya') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nidn` (`nidn`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `dosen_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ta_management.dosen: ~2 rows (approximately)
DELETE FROM `dosen`;
INSERT INTO `dosen` (`id`, `user_id`, `nidn`, `bidang_keahlian`, `peran`) VALUES
	(1, 2, '12345678', 'Kecerdasan Buatan', 'Keduanya'),
	(2, 4, '1130038301', 'HCI', 'Keduanya');

-- Dumping structure for table ta_management.mahasiswa
CREATE TABLE IF NOT EXISTS `mahasiswa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `nim` varchar(10) NOT NULL,
  `prodi` varchar(50) NOT NULL,
  `status` enum('Aktif','Cuti','Lulus') DEFAULT 'Aktif',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nim` (`nim`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ta_management.mahasiswa: ~1 rows (approximately)
DELETE FROM `mahasiswa`;
INSERT INTO `mahasiswa` (`id`, `user_id`, `nim`, `prodi`, `status`) VALUES
	(1, 3, '2011521001', 'Teknik Informatika', 'Aktif');

-- Dumping structure for table ta_management.penguji
CREATE TABLE IF NOT EXISTS `penguji` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ujian_id` int DEFAULT NULL,
  `dosen_id` int DEFAULT NULL,
  `peran` enum('Ketua','Anggota') DEFAULT 'Anggota',
  PRIMARY KEY (`id`),
  KEY `ujian_id` (`ujian_id`),
  KEY `dosen_id` (`dosen_id`),
  CONSTRAINT `penguji_ibfk_1` FOREIGN KEY (`ujian_id`) REFERENCES `ujian` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penguji_ibfk_2` FOREIGN KEY (`dosen_id`) REFERENCES `dosen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ta_management.penguji: ~2 rows (approximately)
DELETE FROM `penguji`;
INSERT INTO `penguji` (`id`, `ujian_id`, `dosen_id`, `peran`) VALUES
	(3, 1, 1, 'Anggota'),
	(4, 1, 2, 'Anggota');

-- Dumping structure for table ta_management.penilaian
CREATE TABLE IF NOT EXISTS `penilaian` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ujian_id` int DEFAULT NULL,
  `dosen_id` int DEFAULT NULL,
  `nilai_presentasi` decimal(4,2) DEFAULT NULL,
  `nilai_makalah` decimal(4,2) DEFAULT NULL,
  `nilai_responsi` decimal(4,2) DEFAULT NULL,
  `nilai_total` decimal(4,2) DEFAULT NULL,
  `catatan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ujian_id` (`ujian_id`),
  KEY `dosen_id` (`dosen_id`),
  CONSTRAINT `penilaian_ibfk_1` FOREIGN KEY (`ujian_id`) REFERENCES `ujian` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penilaian_ibfk_2` FOREIGN KEY (`dosen_id`) REFERENCES `dosen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ta_management.penilaian: ~0 rows (approximately)
DELETE FROM `penilaian`;

-- Dumping structure for table ta_management.system_logs
CREATE TABLE IF NOT EXISTS `system_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ta_management.system_logs: ~2 rows (approximately)
DELETE FROM `system_logs`;
INSERT INTO `system_logs` (`id`, `user_id`, `action`, `description`, `created_at`) VALUES
	(1, 1, 'UPDATE_UJIAN_STATUS', 'Status ujian diubah dari Terjadwal menjadi Selesai untuk mahasiswa Budi Santoso (2011521001)', '2025-08-29 09:55:59'),
	(2, 1, 'GRADUATION', 'Mahasiswa Budi Santoso (2011521001) dinyatakan Lulus', '2025-08-29 09:55:59');

-- Dumping structure for table ta_management.tugas_akhir
CREATE TABLE IF NOT EXISTS `tugas_akhir` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` int DEFAULT NULL,
  `dosen_id` int DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text,
  `proposal_path` varchar(255) DEFAULT NULL,
  `status` enum('Diajukan','Review','Disetujui','Ditolak','Selesai') DEFAULT 'Diajukan',
  `tanggal_pengajuan` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `catatan_dosen` text,
  PRIMARY KEY (`id`),
  KEY `mahasiswa_id` (`mahasiswa_id`),
  KEY `dosen_id` (`dosen_id`),
  CONSTRAINT `tugas_akhir_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tugas_akhir_ibfk_2` FOREIGN KEY (`dosen_id`) REFERENCES `dosen` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ta_management.tugas_akhir: ~1 rows (approximately)
DELETE FROM `tugas_akhir`;
INSERT INTO `tugas_akhir` (`id`, `mahasiswa_id`, `dosen_id`, `judul`, `deskripsi`, `proposal_path`, `status`, `tanggal_pengajuan`, `catatan_dosen`) VALUES
	(1, 1, 1, 'SISTEM NOTIFIKASI DARURAT OTOMATIS PASCA-KECELAKAAN BERBASIS SENSOR FUSION DAN GEOLOKASI', 'JGJGjhAGJ AJSDG JADSG', '../assets/uploads/1756459601_SLR_Report.pdf', 'Disetujui', '2025-08-29 09:26:41', 'Sudah Bagus, Lengkapi Referensi');

-- Dumping structure for table ta_management.ujian
CREATE TABLE IF NOT EXISTS `ujian` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tugas_akhir_id` int DEFAULT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL,
  `ruangan` varchar(50) NOT NULL,
  `status` enum('Terjadwal','Selesai','Dibatalkan') DEFAULT 'Terjadwal',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tugas_akhir_id` (`tugas_akhir_id`),
  CONSTRAINT `ujian_ibfk_1` FOREIGN KEY (`tugas_akhir_id`) REFERENCES `tugas_akhir` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ta_management.ujian: ~1 rows (approximately)
DELETE FROM `ujian`;
INSERT INTO `ujian` (`id`, `tugas_akhir_id`, `tanggal`, `waktu`, `ruangan`, `status`, `created_at`) VALUES
	(1, 1, '2025-08-30', '16:40:00', '202', 'Terjadwal', '2025-08-29 09:33:07');

-- Dumping structure for table ta_management.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','dosen','mahasiswa') NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ta_management.users: ~4 rows (approximately)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `username`, `password`, `role`, `nama`, `email`, `created_at`) VALUES
	(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator', 'admin@university.ac.id', '2025-08-29 07:54:53'),
	(2, 'dosen', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Dr. Ahmad S.T., M.T.', 'ahmad@dosen.university.ac.id', '2025-08-29 07:54:53'),
	(3, 'mahasiswa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Budi Santoso', 'budi@student.university.ac.id', '2025-08-29 07:54:53'),
	(4, 'ciptomx', '$2y$10$15iOmQHSkV7d.YWL6ITx7OlrCuAztWIhFnLP3JuvXTI5DeJvt3wLC', 'dosen', 'SUCIPTO, M.Kom', 'sucipto@unmuhpnk.ac.id', '2025-08-29 09:32:50');
