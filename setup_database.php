<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Include configuration
include 'includes/config.php';

// Set content type to HTML with UTF-8
header('Content-Type: text/html; charset=utf-8');

// Function to display messages
function show_message($message, $type = 'info') {
    $colors = [
        'success' => '28a745',
        'danger' => 'dc3545',
        'warning' => 'ffc107',
        'info' => '17a2b8'
    ];
    $color = $colors[$type] ?? '6c757d';
    return "<div style='background: #{$color}20; border-left: 4px solid #$color; padding: 12px 15px; margin: 10px 0; border-radius: 0 4px 4px 0;'>$message</div>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - FoodOrder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            padding: 2rem 0;
        }
        .setup-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .setup-header h1 {
            color: #ff6b35;
            font-weight: 700;
        }
        .setup-step {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .setup-step h4 {
            color: #495057;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        .btn-setup {
            background: #ff6b35;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-setup:hover {
            background: #e65c2e;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <div class="setup-header">
                <h1>🍔 FoodOrder - Setup Database</h1>
                <p class="text-muted">Menyiapkan database dan tabel yang diperlukan</p>
            </div>
            
            <div class="setup-content">
                <div class="setup-step">
                    <h4>Koneksi Database</h4>
                    <?php
                    // Check connection
                    if (!$conn) {
                        echo show_message("❌ Gagal terhubung ke database: " . mysqli_connect_error(), 'danger');
                        exit;
                    } else {
                        echo show_message("✅ Terhubung ke database dengan sukses", 'success');
                        
                        // Set charset to utf8mb4
                        if (!mysqli_set_charset($conn, 'utf8mb4')) {
                            echo show_message("❌ Gagal mengatur charset: " . mysqli_error($conn), 'warning');
                        }
                    }
                    ?>
                </div>

// Create users table if not exists
$sql = [
    "users" => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
        foto VARCHAR(255) DEFAULT NULL,
        no_hp VARCHAR(20) DEFAULT NULL,
        alamat TEXT DEFAULT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        last_login DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

// Create tables
$tables = [
    // Users table
    "users" => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
        foto VARCHAR(255) DEFAULT NULL,
        no_hp VARCHAR(20) DEFAULT NULL,
        alamat TEXT DEFAULT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        last_login DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Menu table
    "menu" => "CREATE TABLE IF NOT EXISTS menu (
        id_menu INT AUTO_INCREMENT PRIMARY KEY,
        nama_menu VARCHAR(100) NOT NULL,
        deskripsi TEXT,
        kategori VARCHAR(50) DEFAULT 'Makanan',
        harga DECIMAL(10,2) NOT NULL,
        gambar VARCHAR(255) DEFAULT 'default.jpg',
        stok INT DEFAULT 0,
        status ENUM('tersedia', 'habis') DEFAULT 'tersedia',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Pesanan table
    "pesanan" => "CREATE TABLE IF NOT EXISTS pesanan (
        id_pesanan INT AUTO_INCREMENT PRIMARY KEY,
        id_user INT NOT NULL,
        kode_pesanan VARCHAR(20) NOT NULL UNIQUE,
        nama_pelanggan VARCHAR(100) NOT NULL,
        no_meja VARCHAR(10) DEFAULT NULL,
        total_harga DECIMAL(12,2) NOT NULL DEFAULT 0,
        status_pesanan ENUM('menunggu', 'diproses', 'selesai', 'dibatalkan') DEFAULT 'menunggu',
        metode_pembayaran ENUM('tunai', 'transfer', 'qris') DEFAULT 'tunai',
        status_pembayaran ENUM('belum', 'dibayar', 'dikembalikan') DEFAULT 'belum',
        catatan TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Detail Pesanan table
    "detail_pesanan" => "CREATE TABLE IF NOT EXISTS detail_pesanan (
        id_detail INT AUTO_INCREMENT PRIMARY KEY,
        id_pesanan INT NOT NULL,
        id_menu INT NOT NULL,
        jumlah INT NOT NULL,
        harga_satuan DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(12,2) NOT NULL,
        catatan TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE,
        FOREIGN KEY (id_menu) REFERENCES menu(id_menu) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

// Create tables
foreach ($tables as $table_name => $sql) {
    echo "<div class='setup-step'>";
    echo "<h4>Membuat tabel " . ucfirst($table_name) . "</h4>";
    
    if (mysqli_query($conn, $sql)) {
        echo show_message("✅ Tabel $table_name berhasil dibuat", 'success');
        
        // If this is the users table, create default admin user
        if ($table_name === 'users') {
            $check_admin = mysqli_query($conn, "SELECT * FROM users WHERE role='admin' LIMIT 1");
            if (mysqli_num_rows($check_admin) == 0) {
                $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
                $insert_admin = "INSERT INTO users (nama, email, password, role, status) 
                               VALUES ('Administrator', 'admin@example.com', '$hashed_password', 'admin', 'active')";
                
                if (mysqli_query($conn, $insert_admin)) {
                    echo show_message("✅ Admin default berhasil dibuat (email: admin@example.com, password: admin123)", 'success');
                } else {
                    echo show_message("❌ Gagal membuat admin: " . mysqli_error($conn), 'danger');
                }
            } else {
                echo show_message("ℹ️ Akun admin sudah ada", 'info');
            }
        }
    } else {
        echo show_message("❌ Gagal membuat tabel $table_name: " . mysqli_error($conn), 'danger');
    }
    
    echo "</div>";
}
    
    // Create default user if not exists (password: user123)
    $check_user = mysqli_query($conn, "SELECT * FROM users WHERE role='user' LIMIT 1");
    if (mysqli_num_rows($check_user) == 0) {
        $hashed_password = password_hash('user123', PASSWORD_DEFAULT);
        $insert_user = "INSERT INTO users (nama, email, password, role) 
                       VALUES ('Regular User', 'user@example.com', '$hashed_password', 'user')";
        
        if (mysqli_query($conn, $insert_user)) {
            echo "<p style='color:green'>✅ Default user created (email: user@example.com, password: user123)</p>";
        } else {
            echo "<p style='color:red'>❌ Error creating default user: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p>ℹ️ Regular user already exists</p>";
    }
    
} else {
    echo "<p style='color:red'>❌ Error creating users table: " . mysqli_error($conn) . "</p>";
}

// Add sample menu items if table is empty
$check_menu = mysqli_query($conn, "SELECT * FROM menu LIMIT 1");
if (mysqli_num_rows($check_menu) == 0) {
    echo "<div class='setup-step'>";
    echo "<h4>Menambahkan Menu Contoh</h4>";
    
    $menu_items = [
        ['Nasi Goreng Spesial', 'Nasi goreng dengan telur, ayam, dan sayuran', 35000, 'nasi_goreng.jpg', 'Makanan', 50],
        ['Mie Ayam Bakso', 'Mie ayam dengan bakso sapi pilihan', 30000, 'mie_ayam.jpg', 'Makanan', 40],
        ['Es Teh Manis', 'Es teh dengan gula aren', 8000, 'es_teh.jpg', 'Minuman', 100],
        ['Jus Alpukat', 'Jus alpukat segar dengan susu kental manis', 15000, 'jus_alpukat.jpg', 'Minuman', 30],
        ['Ayam Geprek', 'Ayam krispi dengan sambal geprek', 25000, 'geprek.jpg', 'Makanan', 35],
        ['Es Jeruk', 'Es jeruk segar', 10000, 'es_jeruk.jpg', 'Minuman', 80],
        ['Nasi Uduk', 'Nasi uduk komplit dengan lauk', 20000, 'nasi_uduk.jpg', 'Makanan', 45],
        ['Es Campur', 'Es campur dengan berbagai buah', 15000, 'es_campur.jpg', 'Dessert', 60]
    ];
    
    $success_count = 0;
    foreach ($menu_items as $item) {
        $insert_menu = "INSERT INTO menu (nama_menu, deskripsi, harga, gambar, kategori, stok) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $insert_menu);
        mysqli_stmt_bind_param($stmt, "ssisss", $item[0], $item[1], $item[2], $item[3], $item[4], $item[5]);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_count++;
        }
        mysqli_stmt_close($stmt);
    }
    
    echo show_message("✅ Berhasil menambahkan $success_count menu contoh", 'success');
    echo "</div>";
}

// Add sample order for demo
$check_order = mysqli_query($conn, "SELECT * FROM pesanan LIMIT 1");
if (mysqli_num_rows($check_order) == 0) {
    echo "<div class='setup-step'>";
    echo "<h4>Menambahkan Contoh Pesanan</h4>";
    
    // Get admin user ID
    $admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE role='admin' LIMIT 1"));
    $admin_id = $admin ? $admin['id'] : 1;
    
    // Create sample order
    $kode_pesanan = 'INV-' . date('Ymd') . '-' . strtoupper(uniqid());
    $insert_order = "INSERT INTO pesanan (id_user, kode_pesanan, nama_pelanggan, no_meja, total_harga, status_pesanan, status_pembayaran) 
                    VALUES (?, ?, 'Pelanggan Contoh', 'A1', 65000, 'selesai', 'dibayar')";
    
    $stmt = mysqli_prepare($conn, $insert_order);
    mysqli_stmt_bind_param($stmt, "is", $admin_id, $kode_pesanan);
    
    if (mysqli_stmt_execute($stmt)) {
        $order_id = mysqli_insert_id($conn);
        echo show_message("✅ Contoh pesanan berhasil dibuat (Kode: $kode_pesanan)", 'success');
        
        // Add order items
        $menu_items = [
            [1, 2, 35000], // Nasi Goreng Spesial x2
            [3, 1, 10000]  // Es Jeruk x1
        ];
        
        $success_items = 0;
        foreach ($menu_items as $item) {
            $subtotal = $item[1] * $item[2];
            $insert_item = "INSERT INTO detail_pesanan (id_pesanan, id_menu, jumlah, harga_satuan, subtotal) 
                           VALUES (?, ?, ?, ?, ?)";
            
            $stmt_item = mysqli_prepare($conn, $insert_item);
            mysqli_stmt_bind_param($stmt_item, "iiidi", $order_id, $item[0], $item[1], $item[2], $subtotal);
            
            if (mysqli_stmt_execute($stmt_item)) {
                $success_items++;
            }
            mysqli_stmt_close($stmt_item);
        }
        
        echo show_message("✅ Berhasil menambahkan $success_items item ke pesanan", 'success');
    } else {
        echo show_message("❌ Gagal membuat contoh pesanan: " . mysqli_error($conn), 'danger');
    }
    
    mysqli_stmt_close($stmt);
    echo "</div>";
}

// Show completion message
echo "<div class='text-center mt-5'>";
echo "<div class='alert alert-success' role='alert'>";
echo "<h4 class='alert-heading'>Setup Selesai!</h4>";
echo "<p>Database dan tabel berhasil dibuat dan diinisialisasi.</p>";
echo "<hr>";

echo "<div class='row justify-content-center mb-3'>";
echo "<div class='col-md-8'>";
echo "<div class='card shadow-sm'>";
echo "<div class='card-body'>";
echo "<h5 class='card-title'>Informasi Login</h5>";
echo "<table class='table table-borderless'>";
echo "<tr>";
echo "<th>Email</th>";
echo "<td>admin@example.com</td>";
echo "</tr>";
echo "<tr>";
echo "<th>Password</th>";
echo "<td>admin123</td>";
echo "</tr>";
echo "</table>";
echo "<div class='alert alert-warning' role='alert'>";
echo "<i class='fas fa-exclamation-triangle me-2'></i> Segera ganti password default setelah login pertama kali!";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<a href='login.php' class='btn btn-setup btn-lg px-5'>";
echo "<i class='fas fa-sign-in-alt me-2'></i> Masuk ke Aplikasi";
echo "</a>";
echo "</div>";

// Close connection
mysqli_close($conn);
?>

</div><!-- /.setup-content -->
</div><!-- /.setup-container -->
</div><!-- /.container -->

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>

<?php
// Flush output buffer
ob_end_flush();
?>
