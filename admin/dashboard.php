<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

include '../includes/config.php';

// Function to safely get count
function getCount($conn, $table, $where = '') {
    $query = "SELECT COUNT(*) as count FROM `$table`";
    if ($where) $query .= " WHERE $where";
    $result = mysqli_query($conn, $query);
    return $result ? mysqli_fetch_assoc($result)['count'] : 0;
}

// Get counts for dashboard
$menu_count = getCount($conn, 'menu');
$order_count = getCount($conn, 'pesanan');
$pending_orders = getCount($conn, 'pesanan', "status = 'menunggu'");
$user_count = getCount($conn, 'users', "role = 'user'");

// Get recent orders with user information
$recent_orders = mysqli_query($conn, "
    SELECT p.*, u.nama as nama_pelanggan 
    FROM pesanan p 
    LEFT JOIN users u ON p.id_user = u.id_user 
    ORDER BY p.tanggal DESC 
    LIMIT 5
");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin FoodOrder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        .page-header { background:#fff; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.08); padding:15px 20px; }
        .btn-outline-primary{ border-color: var(--primary-color); color: var(--primary-color);} 
        .btn-outline-primary:hover{ background: var(--primary-color); color:#fff; }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include 'includes/navbar.php'; ?>

            <!-- Main Content -->
    <!-- Page header with breadcrumb -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Dashboard
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="pesanan.php?status=menunggu" class="btn btn-primary d-none d-sm-inline-block">
                            <i class='bx bx-plus me-1'></i> Pesanan Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

            <!-- Page body -->
            <div class="page-body">
                <div class="container-xl">
            <!-- Stats Cards -->
            <div class="row row-deck row-cards">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Menu</div>
                                <div class="ms-auto lh-1">
                                    <i class='bx bx-food-menu fs-2 text-primary'></i>
                                </div>
                            </div>
                            <div class="h1 mb-3"><?= $menu_count ?></div>
                            <div class="d-flex mb-2">
                                <div>Total menu tersedia</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Pesanan</div>
                                <div class="ms-auto lh-1">
                                    <i class='bx bx-receipt fs-2 text-success'></i>
                                </div>
                            </div>
                            <div class="h1 mb-3"><?= $order_count ?></div>
                            <div class="d-flex mb-2">
                                <div>Total pesanan</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Menunggu Konfirmasi</div>
                                <div class="ms-auto lh-1">
                                    <i class='bx bx-time fs-2 text-warning'></i>
                                </div>
                            </div>
                            <div class="h1 mb-3"><?= $pending_orders ?></div>
                            <div class="d-flex mb-2">
                                <div>Pesanan menunggu</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Pengguna</div>
                                <div class="ms-auto lh-1">
                                    <i class='bx bx-user fs-2 text-info'></i>
                                </div>
                            </div>
                            <div class="h1 mb-3"><?= $user_count ?></div>
                            <div class="d-flex mb-2">
                                <div>Pengguna terdaftar</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Pesanan Terbaru</h3>
                    <div class="ms-auto">
                        <a href="pesanan.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-striped">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th class="w-1"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                                <tr>
                                    <td>#<?= (isset($order['kode_pesanan']) && $order['kode_pesanan'] !== '') 
                                        ? htmlspecialchars($order['kode_pesanan']) 
                                        : str_pad($order['id_pesanan'], 6, '0', STR_PAD_LEFT) ?>
                                    </td>
                                    <td><?= htmlspecialchars($order['nama_pelanggan']) ?></td>
                                    <td>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="badge bg-<?= getStatusBadgeClass($order['status']) ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted"><?= date('d M Y H:i', strtotime($order['tanggal'])) ?></td>
                                    <td class="text-end">
                                        <a href="order_detail.php?id=<?= $order['id_pesanan'] ?>" class="btn btn-sm btn-icon" aria-label="Lihat detail">
                                            <i class='bx bx-chevron-right'></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'menunggu':
            return 'warning';
        case 'diproses':
            return 'info';
        case 'selesai':
            return 'success';
        case 'dibatalkan':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>