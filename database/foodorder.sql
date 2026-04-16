-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 15 Des 2025 pada 08.51
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
-- Database: `foodorder`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) DEFAULT NULL,
  `id_menu` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_menu`, `jumlah`, `subtotal`) VALUES
(1, 1, 10, 1, 12000.00),
(2, 2, 10, 2, 24000.00),
(3, 3, 10, 1, 12000.00),
(4, 3, 11, 1, 20000.00),
(5, 3, 12, 1, 10000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu`
--

CREATE TABLE `menu` (
  `id_menu` int(11) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `menu`
--

INSERT INTO `menu` (`id_menu`, `nama_menu`, `deskripsi`, `harga`, `gambar`) VALUES
(10, 'burger', 'burger enakkk', 12000.00, '6900ae8f857b4.jpeg'),
(11, 'pizza', 'mantep nih', 20000.00, '693e90dc532fc.jpg'),
(12, 'nasi uduk emak', 'gacoan tim eksentrik', 10000.00, '693e9194a791f.jpeg'),
(13, 'ketoprak indomie', 'enak nik pengen', 25000.00, '693e931a42c49.jpeg'),
(14, 'dimsum', 'jagonya dimsum', 15000.00, '693e934969fa6.jpg'),
(15, 'es coklat iyaaa', 'enak nih coklat', 10000.00, '693e94cb5dc5d.jpg'),
(16, 'kopi ah au', '', 8000.00, '693e94e407549.jpeg'),
(17, 'pop ice all varian', 'iyaaaa seger', 7000.00, '693e9512267f5.jpg'),
(18, 'jus jus apa yang keren', 'justru iya', 13000.00, '693e95356f73d.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_pesanan` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'info',
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `id_user`, `id_pesanan`, `type`, `message`, `is_read`, `created_at`) VALUES
(1, 3, 2, 'payment_confirmed', 'Konfirmasi pembayaran Anda sudah diterima. Pesanan menunggu persetujuan admin.', 1, '2025-12-14 07:31:59'),
(2, 3, 2, 'order_completed', 'Pesanan Anda selesai. Terima kasih telah memesan!', 1, '2025-12-14 07:39:58'),
(3, 3, 1, 'order_completed', 'Pesanan Anda selesai. Terima kasih telah memesan!', 1, '2025-12-14 08:02:04'),
(4, 3, 3, 'payment_confirmed', 'Konfirmasi pembayaran Anda sudah diterima. Pesanan menunggu persetujuan admin.', 1, '2025-12-14 10:48:10'),
(5, 3, 3, 'order_processing', 'Pembayaran Anda disetujui. Pesanan sedang diproses.', 1, '2025-12-14 10:48:50'),
(6, 3, 3, 'order_shipped', 'Pesanan Anda sedang dalam perjalanan.', 1, '2025-12-14 10:48:54'),
(7, 3, 3, 'order_completed', 'Pesanan Anda selesai. Terima kasih telah memesan!', 1, '2025-12-14 10:49:02'),
(8, 3, 3, 'order_shipped', 'Pesanan Anda sedang dalam perjalanan.', 1, '2025-12-14 10:49:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `total_harga` decimal(10,2) DEFAULT NULL,
  `status` enum('menunggu','diproses','dikirim','selesai') DEFAULT 'menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_user`, `tanggal`, `total_harga`, `status`) VALUES
(1, 3, '2025-12-14 14:24:51', 12000.00, 'selesai'),
(2, 3, '2025-12-14 14:31:41', 24000.00, 'selesai'),
(3, 3, '2025-12-14 17:47:31', 42000.00, 'dikirim');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `role`) VALUES
(2, 'Admin', 'admin@gmail.com', '$2y$10$xZ1yjuCs5B4PGZAa8S2/4.f3IA5c/Zh8MPYenW2ghiLKhSo3hmPeO', 'admin'),
(3, 'irgi', 'irgi@gmail.com', '$2y$10$bKHK/pSLMccfP91kjuFtKujGU8/osvfxqhgaesxZN/T3PMkHKJZmK', 'user'),
(4, 'mulyajamalpardamean', 'mulyajamal@gmail.com', '$2y$10$q5Kfcz0yTdmcyxWiARIiUe0xlozp0QFsS6CdObWsJIYKrhAHOiOZa', 'user'),
(5, 'antares', 'antares@gmail.com', '$2y$10$gAkaW98EevluyWVB1bbtl.uOTBkqHI6KundzyVAGHoldsLXlD6liO', 'user'),
(6, 'iya', 'iya@gmail.com', '$2y$10$sLSpDXO4sEtCUTH31CvD5umZ4kOhHQ9Wv.GsUOudWmtTiBo4Rb17C', 'user'),
(7, 'apa', 'apa@gmail.com', '$2y$10$XlGYu6HSmjCvI5sE8wk5meefL1hYoDXZKSWhWgtADReffXmX51d/e', 'user');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_menu` (`id_menu`);

--
-- Indeks untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`),
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
