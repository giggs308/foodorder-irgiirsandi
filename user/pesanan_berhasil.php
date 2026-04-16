<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

$id_user = (int)$_SESSION['user']['id_user'];
$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pesanan <= 0) {
    header('Location: index.php');
    exit();
}

$stmt = mysqli_prepare($conn, 'SELECT p.*, u.nama as nama_pelanggan FROM pesanan p LEFT JOIN users u ON p.id_user = u.id_user WHERE p.id_pesanan = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id_pesanan);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($res);

if (!$order || (int)$order['id_user'] !== $id_user) {
    header('Location: index.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body p-5 text-center">
                        <div class="mb-3">
                            <span class="badge bg-success px-3 py-2">Konfirmasi Pembayaran Terkirim</span>
                        </div>
                        <h3 class="mb-1">Terima kasih, <?php echo htmlspecialchars($order['nama_pelanggan']); ?>! 🎉</h3>
                        <p class="text-muted mb-4">Pembayaran kamu sudah dikonfirmasi. Pesanan <strong>menunggu persetujuan admin</strong>. Kamu akan mendapatkan notifikasi saat pesanan disetujui/diproses.</p>

                        <div class="row text-start g-3 mb-4">
                            <div class="col-6">
                                <div class="p-3 bg-light border rounded">
                                    <div class="text-muted small">ID Pesanan</div>
                                    <div class="fw-semibold">#<?php echo str_pad($order['id_pesanan'], 6, '0', STR_PAD_LEFT); ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light border rounded">
                                    <div class="text-muted small">Tanggal</div>
                                    <div class="fw-semibold"><?php echo date('d M Y H:i', strtotime($order['tanggal'])); ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light border rounded">
                                    <div class="text-muted small">Status</div>
                                    <div class="fw-semibold text-primary text-capitalize"><?php echo htmlspecialchars($order['status']); ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light border rounded">
                                    <div class="text-muted small">Total</div>
                                    <div class="fw-semibold">Rp <?php echo number_format((float)$order['total_harga'], 0, ',', '.'); ?></div>
                                </div>
                            </div>
                        </div>

                        <a href="index.php" class="btn btn-outline-primary">Kembali ke Menu</a>
                        <a href="order_status.php?id=<?php echo (int)$order['id_pesanan']; ?>" class="btn btn-primary" style="background:#ff6b35; border-color:#ff6b35;">Lihat Status Pesanan</a>
                        <div class="mt-3">
                            <a href="history.php" class="btn btn-sm btn-outline-secondary me-2">Riwayat Pesanan</a>
                            <a href="notifications.php" class="btn btn-sm btn-outline-secondary">Notifikasi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
