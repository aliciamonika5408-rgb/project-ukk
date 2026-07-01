<?php
require_once '../config/database.php';
requireLogin();
header('Content-Type: application/json');
$db = getDB();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'tambah':
        $no_opname  = clean($_POST['no_opname']   ?? '');
        $id_brg     = (int)($_POST['id_barang']   ?? 0);
        $stok_sistem= (int)($_POST['stok_sistem'] ?? 0);
        $stok_fisik = (int)($_POST['stok_fisik']  ?? 0);
        $selisih    = (int)($_POST['selisih']      ?? 0);
        $ket        = clean($_POST['keterangan']  ?? '');
        $tanggal    = clean($_POST['tanggal']     ?? date('Y-m-d'));
        $id_user    = (int)($_POST['id_user']     ?? 0);

        if (!$id_brg) { echo json_encode(['success'=>false,'message'=>'Barang wajib dipilih!']); exit; }

        $db->begin_transaction();
        try {
            $id_user_sql = $id_user > 0 ? $id_user : 'NULL';
            $ok1 = $db->query("INSERT INTO stok_opname (no_opname,id_barang,stok_sistem,stok_fisik,selisih,keterangan,tanggal,id_user) VALUES ('$no_opname',$id_brg,$stok_sistem,$stok_fisik,$selisih,'$ket','$tanggal',$id_user_sql)");
            if (!$ok1) throw new Exception($db->error);
            // Update actual stock to match physical count
            $ok2 = $db->query("UPDATE barang SET stok = $stok_fisik WHERE id=$id_brg");
            if (!$ok2) throw new Exception($db->error);
            $db->commit();
            echo json_encode(['success'=>true,'message'=>'Stok opname disimpan! Stok sistem diperbarui ke stok fisik.']);
        } catch (Exception $e) {
            $db->rollback();
            echo json_encode(['success'=>false,'message'=>'Gagal: '.$e->getMessage()]);
        }
        break;

    case 'hapus':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success'=>false,'message'=>'ID tidak valid!']); exit; }
        if ($db->query("DELETE FROM stok_opname WHERE id=$id")) {
            echo json_encode(['success'=>true,'message'=>'Data opname berhasil dihapus!']);
        } else {
            echo json_encode(['success'=>false,'message'=>'Gagal menghapus!']);
        }
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Aksi tidak dikenal!']);
}
