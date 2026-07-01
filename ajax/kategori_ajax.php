<?php
require_once '../config/database.php';
requireLogin();
header('Content-Type: application/json');
$db = getDB();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'tambah':
        $kode = clean($_POST['kode_kategori']  ?? '');
        $nama = clean($_POST['nama_kategori']  ?? '');
        $desk = clean($_POST['deskripsi']      ?? '');
        if (empty($kode) || empty($nama)) { echo json_encode(['success'=>false,'message'=>'Kode dan nama wajib diisi!']); exit; }
        $stmt = $db->prepare("INSERT INTO kategori (kode_kategori,nama_kategori,deskripsi) VALUES (?,?,?)");
        $stmt->bind_param('sss',$kode,$nama,$desk);
        if ($stmt->execute()) {
            echo json_encode(['success'=>true,'message'=>'Kategori berhasil ditambahkan!']);
        } else {
            echo json_encode(['success'=>false,'message'=>$db->errno===1062?'Kode sudah digunakan!':'Gagal: '.$db->error]);
        }
        break;
    case 'edit':
        $id   = (int)($_POST['id'] ?? 0);
        $kode = clean($_POST['kode_kategori'] ?? '');
        $nama = clean($_POST['nama_kategori'] ?? '');
        $desk = clean($_POST['deskripsi']     ?? '');
        if (!$id || !$kode || !$nama) { echo json_encode(['success'=>false,'message'=>'Data tidak valid!']); exit; }
        if ($db->query("UPDATE kategori SET kode_kategori='$kode',nama_kategori='$nama',deskripsi='$desk' WHERE id=$id")) {
            echo json_encode(['success'=>true,'message'=>'Kategori berhasil diperbarui!']);
        } else {
            echo json_encode(['success'=>false,'message'=>'Gagal: '.$db->error]);
        }
        break;
    case 'hapus':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success'=>false,'message'=>'ID tidak valid!']); exit; }
        // Check if used
        $used = $db->query("SELECT COUNT(*) as c FROM barang WHERE id_kategori=$id")->fetch_assoc()['c'];
        if ($used > 0) { echo json_encode(['success'=>false,'message'=>"Kategori digunakan oleh $used barang, tidak bisa dihapus!"]); exit; }
        if ($db->query("DELETE FROM kategori WHERE id=$id")) {
            echo json_encode(['success'=>true,'message'=>'Kategori berhasil dihapus!']);
        } else {
            echo json_encode(['success'=>false,'message'=>'Gagal menghapus!']);
        }
        break;
    default:
        echo json_encode(['success'=>false,'message'=>'Aksi tidak dikenal!']);
}
?>
