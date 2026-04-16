<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Anda harus login terlebih dahulu',
        'redirect' => '../login.php'
    ]);
    exit();
}

// Check if cart exists in session
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Keranjang belanja kosong',
        'redirect' => 'index.php'
    ]);
    exit();
}

// Check if id_menu is provided
if (!isset($_POST['id_menu'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID menu tidak valid'
    ]);
    exit();
}

$id_menu = (int)$_POST['id_menu'];
$item_found = false;

// Debug logging
error_log("Attempting to remove item with id_menu: $id_menu from cart");
error_log("Current cart contents: " . print_r($_SESSION['cart'], true));

// Remove directly using associative key if present
if (isset($_SESSION['cart'][$id_menu])) {
    error_log("Item found in cart by key, attempting to remove...");
    unset($_SESSION['cart'][$id_menu]);
    $item_found = true;
} else {
    // Fallback: search by item content (for legacy carts)
    foreach ($_SESSION['cart'] as $key => $item) {
        if ((int)$item['id_menu'] === $id_menu) {
            error_log("Item found in cart (legacy numeric index), attempting to remove...");
            unset($_SESSION['cart'][$key]);
            $item_found = true;
            break;
        }
    }
}

if ($item_found) {
    
    // Debug logging
    error_log("Cart contents after removal: " . print_r($_SESSION['cart'], true));
    
    // Calculate new subtotal
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $harga = isset($item['harga']) ? (float)$item['harga'] : 0;
        $qty   = isset($item['qty']) ? (int)$item['qty'] : 1;
        $subtotal += $harga * $qty;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Item berhasil dihapus dari keranjang',
        'subtotal' => $subtotal,
        'itemCount' => count($_SESSION['cart'])
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Item tidak ditemukan di keranjang'
    ]);
}

exit();
