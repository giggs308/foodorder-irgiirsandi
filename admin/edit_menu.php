<?php
session_start();
include '../includes/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Pastikan ada ID menu
if (!isset($_GET['edit'])) {
    header("Location: menu.php");
    exit;
}

$id = $_GET['edit'];
$query = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu='$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Menu tidak ditemukan!');window.location='menu.php';</script>";
    exit;
}

// Update data menu
if (isset($_POST['update'])) {
    $nama = $_POST['nama_menu'];
    $harga = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];

    // Jika ganti gambar
    $gambar = $data['gambar'];
    if ($_FILES['gambar']['name'] != '') {
        // Pastikan direktori img ada
        $upload_dir = '../assets/img/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate nama file unik
        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar_baru = uniqid() . '.' . strtolower($file_extension);
        
        // Pindahkan file ke direktori img
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $gambar_baru)) {
            // Hapus gambar lama jika ada
            if ($gambar && file_exists($upload_dir . $gambar)) {
                unlink($upload_dir . $gambar);
            }
            $gambar = $gambar_baru;
        } else {
            echo "<script>alert('Gagal mengupload gambar');</script>";
        }
    }

    $update = mysqli_query($conn, "UPDATE menu 
                                   SET nama_menu='$nama', harga='$harga', deskripsi='$deskripsi', gambar='$gambar' 
                                   WHERE id_menu='$id'");

    if ($update) {
        echo "<script>alert('Menu berhasil diperbarui!');window.location='menu.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui menu');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="text-center mb-4">✏️ Edit Menu Makanan</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Nama Menu</label>
                    <input type="text" name="nama_menu" value="<?= htmlspecialchars($data['nama_menu']) ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" value="<?= $data['harga'] ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label>Gambar Saat Ini:</label><br>
                    <?php if ($data['gambar']) : 
                        $gambar_path = '../assets/img/' . htmlspecialchars($data['gambar']);
                        $gambar_src = file_exists($gambar_path) ? $gambar_path : '../assets/img/burger.jpeg';
                    ?>
                        <img src="<?= $gambar_src ?>" width="120" class="rounded mb-2">
                    <?php else : ?>
                        <span class="text-muted">Tidak ada gambar</span>
                    <?php endif; ?>
                    <input type="file" name="gambar" class="form-control mt-2">
                </div>

                <button type="submit" name="update" class="btn btn-primary">💾 Simpan Perubahan</button>
                <a href="menu.php" class="btn btn-secondary">⬅ Kembali</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
