<?php
session_start();
include '../includes/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Tambah menu
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_menu'];
    $harga = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];
    $kategori = $_POST['kategori'] ?? 'Makanan';
    $stok = (int)($_POST['stok'] ?? 0);

    // Upload gambar
    $gambar = '';
    if ($_FILES['gambar']['name'] != '') {
        // Pastikan direktori img ada
        $upload_dir = '../assets/img/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate nama file unik
        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = uniqid() . '.' . strtolower($file_extension);
        
        // Pindahkan file ke direktori img
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $gambar)) {
            // File berhasil diupload
        } else {
            echo "<script>alert('Gagal mengupload gambar');</script>";
            $gambar = '';
        }
    }

    $query = mysqli_query($conn, "INSERT INTO menu (nama_menu, harga, deskripsi, gambar, kategori, stok) 
                                  VALUES ('$nama', '$harga', '$deskripsi', '$gambar', '$kategori', '$stok')");
    if ($query) {
        echo "<script>alert('Menu berhasil ditambahkan!');window.location='menu.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah menu');</script>";
    }
}

// Hapus menu
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $query = mysqli_query($conn, "DELETE FROM menu WHERE id_menu='$id'");
    echo "<script>alert('Menu dihapus');window.location='menu.php';</script>";
}

// Ambil semua data menu
$menu = mysqli_query($conn, "SELECT * FROM menu ORDER BY id_menu DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff6b35;
            --primary-hover: #e65a2b;
            --sidebar-width: 250px;
            --header-bg: #f8f9fa;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background: #fff;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 15px;
            background: var(--primary-color);
            color: white;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .sidebar-brand h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .sidebar-menu {
            padding: 0 10px;
            list-style: none;
        }
        
        .sidebar-item {
            margin-bottom: 5px;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #444;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.2s;
            font-size: 0.95rem;
        }
        
        .sidebar-link:hover, 
        .sidebar-link.active {
            background: rgba(255, 107, 53, 0.1);
            color: var(--primary-color);
        }
        
        .sidebar-link i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        
        .sidebar-link.logout {
            color: #dc3545;
        }
        
        .sidebar-link.logout:hover {
            background: rgba(220, 53, 69, 0.1);
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
        }
        
        .page-header {
            background: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            margin: 0;
            color: #333;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            color: var(--primary-color);
            margin-right: 10px;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
        }
        
        .card-header i {
            margin-right: 8px;
            color: var(--primary-color);
        }
        
        .form-control, .form-select {
            border-radius: 5px;
            padding: 8px 12px;
            border: 1px solid #ddd;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .btn-outline-secondary {
            border-radius: 5px;
            padding: 8px 20px;
        }
        
        .table {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            padding: 12px 15px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
            border-color: #eee;
        }
        
        .table img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .action-buttons .btn {
            padding: 5px 10px;
            font-size: 0.85rem;
            margin: 0 2px;
        }
        
        /* Image Upload */
        .image-upload-container {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f9f9f9;
            margin-bottom: 15px;
        }
        
        .image-upload-container:hover {
            border-color: var(--primary-color);
            background: #f5f5f5;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 5px;
            margin-bottom: 10px;
            display: none;
        }
        
        .upload-icon {
            font-size: 2.5rem;
            color: #aaa;
            margin-bottom: 10px;
        }
        
        .upload-text {
            color: #666;
            margin-bottom: 5px;
        }
        
        .upload-hint {
            font-size: 0.8rem;
            color: #999;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .main-content.active {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
            
            .table-responsive {
                overflow-x: auto;
            }
        }
        
        @media (max-width: 576px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .btn-group {
                width: 100%;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 5px;
            }
        }
        :root {
            --primary-color: #ff6b35;
            --primary-hover: #e65a2b;
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        body {
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #fff;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar-brand {
            padding: 15px;
            background: var(--primary-color);
            color: white;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .sidebar-brand h3 {
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar-menu {
            padding: 0 15px;
        }
        
        .sidebar-item {
            margin-bottom: 5px;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .sidebar-link:hover, 
        .sidebar-link.active {
            background: rgba(255, 107, 53, 0.1);
            color: var(--primary-color);
        }
        
        .sidebar-link i {
            margin-right: 10px;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }
        
        .sidebar-link.text-danger {
            color: #dc3545 !important;
        }
        
        .sidebar-link.text-danger:hover {
            background: rgba(220, 53, 69, 0.1);
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 53, 0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .table th {
            font-weight: 600;
            background-color: #f8f9fa;
        }
        
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Image Upload */
        #image-upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        #image-upload-area:hover {
            border-color: var(--primary-color);
        }
        
        #image-upload-area.has-image {
            border-style: solid;
        }
        
        #image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                left: calc(-1 * var(--sidebar-width));
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .main-content.active {
                margin-left: var(--sidebar-width);
            }
        }
        :root {
            --primary-color: #ff6b35;
            --primary-hover: #e65a2b;
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        body {
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #fff;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar-brand {
            padding: 20px;
            background: var(--primary-color);
            color: white;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .sidebar-brand h3 {
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-menu li {
            border-bottom: 1px solid #f1f1f1;
        }
        
        .sidebar-menu li a {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: #f8f9fa;
            color: var(--primary-color);
            padding-left: 25px;
        }
        
        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 53, 0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .table th {
            font-weight: 600;
            background-color: #f8f9fa;
        }
        
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                left: calc(-1 * var(--sidebar-width));
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.active {
                margin-left: var(--sidebar-width);
            }
        }
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <?php include 'includes/navbar.php'; ?>
        <div class="container-fluid py-4">
        <div class="page-header">
            <h1 class="page-title">
                <i class='bx bx-food-menu'></i>
                Kelola Menu Makanan
            </h1>
            <button class="btn btn-primary d-md-none" id="sidebarToggle">
                <i class='bx bx-menu'></i>
            </button>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3" style="border-bottom: 2px solid #ff6b35 !important;">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-plus-circle me-2"></i>Tambah Menu Baru
                </h5>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Menu</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-utensils"></i></span>
                                    <input type="text" name="nama_menu" class="form-control form-control-lg" 
                                           placeholder="Contoh: Nasi Goreng Spesial" required>
                                    <div class="invalid-feedback">
                                        Mohon isi nama menu
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Harga (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" name="harga" class="form-control form-control-lg" 
                                           placeholder="Contoh: 25000" min="0" required>
                                    <div class="invalid-feedback">
                                        Mohon isi harga yang valid
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="4" 
                                         placeholder="Deskripsi menu..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kategori</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-tag"></i></span>
                                    <select name="kategori" class="form-select form-control-lg" required>
                                        <option value="Makanan">Makanan</option>
                                        <option value="Minuman">Minuman</option>
                                        <option value="Dessert">Dessert</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Stok</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-boxes"></i></span>
                                    <input type="number" name="stok" class="form-control form-control-lg" 
                                           placeholder="Contoh: 50" min="0" value="0" required>
                                </div>
                                <small class="text-muted">Jumlah stok yang tersedia</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Gambar Menu</label>
                                <div class="border rounded p-3 text-center" style="border-style: dashed !important;" id="image-upload-area">
                                    <img id="image-preview" src="../assets/img/default-food.jpg" 
                                         class="img-fluid mb-2" style="max-height: 200px; border-radius: 8px;">
                                    <div class="mt-2">
                                        <input type="file" name="gambar" id="gambar" class="d-none" accept="image/*">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('gambar').click()">
                                            <i class="fas fa-cloud-upload-alt me-1"></i> Pilih Gambar
                                        </button>
                                        <div class="text-muted small mt-2">Format: JPG, PNG, GIF (Maks. 2MB)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="reset" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-undo me-1"></i> Reset
                        </button>
                        <button type="submit" name="tambah" class="btn btn-primary px-4">
                            <i class="fas fa-plus-circle me-2"></i> Tambah Menu
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">📋 Daftar Menu</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Gambar</th>
                                <th>Nama Menu</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                <?php 
                $no = 1;
                while ($row = mysqli_fetch_assoc($menu)) : ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center">
                        <?php if (!empty($row['gambar'])) : 
                            $gambar_path = '../assets/img/' . htmlspecialchars($row['gambar']);
                            $gambar_src = file_exists($gambar_path) ? $gambar_path : '../assets/img/burger.jpeg';
                        ?>
                            <img src="<?= $gambar_src ?>" width="60" class="img-thumbnail">
                        <?php else : ?>
                            <span class="text-muted">Tidak ada</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['nama_menu']) ?></td>
                    <td>
                        <span class="badge bg-info"><?= htmlspecialchars($row['kategori']) ?></span>
                    </td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td>
                        <?php 
                        $stok = (int)$row['stok'];
                        if ($stok <= 0) {
                            echo '<span class="badge bg-danger">Habis</span>';
                        } elseif ($stok <= 10) {
                            echo '<span class="badge bg-warning">' . $stok . '</span>';
                        } else {
                            echo '<span class="badge bg-success">' . $stok . '</span>';
                        }
                        ?>
                    </td>
                    <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                    <td class="text-center">
                        <a href="edit_menu.php?edit=<?= $row['id_menu'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="menu.php?hapus=<?= $row['id_menu'] ?>" 
                           onclick="return confirm('Yakin hapus menu ini?')" 
                           class="btn btn-danger btn-sm">Hapus</a>
                    </td>

                </tr>
                <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <a href="dashboard.php" class="btn btn-secondary mt-3">⬅ Kembali ke Dashboard</a>
        </div>
    </div>
<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar Toggle for Mobile
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        // Initialize sidebar state
        if (window.innerWidth <= 992) {
            sidebar.classList.remove('active');
            mainContent.classList.remove('active');
        } else {
            sidebar.classList.add('active');
            mainContent.classList.add('active');
        }
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('active');
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                    mainContent.classList.remove('active');
                }
            }
        });
        
        // Handle window resize
        function handleResize() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                mainContent.classList.remove('active');
            }
        }
        
        window.addEventListener('resize', handleResize);
    });
    
    // Form Validation
    (function() {
        'use strict';
        
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation');
        
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
    
    // Image Preview and Validation
    document.getElementById('gambar').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('image-preview');
        const uploadArea = document.getElementById('image-upload-area');
        
        if (file) {
            // Check file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Format file tidak didukung. Harap unggah file JPG, PNG, atau GIF.');
                this.value = '';
                return;
            }
            
            // Check file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file terlalu besar. Maksimal 2MB');
                this.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                uploadArea.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        }
            
            // Check file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Format file tidak didukung. Gunakan JPG, PNG, atau GIF');
                this.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').src = e.target.result;
                document.getElementById('image-upload-area').classList.add('border-primary');
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Drag and drop for image upload
    const dropArea = document.getElementById('image-upload-area');
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        dropArea.classList.add('border-primary', 'bg-light');
    }
    
    function unhighlight() {
        dropArea.classList.remove('border-primary', 'bg-light');
    }
    
    dropArea.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const file = dt.files[0];
        document.getElementById('gambar').files = dt.files;
        
        // Trigger change event
        const event = new Event('change');
        document.getElementById('gambar').dispatchEvent(event);
    }
    
    // Form validation
    (function () {
        'use strict'
        
        // Fetch the form we want to apply custom Bootstrap validation styles to
        const forms = document.querySelectorAll('.needs-validation')
        
        // Loop over them and prevent submission
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>
