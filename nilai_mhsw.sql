-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 28 Okt 2026 pada 02.21
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
-- Database: `db5d`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `nilai_mhsw`
--

CREATE TABLE `nilai_mhsw` (
  `nim` varchar(15) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) NOT NULL,
  `nilai_huruf` char(1) NOT NULL,
  `predikat` varchar(30) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `tgl_diubah` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `nilai_mhsw`
--

INSERT INTO `nilai_mhsw` (`nim`, `nama`, `photo`, `nilai_akhir`, `nilai_huruf`, `predikat`, `reg_date`, `tgl_diubah`) VALUES
('3202416020', 'Ahyas Ulumudin', 'b3dca7cedaa100e0.jpg', 78.05, 'B', 'Memuaskan', '2026-10-15 04:01:10', '2026-10-27 15:17:27'),
('3202416022', 'Acinalimii', 'bf14d8566e3a8209.jpg', 10.00, 'E', 'Tidak Lulus', '2026-10-21 02:49:27', '2026-10-27 15:19:10'),
('3202416024', 'Aurin', '7b9815c2db2d89a7.jpg', 22.00, 'E', 'Tidak Lulus', '2026-10-14 06:44:30', '2026-10-27 15:16:59'),
('3202416025', 'Ahmad', 'e37197736600a192.jpg', 66.00, 'B', 'Memuaskan', '2026-10-21 02:50:20', '2026-10-27 15:18:21'),
('3202416026', 'Zaskiaa', 'ef37338ebd97e42b.jpg', 56.00, 'C', 'Cukup Memuaskan', '2026-10-14 06:45:47', '2026-10-27 15:17:14'),
('3202416029', 'Zahria Rahmadani', 'b5184e4ca01dcefc.jpg', 99.00, 'A', 'Sangat Memuaskan', '2026-10-08 04:20:23', '2026-10-27 15:15:28'),
('3202416056', 'Zoya Azkya', 'dcf66881ddda0ddc.jpg', 43.00, 'D', 'Kurang Memuaskan', '2026-10-15 04:07:05', '2026-10-27 15:18:09'),
('3202416122', 'Rani Imutt', '05f66494ac4be043.jpg', 99.00, 'A', 'Sangat Memuaskan', '2026-10-15 04:02:29', '2026-10-27 15:17:42'),
('3202416123', 'Siang Nir', 'fa848b1669669915.jpg', 87.00, 'A', 'Sangat Memuaskan', '2026-10-15 04:05:34', '2026-10-27 15:17:57'),
('3202416125', 'Melisaa', 'c24fa9cb82c2acc4.jpg', 99.79, 'A', 'Sangat Memuaskan', '2026-10-14 06:42:10', '2026-10-27 14:46:52'),
('3202416566', 'Jaja', 'd07de8569accfef1.jpg', 67.00, 'B', 'Memuaskan', '2026-10-27 06:08:53', '2026-10-27 15:18:36');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `nilai_mhsw`
--
ALTER TABLE `nilai_mhsw`
  ADD PRIMARY KEY (`nim`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
