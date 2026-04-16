<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

$id_user = (int)$_SESSION['user']['id_user'];

// Pastikan tabel ada
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_pesanan INT DEFAULT NULL,
    type VARCHAR(50) DEFAULT 'info',
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Mark read action
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $nid = (int)$_GET['read'];
    $upd = mysqli_prepare($conn, 'UPDATE notifications SET is_read = 1 WHERE id = ? AND id_user = ?');
    mysqli_stmt_bind_param($upd, 'ii', $nid, $id_user);
    mysqli_stmt_execute($upd);
    header('Location: notifications.php');
    exit();
}

// Mark all read
if (isset($_GET['read_all'])) {
    $upd = mysqli_prepare($conn, 'UPDATE notifications SET is_read = 1 WHERE id_user = ?');
    mysqli_stmt_bind_param($upd, 'i', $id_user);
    mysqli_stmt_execute($upd);
    header('Location: notifications.php');
    exit();
}

$notifs = mysqli_prepare($conn, 'SELECT * FROM notifications WHERE id_user = ? ORDER BY created_at DESC, id DESC');
mysqli_stmt_bind_param($notifs, 'i', $id_user);
mysqli_stmt_execute($notifs);
$list = mysqli_stmt_get_result($notifs);

function badgeByType($type) {
    switch ($type) {
        case 'order_processing': return 'warning';
        case 'order_shipped': return 'info';
        case 'order_completed': return 'success';
        case 'payment_confirmed': return 'primary';
        default: return 'secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Notifikasi</h4>
        <div>
            <a href="index.php" class="btn btn-sm btn-outline-secondary me-2">Kembali</a>
            <a href="history.php" class="btn btn-sm btn-outline-primary me-2">Riwayat Pesanan</a>
            <a href="?read_all=1" class="btn btn-sm btn-outline-secondary">Tandai semua sudah dibaca</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="list-group list-group-flush">
            <?php if ($list && mysqli_num_rows($list) > 0): ?>
                <?php while($n = mysqli_fetch_assoc($list)): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-start <?php echo $n['is_read'] ? '' : 'bg-light'; ?>">
                        <div class="ms-2 me-auto">
                            <div class="fw-semibold">
                                <span class="badge bg-<?php echo badgeByType($n['type']); ?> me-2 text-uppercase"><?php echo htmlspecialchars($n['type']); ?></span>
                                <?php if ($n['id_pesanan']): ?>
                                    <a href="order_status.php?id=<?php echo (int)$n['id_pesanan']; ?>" class="text-decoration-none">#<?php echo str_pad($n['id_pesanan'], 6, '0', STR_PAD_LEFT); ?></a>
                                <?php endif; ?>
                            </div>
                            <div><?php echo nl2br(htmlspecialchars($n['message'])); ?></div>
                            <small class="text-muted"><?php echo date('d M Y H:i', strtotime($n['created_at'])); ?></small>
                        </div>
                        <div class="text-nowrap">
                            <?php if (!$n['is_read']): ?>
                                <a href="?read=<?php echo (int)$n['id']; ?>" class="btn btn-sm btn-outline-secondary">Tandai dibaca</a>
                            <?php else: ?>
                                <span class="badge bg-secondary">Sudah dibaca</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="list-group-item text-center py-4 text-muted">Belum ada notifikasi.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
