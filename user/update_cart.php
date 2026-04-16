<?php
session_start();

$isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function respond($data, $isAjax)
{
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    if (isset($data['success']) && $data['success']) {
        $_SESSION['message'] = $data['message'] ?? 'Operasi berhasil';
    } else {
        $_SESSION['error'] = $data['message'] ?? 'Terjadi kesalahan';
    }

    header('Location: cart.php');
    exit();
}

if (!isset($_SESSION['user'])) {
    respond([
        'success' => false,
        'message' => 'Silakan login terlebih dahulu.'
    ], $isAjaxRequest);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_menu'], $_POST['qty'])) {
    respond([
        'success' => false,
        'message' => 'Permintaan tidak valid.'
    ], $isAjaxRequest);
}

$id_menu = (int)$_POST['id_menu'];
$qty = max(1, (int)$_POST['qty']);

if (!isset($_SESSION['cart'][$id_menu])) {
    respond([
        'success' => false,
        'message' => 'Item tidak ditemukan dalam keranjang.'
    ], $isAjaxRequest);
}

$_SESSION['cart'][$id_menu]['qty'] = $qty;

// Hitung ulang subtotal dan total item
$harga = isset($_SESSION['cart'][$id_menu]['harga']) ? (float)$_SESSION['cart'][$id_menu]['harga'] : 0;
$itemTotal = $harga * $qty;

$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $itemHarga = isset($item['harga']) ? (float)$item['harga'] : 0;
    $itemQty   = isset($item['qty']) ? (int)$item['qty'] : 1;
    $subtotal += $itemHarga * $itemQty;
}

respond([
    'success'     => true,
    'message'     => 'Jumlah pesanan berhasil diperbarui.',
    'item_total'  => $itemTotal,
    'subtotal'    => $subtotal,
    'id_menu'     => $id_menu,
    'qty'         => $qty
], $isAjaxRequest);
