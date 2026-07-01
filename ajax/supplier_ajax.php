<?php
require_once '../config/database.php';
requireLogin();
header('Content-Type: application/json');
$db = getDB();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'tambah':
        $kode  = clean($_POST['kode_supplier']  ?? '');
        $nama  = clean($_POST['nama_supplier']  ?? '');
        $alamat= clean($_POST['alamat']         ?? '');
        $telp  = clean($_POST['telepon']        ?? '');
        $email = clean($_POST['email']          ?? '');
        if (empty($kode)||empty($nama)) { echo json_encode(['success'=>false,'message'=>'Kode dan nama wajib diisi!']); exit; }
        $stmt = $db->prepare("INSERT INTO supplier (kode_supplier,nama_supplier,alamat,telepon,email) VALUES (?,?,?,?,?)");
        $stmt->bind_param('sssss',$kode,$nama,$alamat,$telp,$email);
        if ($stmt->execute()) {
            echo json_encode(['success'=>true,'message'=>'Supplier berhasil ditambahkan!']);
        } else {
            echo json_encode(['success'=>false,'message'=>$db->errno===1062?'Kode sudah digunakan!':'Gagal: '.$db->error]);
        }
        break;
    case 'edit':
        $id    = (int)($_POST['id'] ?? 0);
        $kode  = clean($_POST['kode_supplier']  ?? '');
        $nama  = clean($_POST['nama_supplier']  ?? '');
        $alamat= clean($_POST['alamat']         ?? '');
        $telp  = clean($_POST['telepon']        ?? '');
        $email = clean($_POST['email']          ?? '');
        if (!$id||!$kode||!$nama) { echo json_encode(['success'=>false,'message'=>'Data tidak valid!']); exit; }
        if ($db->query("UPDATE supplier SET kode_supplier='$kode',nama_supplier='$nama',alamat='$alamat',telepon='$telp',email='$email' WHERE id=$id")) {
            echo json_encode(['success'=>true,'message'=>'Supplier berhasil diperbarui!']);
        } else {
            echo json_encode(['success'=>false,'message'=>'Gagal: '.$db->error]);
        }
        break;
    case 'hapus':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success'=>false,'message'=>'ID tidak valid!']); exit; }
        if ($db->query("DELETE FROM supplier WHERE id=$id")) {
            echo json_encode(['success'=>true,'message'=>'Supplier berhasil dihapus!']);
        } else {
            echo json_encode(['success'=>false,'message'=>'Gagal menghapus!']);
        }
        break;
    default:
        echo json_encode(['success'=>false,'message'=>'Aksi tidak dikenal!']);
}
?>
