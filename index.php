<?php
// index.php — Main Router
require_once 'config/database.php';
requireLogin();

$page = $_GET['page'] ?? 'dashboard';
$allowed_pages = [
    'dashboard', 'barang', 'kategori', 'supplier',
    'barang_masuk', 'barang_keluar', 'stok_opname',
    'laporan', 'user'
];

// Admin-only pages
$admin_only = ['user'];
if (in_array($page, $admin_only) && !isAdmin()) {
    $page = 'dashboard';
}

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

include 'includes/header.php';
include "includes/header_close.php";
include "pages/{$page}.php";
include 'includes/footer.php';
?>
