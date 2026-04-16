<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_pesanan'])) {
    header('Location: index.php');
    exit();
}

$id_user = (int)$_SESSION['user']['id_user'];
$id_pesanan = (int)$_POST['id_pesanan'];
$metode = isset($_POST['metode']) ? $_POST['metode'] : 'qris';

// Validasi kepemilikan pesanan
$stmt = mysqli_prepare($conn, 'SELECT id_pesanan, id_user, status FROM pesanan WHERE id_pesanan = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id_pesanan);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($res);

if (!$order || (int)$order['id_user'] !== $id_user) {
    header('Location: index.php');
    exit();
}

// Setelah user konfirmasi, pesanan tetap dalam status 'menunggu' sampai admin menyetujui
$status = 'menunggu';

// Simpan metode pembayaran jika kolom tersedia (opsional)
$colCheck = mysqli_query($conn, "SHOW COLUMNS FROM pesanan LIKE 'metode_pembayaran'");
if ($colCheck && mysqli_num_rows($colCheck) > 0) {
    $update = mysqli_prepare($conn, 'UPDATE pesanan SET status = ?, metode_pembayaran = ? WHERE id_pesanan = ?');
    mysqli_stmt_bind_param($update, 'ssi', $status, $metode, $id_pesanan);
    mysqli_stmt_execute($update);
} else {
    $update = mysqli_prepare($conn, 'UPDATE pesanan SET status = ? WHERE id_pesanan = ?');
    mysqli_stmt_bind_param($update, 'si', $status, $id_pesanan);
    mysqli_stmt_execute($update);
}

// Buat notifikasi bahwa konfirmasi pembayaran sudah diterima (tabel dibuat bila belum ada)
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_pesanan INT DEFAULT NULL,
    type VARCHAR(50) DEFAULT 'info',
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$msg = 'Konfirmasi pembayaran Anda sudah diterima. Pesanan menunggu persetujuan admin.';
$stmtNotif = mysqli_prepare($conn, 'INSERT INTO notifications (id_user, id_pesanan, type, message) VALUES (?, ?, ?, ?)');
$type = 'payment_confirmed';
mysqli_stmt_bind_param($stmtNotif, 'iiss', $id_user, $id_pesanan, $type, $msg);
mysqli_stmt_execute($stmtNotif);

// Redirect ke halaman sukses
header('Location: pesanan_berhasil.php?id=' . $id_pesanan);
exit();
