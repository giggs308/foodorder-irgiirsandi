<?php
session_start();
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_menu'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
    exit();
}

$id_menu = (int)$_POST['id_menu'];
$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
$qty = $qty < 1 ? 1 : $qty;

// Ambil data menu dari database
$stmt = mysqli_prepare($conn, "SELECT id_menu, nama_menu, harga, gambar FROM menu WHERE id_menu = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id_menu);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$menu = mysqli_fetch_assoc($result);

if (!$menu) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Menu tidak ditemukan.'
    ]);
    exit();
}

// Siapkan sesi keranjang
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Tambahkan atau perbarui item di keranjang
if (isset($_SESSION['cart'][$id_menu])) {
    $_SESSION['cart'][$id_menu]['qty'] += $qty;
} else {
    $_SESSION['cart'][$id_menu] = [
        'id_menu'   => $menu['id_menu'],
        'nama_menu' => $menu['nama_menu'],
        'harga'     => (float)$menu['harga'],
        'gambar'    => $menu['gambar'],
        'qty'       => $qty
    ];
}

// Return JSON response for AJAX
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Menu berhasil ditambahkan ke keranjang.'
]);
exit();
