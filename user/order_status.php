<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

$id_user = (int)$_SESSION['user']['id_user'];
$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pesanan <= 0) { header('Location: index.php'); exit(); }

$stmt = mysqli_prepare($conn, 'SELECT p.*, u.nama as nama_pelanggan FROM pesanan p LEFT JOIN users u ON p.id_user = u.id_user WHERE p.id_pesanan = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id_pesanan);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($res);

if (!$order || (int)$order['id_user'] !== $id_user) {
    header('Location: index.php');
    exit();
}

$items = mysqli_query($conn, "SELECT d.*, m.nama_menu, m.harga, m.deskripsi FROM detail_pesanan d JOIN menu m ON d.id_menu = m.id_menu WHERE d.id_pesanan = " . (int)$id_pesanan);

function badgeClass($status) {
    switch ($status) {
        case 'selesai': return 'success';
        case 'dikirim': return 'info';
        case 'diproses': return 'warning';
        case 'menunggu': default: return 'secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center" style="background:#ff6b35; color:#fff;">
                    <h5 class="mb-0">Status Pesanan</h5>
                    <a href="index.php" class="btn btn-sm btn-light">Kembali</a>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="text-muted small">ID Pesanan</div>
                            <div class="fw-semibold">#<?php echo str_pad($order['id_pesanan'], 6, '0', STR_PAD_LEFT); ?></div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">Tanggal</div>
                            <div class="fw-semibold"><?php echo date('d M Y H:i', strtotime($order['tanggal'])); ?></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <span class="badge bg-<?php echo badgeClass($order['status']); ?>">Status: <?php echo ucfirst($order['status']); ?></span>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>Menu</th>
                                    <th>Deskripsi</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $total = 0; while($row = mysqli_fetch_assoc($items)): $total += (float)$row['subtotal']; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nama_menu']); ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($row['deskripsi'] ?: 'Tidak ada deskripsi'); ?>
                                        </small>
                                    </td>
                                    <td class="text-center"><?php echo (int)$row['jumlah']; ?></td>
                                    <td class="text-end">Rp <?php echo number_format((float)$row['harga'], 0, ',', '.'); ?></td>
                                    <td class="text-end">Rp <?php echo number_format((float)$row['subtotal'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
                                    <th class="text-end">Rp <?php echo number_format((float)$order['total_harga'], 0, ',', '.'); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
