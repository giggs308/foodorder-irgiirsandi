<?php
session_start();
include '../includes/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Canonical page is pesanan.php. Redirect permanently to avoid duplication.
header('Location: pesanan.php', true, 301);
exit();

// Update order status
if (isset($_POST['update_status'])) {
    $id_pesanan = $_POST['id_pesanan'];
    $status = $_POST['status'];
    
    $query = mysqli_prepare($conn, "UPDATE pesanan SET status = ? WHERE id_pesanan = ?");
    mysqli_stmt_bind_param($query, 'si', $status, $id_pesanan);
    
    if (mysqli_stmt_execute($query)) {
        $_SESSION['message'] = 'Status pesanan berhasil diperbarui';

        // Pastikan tabel notifications ada
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_user INT NOT NULL,
            id_pesanan INT DEFAULT NULL,
            type VARCHAR(50) DEFAULT 'info',
            message TEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Ambil id_user dari pesanan
        $stmtUser = mysqli_prepare($conn, 'SELECT id_user FROM pesanan WHERE id_pesanan = ? LIMIT 1');
        mysqli_stmt_bind_param($stmtUser, 'i', $id_pesanan);
        mysqli_stmt_execute($stmtUser);
        $resUser = mysqli_stmt_get_result($stmtUser);
        if ($rowUser = mysqli_fetch_assoc($resUser)) {
            $uid = (int)$rowUser['id_user'];
            // Susun pesan berdasarkan status
            switch ($status) {
                case 'diproses':
                    $type = 'order_processing';
                    $msg = 'Pembayaran Anda disetujui. Pesanan sedang diproses.';
                    break;
                case 'dikirim':
                    $type = 'order_shipped';
                    $msg = 'Pesanan Anda sedang dalam perjalanan.';
                    break;
                case 'selesai':
                    $type = 'order_completed';
                    $msg = 'Pesanan Anda selesai. Terima kasih telah memesan!';
                    break;
                default:
                    $type = 'order_update';
                    $msg = 'Status pesanan diperbarui menjadi ' . $status . '.';
            }
            $stmtNotif = mysqli_prepare($conn, 'INSERT INTO notifications (id_user, id_pesanan, type, message) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmtNotif, 'iiss', $uid, $id_pesanan, $type, $msg);
            mysqli_stmt_execute($stmtNotif);
        }
    } else {
        $_SESSION['error'] = 'Gagal memperbarui status pesanan';
    }
    header('Location: orders.php');
    exit();
}

// Get all orders with user information
$query = "SELECT p.*, u.nama as nama_pelanggan, u.email as email_pelanggan 
          FROM pesanan p 
          LEFT JOIN users u ON p.id_user = u.id_user 
          ORDER BY p.tanggal DESC";
$orders = mysqli_query($conn, $query);

// Function to get order items
function getOrderItems($conn, $order_id) {
    $query = "SELECT m.nama_menu, m.harga, d.jumlah, (m.harga * d.jumlah) as subtotal 
              FROM detail_pesanan d 
              JOIN menu m ON d.id_menu = m.id_menu 
              WHERE d.id_pesanan = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin FoodOrder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/navbar.php'; ?>
            
            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Kelola Pesanan</h4>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID Pesanan</th>
                                        <th>Pelanggan</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                                        <tr>
                                            <td>#<?= str_pad($order['id_pesanan'], 6, '0', STR_PAD_LEFT) ?></td>
                                            <td>
                                                <div><?= htmlspecialchars($order['nama_pelanggan']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($order['email_pelanggan']) ?></small>
                                            </td>
                                            <td><?= date('d M Y H:i', strtotime($order['tanggal'])) ?></td>
                                            <td>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                                            <td>
                                                <form method="post" class="d-flex">
                                                    <input type="hidden" name="id_pesanan" value="<?= $order['id_pesanan'] ?>">
                                                    <select name="status" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                                                        <option value="menunggu" <?= $order['status'] == 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                                        <option value="diproses" <?= $order['status'] == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                                        <option value="dikirim" <?= $order['status'] == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                                        <option value="selesai" <?= $order['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#orderDetail<?= $order['id_pesanan'] ?>">
                                                    <i class='bx bx-detail'></i> Detail
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Order Detail Modal -->
                                        <div class="modal fade" id="orderDetail<?= $order['id_pesanan'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detail Pesanan #<?= str_pad($order['id_pesanan'], 6, '0', STR_PAD_LEFT) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-4">
                                                            <div class="col-md-6">
                                                                <h6>Informasi Pelanggan</h6>
                                                                <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($order['nama_pelanggan']) ?></p>
                                                                <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($order['email_pelanggan']) ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6>Informasi Pesanan</h6>
                                                                <p class="mb-1"><strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($order['tanggal'])) ?></p>
                                                                <p class="mb-1"><strong>Status:</strong> 
                                                                    <span class="badge bg-<?= 
                                                                        $order['status'] == 'selesai' ? 'success' : 
                                                                        ($order['status'] == 'dikirim' ? 'info' : 
                                                                        ($order['status'] == 'diproses' ? 'warning' : 'secondary')) 
                                                                    ?>">
                                                                        <?= ucfirst($order['status']) ?>
                                                                    </span>
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <h6>Daftar Pesanan</h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Menu</th>
                                                                        <th class="text-end">Harga</th>
                                                                        <th class="text-center">Jumlah</th>
                                                                        <th class="text-end">Subtotal</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php 
                                                                    $order_items = getOrderItems($conn, $order['id_pesanan']);
                                                                    $total = 0;
                                                                    while ($item = mysqli_fetch_assoc($order_items)): 
                                                                        $subtotal = $item['harga'] * $item['jumlah'];
                                                                        $total += $subtotal;
                                                                    ?>
                                                                        <tr>
                                                                            <td><?= htmlspecialchars($item['nama_menu']) ?></td>
                                                                            <td class="text-end">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                                                            <td class="text-center"><?= $item['jumlah'] ?></td>
                                                                            <td class="text-end">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                                                        </tr>
                                                                    <?php endwhile; ?>
                                                                    <tr>
                                                                        <th colspan="3" class="text-end">Total</th>
                                                                        <th class="text-end">Rp <?= number_format($total, 0, ',', '.') ?></th>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                    <?php if (mysqli_num_rows($orders) == 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">Belum ada pesanan</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
