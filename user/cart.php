<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    unset($_SESSION['cart'][$id]);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - FoodOrder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .img-thumbnail { object-fit: cover; height: 70px; width: 70px; }
        .table { background-color: white; border-radius: 10px; overflow: hidden; }
        .table thead th { background-color: #ffc107; border: none; }
        .btn-success { background-color: #28a745; border: none; padding: 8px 20px; }
        .btn-success:hover { background-color: #218838; }
    </style>
</head>
<body style="background-color:#fff7f2; font-family:Poppins, sans-serif;">

<div class="container mt-5">
    <h3 class="mb-4">🛒 Keranjang Belanja</h3>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-warning">Keranjang kamu masih kosong.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-warning">
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Menu</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $grand_total = 0;
                foreach ($_SESSION['cart'] as $item): 
                    $harga = isset($item['harga']) ? (float)$item['harga'] : 0;
                    $qty = isset($item['qty']) ? (int)$item['qty'] : 1;
                    $total = $harga * $qty;
                    $grand_total += $total;
                ?>
                    <tr>
                        <td>
                            <?php
                            $image_path = '../assets/img/default-food.jpg';
                            $fallback_image = 'https://via.placeholder.com/70?text=Food';
                            if (!empty($item['gambar'])) {
                                $relative_path = '../assets/img/' . $item['gambar'];
                                $absolute_path = dirname(__FILE__, 1) . '/../assets/img/' . $item['gambar'];

                                if (file_exists($absolute_path)) {
                                    $image_path = $relative_path;
                                } else {
                                    $filename = pathinfo($item['gambar'], PATHINFO_FILENAME);
                                    $extensions = ['jpg', 'jpeg', 'png', 'gif'];
                                    foreach ($extensions as $ext) {
                                        $alt_relative = '../assets/img/' . $filename . '.' . $ext;
                                        $alt_absolute = dirname(__FILE__, 1) . '/../assets/img/' . $filename . '.' . $ext;
                                        if (file_exists($alt_absolute)) {
                                            $image_path = $alt_relative;
                                            break;
                                        }
                                    }
                                }
                            }

                            if (!file_exists(dirname(__FILE__, 1) . '/../assets/img/default-food.jpg')) {
                                $image_path = $fallback_image;
                            }
                            ?>
                            <img src="<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($item['nama_menu']) ?>" width="70" class="img-thumbnail">
                        </td>
                        <td><?= htmlspecialchars($item['nama_menu']) ?></td>
                        <td>Rp <?= number_format((float)$item['harga'], 0, ',', '.') ?></td>
                        <td>
                            <form method="post" action="update_cart.php" class="d-inline">
                                <input type="hidden" name="id_menu" value="<?= $item['id_menu'] ?>">
                                <input type="number" name="qty" value="<?= $item['qty'] ?>" min="1" class="form-control form-control-sm d-inline-block" style="width: 70px;">
                                <button type="submit" class="btn btn-sm btn-outline-primary">✓</button>
                            </form>
                        </td>
                        <td>Rp <?= number_format((float)$total, 0, ',', '.') ?></td>
                        <td><a href="cart.php?hapus=<?= $item['id_menu'] ?>" class="btn btn-danger btn-sm">Hapus</a></td>
                    </tr>
                <?php endforeach; ?>
                    <tr class="table-light">
                        <td colspan="4" class="text-end fw-bold">Total Keseluruhan</td>
                        <td colspan="2" class="fw-bold text-danger">Rp <?= number_format((float)$grand_total, 0, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="text-end">
            <a href="index.php" class="btn btn-secondary">Kembali</a>
            <?php if (!empty($_SESSION['cart'])): ?>
                <a href="checkout.php" class="btn btn-success">Lanjut ke Pembayaran</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
