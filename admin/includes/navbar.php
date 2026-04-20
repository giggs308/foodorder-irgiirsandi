<nav class="navbar">
    <div class="navbar-left"><?php
// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get count of new orders (status = 'menunggu')
$new_orders_query = mysqli_query($GLOBALS['conn'], "SELECT COUNT(*) as count FROM pesanan WHERE status = 'menunggu'");
$new_orders = mysqli_fetch_assoc($new_orders_query)['count'];

$page_name = basename($_SERVER['PHP_SELF'], '.php');
$page_name = ucfirst($page_name);
?>
<header class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top" style="height: var(--header-height);">
    <div class="container-fluid">
        <!-- Toggle sidebar button -->
        <button class="btn btn-link text-white me-2" id="sidebarToggle">
            <i class='bx bx-menu fs-4'></i>
        </button>
        
        <!-- Page title -->
        <div class="navbar-brand d-none d-md-flex align-items-center">
            <h1 class="h5 mb-0"><?= $page_name ?: 'Dashboard' ?></h1>
        </div>
        
        <!-- Mobile menu button -->
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
            <i class='bx bx-dots-vertical-rounded fs-4'></i>
        </button>
        
        <!-- Navbar content -->
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto align-items-center">
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class='bx bx-bell fs-5'></i>
                        <?php if ($new_orders > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $new_orders ?>
                                <span class="visually-hidden">notifikasi baru</span>
                            </span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start" aria-labelledby="notificationsDropdown">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Notifikasi</h6>
                            <?php if ($new_orders > 0): ?>
                                <a href="pesanan.php?status=menunggu" class="btn btn-sm btn-outline-primary btn-sm">
                                    Lihat Semua
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown-divider"></div>
                        <?php if ($new_orders > 0): ?>
                            <a class="dropdown-item" href="pesanan.php?status=menunggu">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 text-primary">
                                        <i class='bx bx-cart fs-4'></i>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <h6 class="mb-0"><?= $new_orders ?> Pesanan Baru</h6>
                                        <small class="text-muted">Klik untuk melihat detail</small>
                                    </div>
                                </div>
                            </a>
                        <?php else: ?>
                            <div class="text-center p-3 text-muted">
                                <i class='bx bx-bell-off fs-1 mb-2 d-block'></i>
                                <p class="mb-0">Tidak ada notifikasi baru</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </li>
                
                <!-- User menu -->
                <li class="nav-item dropdown ms-2">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <div class="avatar bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <?= strtoupper(substr($_SESSION['user']['nama'] ?? 'A', 0, 1)) ?>
                        </div>
                        <span class="ms-2 d-none d-lg-inline">
                            <?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Admin') ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <div class="dropdown-header">
                                <h6 class="mb-0"><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Admin') ?></h6>
                                <small class="text-muted"><?= ucfirst($_SESSION['user']['role'] ?? 'admin') ?></small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="profile.php">
                                <i class='bx bx-user me-2'></i> Profil Saya
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="settings.php">
                                <i class='bx bx-cog me-2'></i> Pengaturan
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="../logout.php">
                                <i class='bx bx-log-out me-2'></i> Keluar
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</header>

<script>
// Toggle sidebar on mobile
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('active');
    document.querySelector('.main-content').classList.toggle('active');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.getElementById('toggle-sidebar');
    
    if (window.innerWidth <= 992) {
        if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
            sidebar.classList.remove('active');
            document.querySelector('.main-content').classList.remove('active');
        }
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 992) {
        document.querySelector('.sidebar').classList.remove('active');
        document.querySelector('.main-content').classList.remove('active');
    }
});
</script>
