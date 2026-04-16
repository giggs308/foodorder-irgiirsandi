<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

$id_user = (int)$_SESSION['user']['id_user'];

// Ambil semua pesanan milik user ini
$orders = mysqli_query($conn, "SELECT * FROM pesanan WHERE id_user = " . $id_user . " ORDER BY tanggal DESC");

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
    <title>Riwayat Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Riwayat Pesanan</h4>
        <div>
            <a href="index.php" class="btn btn-sm btn-outline-primary">Kembali ke Menu</a>
            <a href="notifications.php" class="btn btn-sm btn-outline-secondary">Notifikasi</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
                            <?php while ($o = mysqli_fetch_assoc($orders)): ?>
                                <tr>
                                    <td>#<?php echo str_pad($o['id_pesanan'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($o['tanggal'])); ?></td>
                                    <td><span class="badge bg-<?php echo badgeClass($o['status']); ?>"><?php echo ucfirst($o['status']); ?></span></td>
                                    <td class="text-end">Rp <?php echo number_format((float)$o['total_harga'], 0, ',', '.'); ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary" href="order_status.php?id=<?php echo (int)$o['id_pesanan']; ?>">Detail</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat pesanan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
