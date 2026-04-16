<?php
session_start();
include '../includes/config.php';

// Cek login dan role user
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

$nama_user = $_SESSION['user']['nama'];

// Data pelengkap: jumlah notifikasi belum dibaca dan pesanan terakhir
$unread_notifications = 0;
$latest_order = null;

// Ambil jumlah notifikasi belum dibaca jika tabel tersedia
$checkNotifTable = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
if ($checkNotifTable && mysqli_num_rows($checkNotifTable) > 0) {
    $id_user_logged = (int)$_SESSION['user']['id_user'];
    $resUnread = mysqli_query($conn, "SELECT COUNT(*) AS c FROM notifications WHERE id_user = {$id_user_logged} AND is_read = 0");
    if ($resUnread && $rowUnread = mysqli_fetch_assoc($resUnread)) {
        $unread_notifications = (int)$rowUnread['c'];
    }
}

// Ambil pesanan terakhir user (untuk menampilkan status cepat)
$id_user_logged = (int)$_SESSION['user']['id_user'];
$resLatest = mysqli_query($conn, "SELECT id_pesanan, tanggal, status, total_harga FROM pesanan WHERE id_user = {$id_user_logged} ORDER BY tanggal DESC LIMIT 1");
if ($resLatest && mysqli_num_rows($resLatest) > 0) {
    $latest_order = mysqli_fetch_assoc($resLatest);
}

// Helper untuk membuat slug kategori
if (!function_exists('make_slug')) {
    function make_slug($text)
    {
        $text = strtolower($text ?? '');
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        $text = trim($text, '-');
        return $text === '' ? 'lainnya' : $text;
    }
}

// Ambil data menu dari database
$query = mysqli_query($conn, "SELECT * FROM menu");

// Ambil daftar kategori unik dengan pengecekan kolom 'kategori'
$categories = [];
$hasKategoriCol = false;
$checkKategori = mysqli_query($conn, "SHOW COLUMNS FROM menu LIKE 'kategori'");
if ($checkKategori && mysqli_num_rows($checkKategori) > 0) {
    $hasKategoriCol = true;
}

if ($hasKategoriCol) {
    $category_result = mysqli_query($conn, "SELECT DISTINCT kategori FROM menu ORDER BY kategori ASC");
    if ($category_result) {
        while ($category_row = mysqli_fetch_assoc($category_result)) {
            $categories[] = $category_row['kategori'] ?? 'Lainnya';
        }
    }
} else {
    // Fallback jika kolom kategori tidak ada di database
    $categories = ['Makanan', 'Minuman', 'Snack', 'Paket'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodOrder - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff8f3;
            color: #333;
        }

        /* Navbar */
        .navbar {
            background-color: #ff6b35;
            color: white;
            padding: 15px 25px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .navbar .brand {
            font-size: 1.6rem;
            font-weight: 700;
        }

        .navbar input {
            border-radius: 30px;
            border: none;
            padding: 10px 15px;
            width: 100%;
            outline: none;
        }

        .main-container {
            display: flex;
            gap: 25px;
            padding: 25px;
        }

        /* Menu Section */
        .menu-section {
            flex: 3;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .menu-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: 0.3s;
        }

        .menu-card:hover {
            transform: translateY(-5px);
        }

        .menu-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }

        .menu-card .menu-body {
            padding: 15px;
        }

        .menu-card h5 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .menu-card .price {
            color: #ff6b35;
            font-weight: 600;
            margin: 10px 0;
        }

        .menu-card button {
            width: 100%;
            background-color: #ff6b35;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 0;
            transition: 0.3s;
        }

        .menu-card button:hover {
            background-color: #ff814e;
        }

        /* Cart Section */
        .cart-section {
            flex: 1.2;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 20px;
            height: fit-content;
            position: sticky;
            top: 90px;
        }

        .cart-section h5 {
            font-weight: 700;
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .cart-item img {
            width: 55px;
            height: 55px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 10px;
        }

        .cart-total {
            margin-top: 20px;
            font-size: 1rem;
        }

        .btn-checkout {
            width: 100%;
            background-color: #ff6b35;
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            padding: 10px;
            margin-top: 15px;
            transition: 0.3s;
        }

        .btn-checkout:hover {
            background-color: #ff814e;
        }

        /* Responsif */
        @media (max-width: 992px) {
            .main-container {
                flex-direction: column;
            }

            .cart-section {
                position: relative;
                top: 0;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar d-flex align-items-center justify-content-between">
    <div class="brand">🍔 FoodOrder</div>
    <div class="flex-grow-1 mx-3">
        <input type="text" placeholder="Apa yang ingin kamu makan hari ini?">
    </div>
    <div class="d-flex align-items-center">
        <span class="me-2">👋 Halo, <?= htmlspecialchars($nama_user) ?></span>
        <a href="notifications.php" class="btn btn-sm btn-light position-relative me-2">
            Notifikasi
            <?php if ($unread_notifications > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= $unread_notifications ?>
                    <span class="visually-hidden">unread</span>
                </span>
            <?php endif; ?>
        </a>
        <a href="history.php" class="btn btn-sm btn-light me-2">Riwayat</a>
        <a href="../logout.php" class="btn btn-sm btn-dark">Logout</a>
    </div>
</nav>

<style>
/* Menu Section Styles */
.menu-section {
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.menu-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.search-box {
    position: relative;
    width: 300px;
}

.search-box input {
    width: 100%;
    padding: 10px 15px 10px 40px;
    border: 1px solid #ddd;
    border-radius: 30px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    border-color: #ff6b35;
    box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
    outline: none;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
}

.categories {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    overflow-x: auto;
    padding-bottom: 10px;
}

.category-btn {
    padding: 8px 20px;
    border: 1px solid #ddd;
    background: #f8f9fa;
    border-radius: 20px;
    font-size: 0.9rem;
    color: #555;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.3s ease;
}

.category-btn:hover, .category-btn.active {
    background: #ff6b35;
    color: white;
    border-color: #ff6b35;
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.menu-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
}

.menu-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.menu-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1;
}

.menu-badge .badge {
    font-size: 0.75rem;
    padding: 5px 10px;
    border-radius: 15px;
    font-weight: 500;
}

.menu-image {
    width: 100%;
    height: 180px;
    overflow: hidden;
}

.menu-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.menu-card:hover .menu-image img {
    transform: scale(1.05);
}

.menu-details {
    padding: 15px;
}

.menu-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 5px 0;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.menu-price {
    color: #ff6b35;
    font-weight: 700;
    font-size: 1.1rem;
    margin: 0 0 10px 0;
}

.menu-description {
    color: #666;
    font-size: 0.9rem;
    margin: 0 0 15px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 40px;
}

.add-to-cart-form {
    margin-top: 15px;
}

.qty-selector {
    display: flex;
    align-items: center;
    background: #f5f5f5;
    border-radius: 20px;
    padding: 2px;
    width: 100px;
}

.qty-btn {
    background: none;
    border: none;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1rem;
    color: #555;
    transition: all 0.2s;
}

.qty-btn:hover {
    background: #e9ecef;
    border-radius: 50%;
}

.qty-input {
    width: 40px;
    text-align: center;
    border: none;
    background: transparent;
    font-weight: 600;
    -moz-appearance: textfield;
}

.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.btn-add-to-cart {
    background: #ff6b35;
    color: white;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-left: 10px;
}

.btn-add-to-cart:hover {
    background: #e65a2b;
    transform: scale(1.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .menu-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .search-box {
        width: 200px;
    }
}

@media (max-width: 576px) {
    .menu-grid {
        grid-template-columns: 1fr;
    }
    
    .menu-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .search-box {
        width: 100%;
    }
}
</style>

<!-- Main Container -->
<div class="main-container">
    <!-- Menu Section -->
    <div class="menu-section">
        <div class="menu-header">
            <h1 class="menu-title">Menu Makanan</h1>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Cari menu..." id="search-menu">
            </div>
        </div>
        
        <!-- Categories -->
        <div class="categories">
            <button class="category-btn active" data-category="all">Semua</button>
            <?php if (!empty($categories)) : ?>
                <?php foreach ($categories as $category_name) :
                    $category_slug = make_slug($category_name);
                ?>
                    <button class="category-btn" data-category="<?= htmlspecialchars($category_slug) ?>">
                        <?= htmlspecialchars($category_name) ?>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Status pesanan terakhir dipindahkan: tampil hanya lewat Notifikasi dan Riwayat -->
        
        <div class="menu-grid">
            <?php 
            // Reset pointer to the beginning of the result set
            mysqli_data_seek($query, 0);
            while ($row = mysqli_fetch_assoc($query)) : 
                // Format harga ke Rupiah
                $harga = number_format($row['harga'], 0, ',', '.');
                
                // Cek dan sesuaikan nama file gambar
                $gambar_file = trim(htmlspecialchars($row['gambar']));
                $gambar_path = '../assets/img/' . $gambar_file;
                
                // Daftar ekstensi yang diperbolehkan
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                $default_image = '../assets/img/default-food.jpg';
                
                // Cek apakah file gambar ada dengan case-insensitive
                $gambar_src = $default_image;
                if (!empty($gambar_file)) {
                    foreach ($allowed_extensions as $ext) {
                        $test_path = '../assets/img/' . pathinfo($gambar_file, PATHINFO_FILENAME) . '.' . $ext;
                        if (file_exists($test_path)) {
                            $gambar_src = $test_path;
                            break;
                        }
                    }
                }
            ?>
                <?php $category_slug = make_slug($row['kategori'] ?? 'Lainnya'); ?>
                <div class="menu-card" data-category="<?= htmlspecialchars($category_slug) ?>">
                    <div class="menu-badge">
                        <span class="badge bg-success">Tersedia</span>
                    </div>
                    <div class="menu-image">
                        <img src="<?= $gambar_src ?>" alt="<?= htmlspecialchars($row['nama_menu']) ?>">
                    </div>
                    <div class="menu-details">
                        <h5 class="menu-title"><?= htmlspecialchars($row['nama_menu']) ?></h5>
                        <p class="menu-price">Rp<?= number_format($row['harga'], 0, ',', '.') ?></p>
                        <p class="menu-description"><?= htmlspecialchars($row['deskripsi']) ?></p>
                        <form method="post" action="add_to_chart.php" class="add-to-cart-form">
                            <input type="hidden" name="id_menu" value="<?= $row['id_menu'] ?>">
                            <div class="d-flex align-items-center">
                                <div class="qty-selector">
                                    <button type="button" class="qty-btn minus">-</button>
                                    <input type="number" name="qty" value="1" min="1" class="qty-input" readonly>
                                    <button type="button" class="qty-btn plus">+</button>
                                </div>
                                <button type="submit" class="btn-add-to-cart" title="Tambah ke Keranjang">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

    <!-- Cart Section -->
    <div class="cart-section" style="background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-top: 20px;">
        <h5 class="mb-3" style="font-weight: 600; color: #333; border-bottom: 2px solid #ff6b35; padding-bottom: 10px; display: flex; align-items: center;">
            <i class="fas fa-shopping-cart me-2" style="color: #ff6b35;"></i> Pesanan Saya
            <span class="badge bg-danger ms-2" id="cart-count">
                <?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>
            </span>
        </h5>
        <div id="cart-message" class="mb-3"></div>
        <div id="cart-items" style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
            <?php
            $subtotal = 0;
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $key => $item) {
                    // Pastikan data valid
                    $harga = is_numeric($item['harga']) ? $item['harga'] : 0;
                    $qty = is_numeric($item['qty']) ? $item['qty'] : 1;
                    $total_item = $harga * $qty;
                    $subtotal += $total_item;
                    
                    // Format harga
                    $harga_formatted = 'Rp ' . number_format($harga, 0, ',', '.');
                    $total_item_formatted = 'Rp ' . number_format($total_item, 0, ',', '.');
                    
                    // Handle gambar
                    $gambar_src = '../assets/img/default-food.jpg';
                    if (!empty($item['gambar'])) {
                        $gambar_file = '../assets/img/' . $item['gambar'];
                        if (file_exists($gambar_file)) {
                            $gambar_src = $gambar_file;
                        } else {
                            // Coba cari ekstensi yang sesuai
                            $gambar_name = pathinfo($item['gambar'], PATHINFO_FILENAME);
                            $extensions = ['jpg', 'jpeg', 'png', 'gif'];
                            foreach ($extensions as $ext) {
                                $test_path = '../assets/img/' . $gambar_name . '.' . $ext;
                                if (file_exists($test_path)) {
                                    $gambar_src = $test_path;
                                    break;
                                }
                            }
                        }
                    }
                    ?>
                    <div class="cart-item d-flex align-items-center p-3 mb-3" 
                         id="cart-item-<?= $item['id_menu'] ?>"
                         data-price="<?= $harga ?>" 
                         data-qty="<?= $qty ?>"
                         style="background: #f8f9fa; border-radius: 10px; transition: all 0.3s ease;">
                        
                        <!-- Gambar Produk -->
                        <div style="width: 80px; height: 80px; overflow: hidden; border-radius: 8px; margin-right: 15px; flex-shrink: 0;">
                            <img src="<?= $gambar_src ?>" 
                                 alt="<?= htmlspecialchars($item['nama_menu']) ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover; border: 1px solid #eee;">
                        </div>
                        
                        <!-- Detail Produk -->
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1" style="font-weight: 600; color: #333;">
                                        <?= htmlspecialchars($item['nama_menu']) ?>
                                    </h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-light text-dark me-2 item-qty-badge" data-id="<?= $item['id_menu'] ?>"><?= $qty ?>x</span>
                                        <span class="text-muted item-price-single" data-id="<?= $item['id_menu'] ?>"><?= $harga_formatted ?></span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary item-total" data-id="<?= $item['id_menu'] ?>"><?= $total_item_formatted ?></div>
                                </div>
                            </div>
                            
                            <!-- Tombol Aksi -->
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-item" 
                                        data-id="<?= $item['id_menu'] ?>"
                                        style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                    <i class="fas fa-trash-alt me-1"></i> Hapus
                                </button>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-secondary btn-minus" 
                                            data-id="<?= $item['id_menu'] ?>"
                                            style="padding: 0.25rem 0.5rem;">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="btn item-qty-display" data-id="<?= $item['id_menu'] ?>" style="min-width: 30px;"><?= $qty ?></span>
                                    <button type="button" class="btn btn-outline-secondary btn-plus" 
                                            data-id="<?= $item['id_menu'] ?>"
                                            style="padding: 0.25rem 0.5rem;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <div class="cart-summary mt-4 p-3" style="background: #f8f9fa; border-radius: 10px;">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong class="subtotal-amount">Rp <?= number_format($subtotal, 0, ',', '.') ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ongkir:</span>
                        <strong>Rp 0</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <span class="fw-bold">Total:</span>
                        <h5 class="mb-0 text-primary total-amount">Rp <?= number_format($subtotal, 0, ',', '.') ?></h5>
                    </div>
                    <a href="checkout.php" class="btn btn-primary w-100 mt-3 py-2 fw-bold" 
                       style="border-radius: 8px; font-size: 1rem;">
                        <i class="fas fa-credit-card me-2"></i>Lanjut ke Pembayaran
                    </a>
                </div>
                <?php
            } else {
                echo '<div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-shopping-cart" style="font-size: 3rem; color: #dee2e6;"></i>
                    </div>
                    <p class="text-muted mb-3">Keranjang belanja Anda masih kosong</p>
                    <a href="#" class="btn btn-outline-primary">Lihat Menu</a>
                </div>';
            }
            ?>
        </div>
    </div>

</div>

    <!-- jQuery and AJAX for cart functionality -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        const $menuCards = $('.menu-card');
        const $categoryButtons = $('.category-btn');
        const $searchInput = $('#search-menu');
        let activeCategory = 'all';

        function normalizeText(text) {
            return (text || '').toString().toLowerCase();
        }

        function filterMenu() {
            const searchTerm = normalizeText($searchInput.val());

            $menuCards.each(function() {
                const $card = $(this);
                const cardCategory = $card.data('category') || 'lainnya';
                const matchesCategory = activeCategory === 'all' || cardCategory === activeCategory;
                const nameText = normalizeText($card.find('.menu-title').text());
                const descText = normalizeText($card.find('.menu-description').text());
                const matchesSearch = !searchTerm || nameText.includes(searchTerm) || descText.includes(searchTerm);

                if (matchesCategory && matchesSearch) {
                    $card.closest('.menu-card').show();
                } else {
                    $card.closest('.menu-card').hide();
                }
            });
        }

        $categoryButtons.on('click', function() {
            const $btn = $(this);
            activeCategory = $btn.data('category') || 'all';

            $categoryButtons.removeClass('active');
            $btn.addClass('active');
            filterMenu();
        });

        $searchInput.on('input', function() {
            filterMenu();
        });

        // Quantity selector on product cards (plus/minus)
        // This updates the readonly input `.qty-input` within each card form
        $(document).on('click', '.menu-card .qty-btn', function() {
            const $btn = $(this);
            const $form = $btn.closest('.add-to-cart-form');
            const $input = $form.find('.qty-input');
            let current = parseInt($input.val(), 10);
            if (isNaN(current) || current < 1) current = 1;

            if ($btn.hasClass('plus')) {
                current += 1;
            } else {
                current = Math.max(1, current - 1);
            }

            $input.val(current);
        });

        // Update cart count badge
        function updateCartCount() {
            const count = $('.cart-item').length;
            $('#cart-count').text(count);
            return count;
        }

        // Handle remove item
        $(document).on('click', '.remove-item', function(e) {
            e.preventDefault();
            
            const id_menu = $(this).data('id');
            const itemElement = $(`#cart-item-${id_menu}`);
            
            if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
                $.ajax({
                    url: 'remove.php',
                    type: 'POST',
                    data: { 
                        action: 'remove',
                        id_menu: id_menu 
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Remove item from UI
                            itemElement.slideUp(300, function() {
                                $(this).remove();
                                updateCartTotal();
                                updateCartCount();
                                showAlert('success', response.message);
                                
                                // If cart is empty, show message
                                if ($('.cart-item').length === 0) {
                                    location.reload(); // Reload to show empty cart state
                                }
                            });
                        } else {
                            showAlert('danger', response.message || 'Gagal menghapus item');
                        }
                    },
                    error: function() {
                        showAlert('danger', 'Terjadi kesalahan saat menghubungi server');
                    }
                });
            }
        });

        // Handle quantity changes
        $(document).on('click', '.btn-minus, .btn-plus', function() {
            const id_menu = $(this).data('id');
            const isIncrement = $(this).hasClass('btn-plus');
            const qtyElement = $(this).siblings('.item-qty-display').first();
            let qty = parseInt(qtyElement.text());
            
            // Update quantity
            qty = isIncrement ? qty + 1 : Math.max(1, qty - 1);
            
            // Update UI immediately for better UX
            qtyElement.text(qty);
            
            // Update cart in session via AJAX
            $.ajax({
                url: 'update_cart.php',
                type: 'POST',
                data: {
                    id_menu: id_menu,
                    qty: qty
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update item total and qty indicators
                        const itemElement = $(`#cart-item-${id_menu}`);
                        const price = parseFloat(itemElement.data('price')) || 0;
                        const newTotal = price * qty;

                        // Update data attributes
                        itemElement.data('qty', qty);

                        // Update UI for this item
                        itemElement.find('.item-total').text('Rp ' + newTotal.toLocaleString('id-ID'));
                        itemElement.find('.item-qty-display[data-id="' + id_menu + '"]').text(qty);
                        itemElement.find('.item-qty-badge[data-id="' + id_menu + '"]').text(qty + 'x');

                        // Update subtotal and total from server response if available
                        if (typeof response.subtotal !== 'undefined') {
                            const subtotal = parseFloat(response.subtotal) || 0;
                            $('.subtotal-amount').text('Rp ' + subtotal.toLocaleString('id-ID'));
                            $('.total-amount').text('Rp ' + subtotal.toLocaleString('id-ID'));
                        } else {
                            // Fallback: recompute client-side
                            updateCartTotal();
                        }

                        showAlert('success', 'Jumlah berhasil diupdate');
                    } else {
                        // Revert on error
                        qtyElement.text(qty + (isIncrement ? -1 : 1));
                        showAlert('danger', response.message || 'Gagal mengupdate jumlah');
                    }
                },
                error: function() {
                    // Revert on error
                    qtyElement.text(qty + (isIncrement ? -1 : 1));
                    showAlert('danger', 'Terjadi kesalahan saat mengupdate keranjang');
                }
            });
        });
        
        // Update cart total
        function updateCartTotal() {
            let subtotal = 0;
            $('.cart-item').each(function() {
                const price = parseFloat($(this).data('price')) || 0;
                const qty = parseInt($(this).data('qty')) || 1;
                subtotal += price * qty;
            });
            
            // Update subtotal and total display
            $('.subtotal-amount').text('Rp ' + subtotal.toLocaleString('id-ID'));
            $('.total-amount').text('Rp ' + subtotal.toLocaleString('id-ID'));
        }
        
        // Show alert message
        function showAlert(type, message) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            // Remove any existing alerts
            $('.alert-dismissible').remove();
            
            // Add new alert
            $('#cart-message').html(alertHtml);
            
            // Auto hide after 3 seconds
            setTimeout(() => {
                $('.alert-dismissible').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    });
    </script>
</body>
</html>
