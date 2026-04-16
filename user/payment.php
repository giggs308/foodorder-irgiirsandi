<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

$id_user = $_SESSION['user']['id_user'];
$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pesanan <= 0) {
    header('Location: index.php');
    exit();
}

// Ambil data pesanan milik user ini
$order_q = mysqli_prepare($conn, 'SELECT id_pesanan, id_user, total_harga, tanggal, status FROM pesanan WHERE id_pesanan = ? LIMIT 1');
mysqli_stmt_bind_param($order_q, 'i', $id_pesanan);
mysqli_stmt_execute($order_q);
$order_res = mysqli_stmt_get_result($order_q);
$order = mysqli_fetch_assoc($order_res);

if (!$order || (int)$order['id_user'] !== (int)$id_user) {
    header('Location: index.php');
    exit();
}

// Ambil item pesanan
$items_q = mysqli_prepare($conn, 'SELECT d.id_menu, d.jumlah, d.subtotal, m.nama_menu, m.harga FROM detail_pesanan d JOIN menu m ON d.id_menu = m.id_menu WHERE d.id_pesanan = ?');
mysqli_stmt_bind_param($items_q, 'i', $id_pesanan);
mysqli_stmt_execute($items_q);
$items_res = mysqli_stmt_get_result($items_q);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pesanan #<?php echo htmlspecialchars($id_pesanan); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background:#ff6b35; color:#fff;">
                        <h5 class="mb-0">Pembayaran Pesanan</h5>
                        <a href="index.php" class="btn btn-sm btn-light">Kembali</a>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-muted">ID Pesanan</div>
                                    <div class="fw-semibold">#<?php echo str_pad($order['id_pesanan'], 6, '0', STR_PAD_LEFT); ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted">Tanggal</div>
                                    <div class="fw-semibold"><?php echo date('d M Y H:i', strtotime($order['tanggal'])); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Menu</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $computed_total = 0; ?>
                                    <?php while ($row = mysqli_fetch_assoc($items_res)): ?>
                                        <?php $computed_total += (float)$row['subtotal']; ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nama_menu']); ?></td>
                                            <td class="text-center"><?php echo (int)$row['jumlah']; ?></td>
                                            <td class="text-end">Rp <?php echo number_format((float)$row['harga'], 0, ',', '.'); ?></td>
                                            <td class="text-end">Rp <?php echo number_format((float)$row['subtotal'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th class="text-end">Rp <?php echo number_format((float)$order['total_harga'], 0, ',', '.'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <form action="confirm_payment.php" method="post" class="mt-4">
                            <input type="hidden" name="id_pesanan" value="<?php echo (int)$order['id_pesanan']; ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Metode Pembayaran</label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="form-check border rounded p-3 h-100">
                                            <input class="form-check-input" type="radio" name="metode" id="pay_qris" value="qris" checked>
                                            <label class="form-check-label" for="pay_qris">
                                                QRIS
                                            </label>
                                            <div class="small text-muted mt-2">Scan QR saat konfirmasi</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check border rounded p-3 h-100">
                                            <input class="form-check-input" type="radio" name="metode" id="pay_transfer" value="transfer">
                                            <label class="form-check-label" for="pay_transfer">
                                                Transfer Bank
                                            </label>
                                            <div class="small text-muted mt-2">BCA - 123456789 a/n FoodOrder</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check border rounded p-3 h-100">
                                            <input class="form-check-input" type="radio" name="metode" id="pay_cash" value="tunai">
                                            <label class="form-check-label" for="pay_cash">
                                                Tunai
                                            </label>
                                            <div class="small text-muted mt-2">Bayar saat pesanan diterima</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <div>
                                    Setelah Anda menekan tombol "Saya sudah bayar", status pesanan akan menjadi <strong>Diproses</strong> dan admin akan segera menyiapkan pesanan Anda.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100" style="background:#ff6b35; border-color:#ff6b35;">
                                Saya sudah bayar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
