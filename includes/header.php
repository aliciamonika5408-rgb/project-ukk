<?php
// includes/header.php
// Must be called after requireLogin()
$page_param   = $_GET['page'] ?? 'dashboard';
$page_titles  = [
    'dashboard'    => ['Dashboard',           'Selamat datang kembali!'],
    'barang'       => ['Data Barang',          'Kelola data inventaris barang gudang'],
    'kategori'     => ['Kategori Barang',      'Kelola kategori pengelompokan barang'],
    'supplier'     => ['Supplier',             'Kelola data supplier & pemasok barang'],
    'barang_masuk' => ['Barang Masuk',         'Catat transaksi penerimaan barang'],
    'barang_keluar'=> ['Barang Keluar',        'Catat transaksi pengeluaran barang'],
    'stok_opname'  => ['Stok Opname',          'Pencocokan stok sistem dengan fisik'],
    'laporan'      => ['Laporan',              'Cetak & export laporan stok gudang'],
    'user'         => ['Manajemen User',        'Kelola akun pengguna sistem'],
];
$title_info = $page_titles[$page_param] ?? ['Halaman', ''];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title_info[0] ?> — Stock Gudang</title>
    <meta name="description" content="<?= $title_info[1] ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
</head>
<body class="page-<?= $page_param ?>">

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display:none">
    <div class="spinner"></div>
    <span class="loading-text">Memproses data...</span>
</div>

<div class="app-layout">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- Navbar -->
        <header class="navbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
                <i class="fas fa-bars"></i>
            </button>

            <div class="search-bar">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" id="globalSearch" placeholder="Cari sesuatu..." autocomplete="off">
            </div>

            <div class="navbar-right">
                <?php
                $db = getDB();
                $stok_menipis = $db->query("SELECT COUNT(*) as c FROM barang WHERE stok <= stok_minimum")->fetch_assoc()['c'];
                ?>
                <button class="navbar-btn" id="notifBtn" title="Notifikasi" onclick="toggleNotifPanel()">
                    <i class="fas fa-bell"></i>
                    <?php if ($stok_menipis > 0): ?>
                    <span class="notification-badge"></span>
                    <?php endif; ?>
                </button>

                <div class="navbar-user" onclick="window.location='index.php?page=<?= isAdmin() ? 'user' : 'dashboard' ?>'">
                    <div class="user-avatar">
                        <?php if (!empty($_SESSION['foto']) && file_exists('assets/img/users/' . $_SESSION['foto'])): ?>
                            <img src="assets/img/users/<?= htmlspecialchars($_SESSION['foto']) ?>" alt="Foto">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <span class="user-name"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </header>

        <!-- Notification Panel -->
        <?php if ($stok_menipis > 0):
            $stok_items = $db->query("SELECT nama_barang, stok, stok_minimum FROM barang WHERE stok <= stok_minimum ORDER BY stok ASC LIMIT 5");
        ?>
        <div id="notifPanel" style="display:none; position:fixed; top:78px; right:20px; z-index:1500; width:320px;">
            <div class="card" style="box-shadow: var(--shadow-lg);">
                <div class="card-header" style="padding:14px 18px;">
                    <span class="card-title" style="font-size:13px;"><i class="fas fa-bell"></i> Notifikasi Stok</span>
                    <button onclick="toggleNotifPanel()" style="background:none;border:none;cursor:pointer;color:var(--text-light)"><i class="fas fa-xmark"></i></button>
                </div>
                <div style="padding:10px 0; max-height:280px; overflow-y:auto;">
                    <?php while($item = $stok_items->fetch_assoc()): ?>
                    <div style="display:flex;align-items:center;gap:10px;padding:10px 18px;border-bottom:1px solid var(--border-light);">
                        <div style="width:36px;height:36px;background:var(--warning-bg);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-triangle-exclamation" style="color:var(--warning);font-size:15px;"></i>
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--text-dark);"><?= htmlspecialchars($item['nama_barang']) ?></div>
                            <div style="font-size:11px;color:var(--text-light);">Stok: <strong style="color:var(--warning)"><?= $item['stok'] ?></strong> / Min: <?= $item['stok_minimum'] ?></div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php if ($stok_menipis > 5): ?>
                    <div style="text-align:center;padding:10px;font-size:12px;color:var(--text-light);">+<?= $stok_menipis - 5 ?> barang lainnya</div>
                    <?php endif; ?>
                </div>
                <div style="padding:12px 18px;border-top:1px solid var(--border-light);">
                    <a href="index.php?page=barang&filter=menipis" style="font-size:12px;font-weight:600;color:var(--primary-dark);">Lihat Semua Barang Menipis →</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Page Content -->
        <main class="page-content">
            <div class="page-header">
                <div class="page-title">
                    <div class="breadcrumb">
                        <a href="index.php?page=dashboard"><i class="fas fa-house"></i></a>
                        <i class="fas fa-chevron-right"></i>
                        <span><?= $title_info[0] ?></span>
                    </div>
                    <h1><?= $title_info[0] ?></h1>
                    <p><?= $title_info[1] ?></p>
                </div>
