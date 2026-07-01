<?php
require_once '../config/database.php';
requireLogin();
header('Content-Type: application/json');

$db     = getDB();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'tambah':
        $kode       = clean($_POST['kode_barang'] ?? '');
        $nama       = clean($_POST['nama_barang']  ?? '');
        $id_kat     = (int)($_POST['id_kategori'] ?? 0);
        $satuan     = clean($_POST['satuan']       ?? 'pcs');
        $h_beli     = (float)($_POST['harga_beli'] ?? 0);
        $h_jual     = (float)($_POST['harga_jual'] ?? 0);
        $stok       = (int)($_POST['stok']         ?? 0);
        $stok_min   = (int)($_POST['stok_minimum'] ?? 5);
        $foto       = '';

        if (empty($kode) || empty($nama)) {
            echo json_encode(['success'=>false,'message'=>'Kode dan nama barang wajib diisi!']); exit;
        }

        // Handle foto upload
        if (!empty($_FILES['foto']['name'])) {
            $ext    = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (!in_array($ext, $allowed)) {
                echo json_encode(['success'=>false,'message'=>'Format foto tidak valid!']); exit;
            }
            $dir = '../assets/img/barang/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $foto = uniqid('brg_') . '.' . $ext;
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $foto)) {
                $foto = '';
            }
        }

        $id_kat_sql = $id_kat > 0 ? $id_kat : 'NULL';
        $stmt = $db->prepare("INSERT INTO barang (kode_barang,nama_barang,id_kategori,satuan,harga_beli,harga_jual,stok,stok_minimum,foto) VALUES (?,?,?,?,?,?,?,?,?)");
        $id_kat_bind = $id_kat > 0 ? $id_kat : null;
        $stmt->bind_param('ssissddis', $kode,$nama,$id_kat_bind,$satuan,$h_beli,$h_jual,$stok,$stok_min,$foto);
        if ($stmt->execute()) {
            echo json_encode(['success'=>true,'message'=>'Barang berhasil ditambahkan!']);
        } else {
            // Check duplicate
            if ($db->errno === 1062) {
                echo json_encode(['success'=>false,'message'=>'Kode barang sudah digunakan!']);
            } else {
                echo json_encode(['success'=>false,'message'=>'Gagal menyimpan: '.$db->error]);
            }
        }
        break;

    case 'edit':
        $id         = (int)($_POST['id']           ?? 0);
        $kode       = clean($_POST['kode_barang']  ?? '');
        $nama       = clean($_POST['nama_barang']  ?? '');
        $id_kat     = (int)($_POST['id_kategori']  ?? 0);
        $satuan     = clean($_POST['satuan']        ?? 'pcs');
        $h_beli     = (float)($_POST['harga_beli'] ?? 0);
        $h_jual     = (float)($_POST['harga_jual'] ?? 0);
        $stok_min   = (int)($_POST['stok_minimum'] ?? 5);

        if ($id <= 0 || empty($kode) || empty($nama)) {
            echo json_encode(['success'=>false,'message'=>'Data tidak valid!']); exit;
        }

        // Handle new foto
        $foto_set = '';
        if (!empty($_FILES['foto']['name'])) {
            $ext     = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $allowed)) {
                $dir = '../assets/img/barang/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $foto_name = uniqid('brg_') . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $foto_name)) {
                    $foto_set = ", foto='$foto_name'";
                }
            }
        }

        $id_kat_sql = $id_kat > 0 ? $id_kat : 'NULL';
        $sql = "UPDATE barang SET kode_barang='$kode',nama_barang='$nama',id_kategori=$id_kat_sql,satuan='$satuan',harga_beli=$h_beli,harga_jual=$h_jual,stok_minimum=$stok_min $foto_set WHERE id=$id";
        if ($db->query($sql)) {
            echo json_encode(['success'=>true,'message'=>'Barang berhasil diperbarui!']);
        } else {
            echo json_encode(['success'=>false,'message'=>'Gagal update: '.$db->error]);
        }
        break;

    case 'hapus':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'ID tidak valid!']); exit; }
        // Get foto to delete
        $res  = $db->query("SELECT foto FROM barang WHERE id=$id");
        $item = $res ? $res->fetch_assoc() : null;
        if ($db->query("DELETE FROM barang WHERE id=$id")) {
            if ($item && $item['foto'] && file_exists("../assets/img/barang/{$item['foto']}")) {
                unlink("../assets/img/barang/{$item['foto']}");
            }
            echo json_encode(['success'=>true,'message'=>'Barang berhasil dihapus!']);
        } else {
            echo json_encode(['success'=>false,'message'=>'Gagal menghapus barang!']);
        }
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Aksi tidak dikenal!']);
}
?>
