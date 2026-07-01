<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));

// Determine active page
$page_param = $_GET['page'] ?? '';

// Get stok menipis count for badge
$db = getDB();
$stok_menipis = $db->query("SELECT COUNT(*) as c FROM barang WHERE stok <= stok_minimum")->fetch_assoc()['c'];

// Determine base path from current location
$depth = ($current_dir === 'pages') ? '../' : '';
?>
<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <!-- Header -->
    <div class="sidebar-header">
        <a href="<?= $depth ?>index.php" class="sidebar-logo">
            <img src="<?= $depth ?>assets/img/cat-hi.png" class="logo-img" alt="Logo">
            <div class="logo-text">
                <h2>Stock Gudang</h2>
                <span>Manajemen Stok v1.0</span>
            </div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <span class="nav-section-title">Utama</span>

        <a href="<?= $depth ?>index.php?page=dashboard"
           class="nav-item <?= ($page_param === 'dashboard' || $page_param === '') ? 'active' : '' ?>">
            <i class="fas fa-chart-pie"></i>
            <span>Dashboard</span>
        </a>

        <span class="nav-section-title">Manajemen Data</span>

        <a href="<?= $depth ?>index.php?page=barang"
           class="nav-item <?= $page_param === 'barang' ? 'active' : '' ?>">
            <i class="fas fa-box"></i>
            <span>Data Barang</span>
            <?php if ($stok_menipis > 0): ?>
            <span class="nav-badge"><?= $stok_menipis ?></span>
            <?php endif; ?>
        </a>

        <a href="<?= $depth ?>index.php?page=kategori"
           class="nav-item <?= $page_param === 'kategori' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i>
            <span>Kategori</span>
        </a>

        <a href="<?= $depth ?>index.php?page=supplier"
           class="nav-item <?= $page_param === 'supplier' ? 'active' : '' ?>">
            <i class="fas fa-truck"></i>
            <span>Supplier</span>
        </a>

        <span class="nav-section-title">Transaksi</span>

        <a href="<?= $depth ?>index.php?page=barang_masuk"
           class="nav-item <?= $page_param === 'barang_masuk' ? 'active' : '' ?>">
            <i class="fas fa-arrow-down-to-bracket"></i>
            <span>Barang Masuk</span>
        </a>

        <a href="<?= $depth ?>index.php?page=barang_keluar"
           class="nav-item <?= $page_param === 'barang_keluar' ? 'active' : '' ?>">
            <i class="fas fa-arrow-up-from-bracket"></i>
            <span>Barang Keluar</span>
        </a>

        <a href="<?= $depth ?>index.php?page=stok_opname"
           class="nav-item <?= $page_param === 'stok_opname' ? 'active' : '' ?>">
            <i class="fas fa-clipboard-check"></i>
            <span>Stok Opname</span>
        </a>

        <span class="nav-section-title">Lainnya</span>

        <a href="<?= $depth ?>index.php?page=laporan"
           class="nav-item <?= $page_param === 'laporan' ? 'active' : '' ?>">
            <i class="fas fa-file-chart-column"></i>
            <span>Laporan</span>
        </a>

        <?php if (isAdmin()): ?>
        <a href="<?= $depth ?>index.php?page=user"
           class="nav-item <?= $page_param === 'user' ? 'active' : '' ?>">
            <i class="fas fa-users-gear"></i>
            <span>Manajemen User</span>
        </a>
        <?php endif; ?>
    </nav>

    <!-- Cat Mascot -->
    <div class="sidebar-mascot">
        <img src="<?= $depth ?>assets/img/cat-laptop.png" alt="Mascot">
        <div class="mascot-speech">
            Stok rapi, gudang aman! Jangan lupa cek barang menipis ya~ 🐾
        </div>
    </div>

    <!-- Decorative Paws -->
    <div class="sidebar-paws">
        🐾 🐾 🐾 🐾 🐾
    </div>

    <!-- Footer / User Profile -->
    <div class="sidebar-footer">
        <div class="user-profile-card">
            <div class="user-avatar">
                <?php if (!empty($_SESSION['foto']) && file_exists('../assets/img/users/' . $_SESSION['foto'])): ?>
                    <img src="<?= $depth ?>assets/img/users/<?= htmlspecialchars($_SESSION['foto']) ?>" alt="Foto">
                <?php else: ?>
                    <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></div>
                <div class="user-role"><?= ucfirst($_SESSION['role'] ?? '') ?></div>
            </div>
            <a href="<?= $depth ?>auth/logout.php" class="btn-logout" title="Logout">
                <i class="fas fa-right-from-bracket"></i>
            </a>
        </div>
    </div>
</aside>
