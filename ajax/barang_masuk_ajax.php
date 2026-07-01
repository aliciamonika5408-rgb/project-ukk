<?php
require_once '../config/database.php';
requireLogin();
header('Content-Type: application/json');
$db = getDB();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'tambah':
        $no_trx    = clean($_POST['no_transaksi'] ?? '');
        $id_sup    = (int)($_POST['id_supplier']  ?? 0);
        $id_brg    = (int)($_POST['id_barang']    ?? 0);
        $jumlah    = (int)($_POST['jumlah']       ?? 0);
        $harga     = (float)($_POST['harga_satuan'] ?? 0);
        $tanggal   = clean($_POST['tanggal']      ?? date('Y-m-d'));
        $ket       = clean($_POST['keterangan']   ?? '');
        $id_user   = (int)($_POST['id_user']      ?? 0);

        if (!$id_brg || $jumlah <= 0) {
            echo json_encode(['success'=>false,'message'=>'Barang dan jumlah wajib diisi!']); exit;
        }

        $db->begin_transaction();
        try {
            $id_sup_sql = $id_sup > 0 ? $id_sup : 'NULL';
            $id_user_sql = $id_user > 0 ? $id_user : 'NULL';
            $ok1 = $db->query("INSERT INTO barang_masuk (no_transaksi,id_supplier,id_barang,jumlah,harga_satuan,tanggal,keterangan,id_user) VALUES ('$no_trx',$id_sup_sql,$id_brg,$jumlah,$harga,'$tanggal','$ket',$id_user_sql)");
            if (!$ok1) throw new Exception($db->error);
            $ok2 = $db->query("UPDATE barang SET stok = stok + $jumlah WHERE id=$id_brg");
            if (!$ok2) throw new Exception($db->error);
            $db->commit();
            echo json_encode(['success'=>true,'message'=>'Barang masuk berhasil dicatat! Stok diperbarui.']);
        } catch (Exception $e) {
            $db->rollback();
            echo json_encode(['success'=>false,'message'=>'Gagal: '.$e->getMessage()]);
        }
        break;

    case 'hapus':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success'=>false,'message'=>'ID tidak valid!']); exit; }
        $item = $db->query("SELECT id_barang,jumlah FROM barang_masuk WHERE id=$id")->fetch_assoc();
        if (!$item) { echo json_encode(['success'=>false,'message'=>'Data tidak ditemukan!']); exit; }
        $db->begin_transaction();
        try {
            $db->query("DELETE FROM barang_masuk WHERE id=$id");
            $db->query("UPDATE barang SET stok = stok - {$item['jumlah']} WHERE id={$item['id_barang']}");
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
