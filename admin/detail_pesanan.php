<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$id_pesanan = $_GET['id'];

// Ambil data pesanan
$pesanan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT p.*, u.nama AS nama_user, u.email 
    FROM pesanan p 
    JOIN users u ON p.id_user = u.id_user 
    WHERE p.id_pesanan = '$id_pesanan'
"));

// Ambil detail item pesanan
$items = mysqli_query($conn, "
    SELECT dp.*, m.nama_menu, m.harga 
    FROM detail_pesanan dp 
    JOIN menu m ON dp.id_menu = m.id_menu 
    WHERE dp.id_pesanan = '$id_pesanan'
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Pesanan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: center;
        }
        th {
            background: #f9f9f9;
        }
    </style>
</head>
<body>

<h2>📋 Detail Pesanan #<?= $pesanan['id_pesanan'] ?></h2>
<p><a href="pesanan.php">⬅ Kembali</a></p>

<hr>

<h3>Informasi Pelanggan</h3>
<p><b>Nama:</b> <?= $pesanan['nama_user'] ?><br>
<b>Email:</b> <?= $pesanan['email'] ?><br>
<b>Tanggal Pesan:</b> <?= $pesanan['tanggal_pesan'] ?><br>
<b>Status:</b> <?= ucfirst($pesanan['status']) ?><br>
<b>Total Bayar:</b> Rp <?= number_format($pesanan['total_bayar']) ?></p>

<hr>

<h3>Daftar Makanan Dipesan</h3>
<table>
    <tr>
        <th>Nama Menu</th>
        <th>Jumlah</th>
        <th>Harga Satuan</th>
        <th>Subtotal</th>
    </tr>
    <?php 
    $total = 0;
    while ($row = mysqli_fetch_assoc($items)) {
        $subtotal = $row['jumlah'] * $row['harga'];
        $total += $subtotal;
    ?>
    <tr>
        <td><?= $row['nama_menu'] ?></td>
        <td><?= $row['jumlah'] ?></td>
        <td>Rp <?= number_format($row['harga']) ?></td>
        <td>Rp <?= number_format($subtotal) ?></td>
    </tr>
    <?php } ?>
    <tr>
        <th colspan="3">Total</th>
        <th>Rp <?= number_format($total) ?></th>
    </tr>
</table>

</body>
</html>
