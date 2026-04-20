<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Get count of pending orders
$pending_orders_query = mysqli_query($GLOBALS['conn'], "SELECT COUNT(*) as count FROM pesanan WHERE status = 'menunggu'");
$pending_orders = mysqli_fetch_assoc($pending_orders_query)['count'];
?>

<aside class="sidebar bg-dark text-white" id="sidebar">
    <div class="sidebar-header d-flex justify-content-between align-items-center p-3 border-bottom border-secondary">
        <div class="d-flex align-items-center">
            <i class='bx bxs-restaurant fs-4 me-2 text-primary'></i>
            <h4 class="m-0 fw-bold">FoodOrder</h4>
        </div>
        <button class="btn btn-link text-white d-none d-lg-block" id="sidebarCollapse">
            <i class='bx bx-chevron-left'></i>
        </button>
    </div>
    
    <div class="sidebar-content px-2 py-3">
        <!-- User Profile -->
        <div class="text-center mb-4 px-3">
            <div class="avatar bg-primary text-white d-inline-flex align-items-center justify-content-center mb-2" 
                 style="width: 80px; height: 80px; font-size: 32px; border: 3px solid rgba(255,255,255,0.1);">
                <?= strtoupper(substr($_SESSION['user']['nama'] ?? 'A', 0, 1)) ?>
            </div>
            <h5 class="mb-1 text-white"><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Admin') ?></h5>
            <?php $role = ucfirst($_SESSION['user']['role'] ?? 'Admin'); if (trim($role) !== ''): ?>
            <span class="badge rounded-pill sidebar-role-badge">
                <?= htmlspecialchars($role) ?>
            </span>
            <?php endif; ?>
        </div>
        
        <!-- Navigation -->
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link d-flex align-items-center py-3 px-3 rounded-3 mb-1 <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
                    <i class='bx bxs-dashboard fs-5 me-2'></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="menu.php" class="nav-link d-flex align-items-center py-3 px-3 rounded-3 mb-1 <?= ($current_page == 'menu.php') ? 'active' : '' ?>">
                    <i class='bx bx-food-menu fs-5 me-2'></i>
                    <span>Menu</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="pesanan.php" class="nav-link d-flex align-items-center justify-content-between py-3 px-3 rounded-3 mb-1 <?= ($current_page == 'pesanan.php') ? 'active' : '' ?>">
                    <div class="d-flex align-items-center">
                        <i class='bx bx-cart fs-5 me-2'></i>
                        <span>Pesanan</span>
                    </div>
                    <?php if ($pending_orders > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?= $pending_orders ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="users.php" class="nav-link d-flex align-items-center py-3 px-3 rounded-3 mb-1 <?= ($current_page == 'users.php') ? 'active' : '' ?>">
                    <i class='bx bx-user fs-5 me-2'></i>
                    <span>Pengguna</span>
                </a>
            </li>
            
            <li class="nav-item mt-4 pt-2 border-top border-secondary">
                <a href="../logout.php" class="nav-link d-flex align-items-center py-3 px-3 rounded-3 text-danger">
                    <i class='bx bx-log-out fs-5 me-2'></i>
                    <span>Keluar</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Sidebar footer -->
    <div class="sidebar-footer p-3 text-center border-top border-secondary">
        <small class="text-muted">v1.0.0</small>
    </div>
</aside>
