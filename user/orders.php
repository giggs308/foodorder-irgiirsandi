<?php
session_start();
include '../includes/config.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$id_user = $_SESSION['user']['id_user'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Pesanan</title>
</head>
<body>
<h2>Riwayat Pesanan</h2>
<a href="index.php">← Kembali ke Menu</a>
<hr>

<table border="1" cellpadding="10">
<tr>
    <th>ID Pesanan</th>
    <th>Tanggal</th>
    <th>Total</th>
    <th>Status</th>
</tr>
<?php
$orders = mysqli_query($conn, "SELECT * FROM pesanan WHERE id_user=$id_user ORDER BY tanggal DESC");
while ($row = mysqli_fetch_assoc($orders)) {
    echo "<tr>
        <td>{$row['id_pesanan']}</td>
        <td>{$row['tanggal']}</td>
        <td>Rp " . number_format($row['total_harga']) . "</td>
        <td>{$row['status']}</td>
    </tr>";
}
?>
</table>
</body>
</html>
