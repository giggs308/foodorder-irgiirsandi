<?php
session_start();
include '../includes/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle user status update
if (isset($_POST['update_status'])) {
    $id_user = $_POST['id_user'];
    $status = $_POST['status'];
    
    $query = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE id_user = ?");
    mysqli_stmt_bind_param($query, 'si', $status, $id_user);
    
    if (mysqli_stmt_execute($query)) {
        $_SESSION['message'] = 'Status pengguna berhasil diperbarui';
    } else {
        $_SESSION['error'] = 'Gagal memperbarui status pengguna';
    }
    header('Location: users.php');
    exit();
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $id_user = $_POST['id_user'];
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Delete related records first
        mysqli_query($conn, "DELETE FROM pesanan WHERE id_user = $id_user");
        
        // Delete the user
        $query = mysqli_prepare($conn, "DELETE FROM users WHERE id_user = ?");
        mysqli_stmt_bind_param($query, 'i', $id_user);
        
        if (mysqli_stmt_execute($query)) {
            mysqli_commit($conn);
            $_SESSION['message'] = 'Pengguna berhasil dihapus';
        } else {
            throw new Exception('Gagal menghapus pengguna');
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: users.php');
    exit();
}

// Get all users except the current admin
$query = "SELECT * FROM users WHERE id_user != ? ORDER BY role, nama";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user']['id_user']);
mysqli_stmt_execute($stmt);
$users = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin FoodOrder</title>
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
                    <h4 class="mb-0">Kelola Pengguna</h4>
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
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = mysqli_fetch_assoc($users)): ?>
                                        <tr>
                                            <td>#<?= str_pad($user['id_user'], 4, '0', STR_PAD_LEFT) ?></td>
                                            <td><?= htmlspecialchars($user['nama']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $user['role'] == 'admin' ? 'primary' : 'success' ?>">
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="post" class="d-flex">
                                                    <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                                                    <select name="status" class="form-select form-select-sm me-2" onchange="this.form.submit()" <?= $user['role'] == 'admin' ? 'disabled' : '' ?>>
                                                        <option value="aktif" <?= ($user['status'] ?? 'aktif') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                                        <option value="nonaktif" <?= ($user['status'] ?? 'aktif') == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </td>
                                            <td>
                                                <?php if ($user['role'] != 'admin'): ?>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                                        <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger">
                                                            <i class='bx bx-trash'></i> Hapus
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    <?php if (mysqli_num_rows($users) == 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">Tidak ada pengguna</td>
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
