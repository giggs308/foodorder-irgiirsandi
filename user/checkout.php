<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Jika keranjang kosong, arahkan kembali ke halaman utama
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>alert('Keranjang kamu masih kosong!'); window.location='index.php';</script>";
    exit;
}

$id_user = $_SESSION['user']['id_user'];
$total = 0;

// Proses checkout
if (isset($_POST['checkout'])) {
    mysqli_begin_transaction($conn);

    try {
        // Hitung total harga pesanan
        foreach ($_SESSION['cart'] as $item) {
            if (!is_array($item) || !isset($item['id_menu'])) continue;
            
            $id_menu = mysqli_real_escape_string($conn, $item['id_menu']);
            $qty = isset($item['qty']) ? (int)$item['qty'] : 1;
            
            $res = mysqli_query($conn, "SELECT harga FROM menu WHERE id_menu='$id_menu'");
            $menu = mysqli_fetch_assoc($res);
            
            if ($menu) {
                $harga = (float)$menu['harga'];
                $total += $harga * $qty;
            }
        }

        // Simpan ke tabel pesanan (gunakan kolom yang dipakai admin: tanggal, status)
        $query_pesanan = "INSERT INTO pesanan (id_user, total_harga, tanggal, status) 
                          VALUES ('$id_user', '$total', NOW(), 'menunggu')";
        
        if (mysqli_query($conn, $query_pesanan)) {
            $id_pesanan = mysqli_insert_id($conn);

            // Simpan detail pesanan
            foreach ($_SESSION['cart'] as $item) {
                if (!is_array($item) || !isset($item['id_menu'])) continue;
                
                $id_menu = mysqli_real_escape_string($conn, $item['id_menu']);
                $qty = isset($item['qty']) ? (int)$item['qty'] : 1;
                
                $res = mysqli_query($conn, "SELECT harga FROM menu WHERE id_menu='$id_menu'");
                $menu = mysqli_fetch_assoc($res);
                
                if ($menu) {
                    $harga = (float)$menu['harga'];
                    $subtotal = $harga * $qty;
                    
                    $query_detail = "INSERT INTO detail_pesanan (id_pesanan, id_menu, jumlah, subtotal)
                                   VALUES ('$id_pesanan', '$id_menu', '$qty', '$subtotal')";
                    mysqli_query($conn, $query_detail);
                }
            }

            mysqli_commit($conn);
            
            // Kosongkan keranjang
            $_SESSION['cart'] = [];
            
            // Redirect ke halaman pembayaran
            header("Location: payment.php?id=$id_pesanan");
            exit;
        } else {
            throw new Exception("Gagal menyimpan pesanan");
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Terjadi kesalahan saat memproses pesanan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - FoodOrder</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fffaf6;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #ff6b35;
            color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 22px;
        }

        header a {
            background: white;
            color: #ff6b35;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }

        main {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        table th {
            background-color: #fff5ef;
            color: #ff6b35;
        }

        .total {
            text-align: right;
            font-weight: bold;
            font-size: 18px;
            margin-top: 10px;
        }

        .checkout-btn {
            display: block;
            width: 100%;
            background-color: #ff6b35;
            color: white;
            border: none;
            padding: 14px;
            font-size: 16px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .checkout-btn:hover {
            background-color: #ff5722;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 10px;
            color: #ff6b35;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<header>
    <h1>Checkout Pesanan</h1>
    <a href="index.php">Kembali ke Menu</a>
</header>

<main>
    <h2>Ringkasan Pesanan Kamu 🍔</h2>

    <table>
        <tr>
            <th>Nama Menu</th>
            <th>Jumlah</th>
            <th>Harga Satuan</th>
            <th>Subtotal</th>
        </tr>
        <?php
        $grand_total = 0;
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item):
                if (!is_array($item) || !isset($item['id_menu'])) continue;
                
                $id_menu = $item['id_menu'];
                $qty = isset($item['qty']) ? (int)$item['qty'] : 1;
                
                $res = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu='$id_menu'");
                $menu = mysqli_fetch_assoc($res);
                
                if (!$menu) continue;
                
                $harga = isset($menu['harga']) ? (float)$menu['harga'] : 0;
                $subtotal = $harga * $qty;
                $grand_total += $subtotal;
                
                // Handle gambar
                $gambar_src = '../assets/img/default-food.jpg';
                if (!empty($menu['gambar'])) {
                    $gambar_file = '../assets/img/' . $menu['gambar'];
                    if (file_exists($gambar_file)) {
                        $gambar_src = $gambar_file;
                    }
                }
        ?>
        <tr>
            <td>
                <img src="<?= $gambar_src ?>" alt="<?= htmlspecialchars($menu['nama_menu']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 10px;">
                <?= htmlspecialchars($menu['nama_menu']); ?>
            </td>
            <td><?= $qty; ?></td>
            <td>Rp <?= number_format($harga, 0, ',', '.'); ?></td>
            <td>Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
        </tr>
        <?php 
            endforeach;
        } // Close the if statement for checking cart
        ?>
    </table>

    <?php if ($grand_total > 0): ?>
    <p class="total">Total Pembayaran: Rp <?= number_format($grand_total, 0, ',', '.'); ?></p>

    <form method="POST">
        <button type="submit" name="checkout" class="checkout-btn">Selesaikan Pembayaran</button>
    </form>
    <?php else: ?>
    <p class="text-center text-muted mt-4">Keranjang belanja Anda kosong.</p>
    <a href="index.php" class="btn btn-primary">Kembali ke Menu</a>
    <?php endif; ?>
</main>

</body>
</html>

