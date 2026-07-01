<?php
// includes/header_close.php
$page_param = $_GET['page'] ?? 'dashboard';
$btn_labels = [
    'barang'       => 'Tambah Barang',
    'kategori'     => 'Tambah Kategori',
    'supplier'     => 'Tambah Supplier',
    'barang_masuk' => 'Tambah Barang Masuk',
    'barang_keluar'=> 'Tambah Barang Keluar',
    'stok_opname'  => 'Tambah Stok Opname',
    'user'         => 'Tambah User',
];

if (isset($btn_labels[$page_param])): ?>
    <div style="margin-left:auto;">
        <button class="btn btn-primary" onclick="openModal('modalTambah')">
            <i class="fas fa-plus"></i> <?= $btn_labels[$page_param] ?>
        </button>
    </div>
<?php endif; ?>
        </div><!-- end page-header -->

