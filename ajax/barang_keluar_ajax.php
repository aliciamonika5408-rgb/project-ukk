<?php
require_once '../config/database.php';
requireLogin();
header('Content-Type: application/json');
$db = getDB();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'tambah':
        $no_trx  = clean($_POST['no_transaksi'] ?? '');
        $id_brg  = (int)($_POST['id_barang']    ?? 0);
        $jumlah  = (int)($_POST['jumlah']       ?? 0);
        $harga   = (float)($_POST['harga_satuan'] ?? 0);
        $tanggal = clean($_POST['tanggal']      ?? date('Y-m-d'));
        $tujuan  = clean($_POST['tujuan']       ?? '');
        $ket     = clean($_POST['keterangan']   ?? '');
        $id_user = (int)($_POST['id_user']      ?? 0);

        if (!$id_brg || $jumlah <= 0) {
            echo json_encode(['success'=>false,'message'=>'Barang dan jumlah wajib diisi!']); exit;
        }

        // Check stock
        $stok_row = $db->query("SELECT stok FROM barang WHERE id=$id_brg")->fetch_assoc();
        if (!$stok_row) { echo json_encode(['success'=>false,'message'=>'Barang tidak ditemukan!']); exit; }
        if ($stok_row['stok'] < $jumlah) {
            echo json_encode(['success'=>false,'message'=>"Stok tidak cukup! Tersedia: {$stok_row['stok']}"]); exit;
        }

        $db->begin_transaction();
        try {
            $id_user_sql = $id_user > 0 ? $id_user : 'NULL';
            $ok1 = $db->query("INSERT INTO barang_keluar (no_transaksi,id_barang,jumlah,harga_satuan,tanggal,tujuan,keterangan,id_user) VALUES ('$no_trx',$id_brg,$jumlah,$harga,'$tanggal','$tujuan','$ket',$id_user_sql)");
            if (!$ok1) throw new Exception($db->error);
            $ok2 = $db->query("UPDATE barang SET stok = stok - $jumlah WHERE id=$id_brg");
            if (!$ok2) throw new Exception($db->error);
            $db->commit();
            echo json_encode(['success'=>true,'message'=>'Barang keluar berhasil dicatat! Stok dikurangi.']);
        } catch (Exception $e) {
            $db->rollback();
            echo json_encode(['success'=>false,'message'=>'Gagal: '.$e->getMessage()]);
        }
        break;

    case 'hapus':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success'=>false,'message'=>'ID tidak valid!']); exit; }
        $item = $db->query("SELECT id_barang,jumlah FROM barang_keluar WHERE id=$id")->fetch_assoc();
        if (!$item) { echo json_encode(['success'=>false,'message'=>'Data tidak ditemukan!']); exit; }
        $db->begin_transaction();
        try {
            $db->query("DELETE FROM barang_keluar WHERE id=$id");
            $db->query("UPDATE barang SET stok = stok + {$item['jumlah']} WHERE id={$item['id_barang']}");
            $db->commit();
            echo json_encode(['success'=>true,'message'=>'Transaksi dihapus dan stok dikembalikan!']);
        } catch (Exception $e) {
            $db->rollback();
            echo json_encode(['success'=>false,'message'=>'Gagal: '.$e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Aksi tidak dikenal!']);
}
?>
