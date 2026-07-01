<?php
require_once '../config/database.php';
requireLogin();
header('Content-Type: application/json');
if (!isAdmin()) { echo json_encode(['success'=>false,'message'=>'Akses ditolak!']); exit; }
$db = getDB();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'tambah':
        $nama     = clean($_POST['nama']     ?? '');
        $username = clean($_POST['username'] ?? '');
        $password = $_POST['password']       ?? '';
        $role     = clean($_POST['role']     ?? 'gudang');
        $foto     = '';

        if (!$nama||!$username||strlen($password)<6) {
            echo json_encode(['success'=>false,'message'=>'Nama, username, dan password (min.6) wajib diisi!']); exit;
        }
        if (!in_array($role,['admin','gudang','viewer'])) $role='gudang';

        // Foto upload
        if (!empty($_FILES['foto']['name'])) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'],PATHINFO_EXTENSION));
            if (in_array($ext,['jpg','jpeg','png','gif','webp'])) {
                $dir = '../assets/img/users/';
                if (!is_dir($dir)) mkdir($dir,0777,true);
                $foto = uniqid('usr_').'.'.$ext;
                if (!move_uploaded_file($_FILES['foto']['tmp_name'],$dir.$foto)) $foto='';
            }
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username,password,nama,role,foto) VALUES (?,?,?,?,?)");
        $stmt->bind_param('sssss',$username,$hash,$nama,$role,$foto);
        if ($stmt->execute()) {
            echo json_encode(['success'=>true,'message'=>'User berhasil ditambahkan!']);
        } else {
            echo json_encode(['success'=>false,'message'=>$db->errno===1062?'Username sudah digunakan!':'Gagal: '.$db->error]);
        }
        break;

    case 'edit':
        $id       = (int)($_POST['id'] ?? 0);
        $nama     = clean($_POST['nama']     ?? '');
        $username = clean($_POST['username'] ?? '');
        $password = $_POST['password']       ?? '';
        $role     = clean($_POST['role']     ?? 'gudang');

        if (!$id||!$nama||!$username) { echo json_encode(['success'=>false,'message'=>'Data tidak valid!']); exit; }
        if (!in_array($role,['admin','gudang','viewer'])) $role='gudang';

        $pw_set = '';
        if (strlen($password) >= 6) {
            $hash   = password_hash($password, PASSWORD_DEFAULT);
            $pw_set = ",password='$hash'";
        }

        $foto_set = '';
        if (!empty($_FILES['foto']['name'])) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'],PATHINFO_EXTENSION));
            if (in_array($ext,['jpg','jpeg','png','gif','webp'])) {
                $dir = '../assets/img/users/';
                if (!is_dir($dir)) mkdir($dir,0777,true);
                $fn = uniqid('usr_').'.'.$ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'],$dir.$fn)) $foto_set=",foto='$fn'";
            }
        }

        if ($db->query("UPDATE users SET nama='$nama',username='$username',role='$role' $pw_set $foto_set WHERE id=$id")) {
            echo json_encode(['success'=>true,'message'=>'User berhasil diperbarui!']);
        } else {
            echo json_encode(['success'=>false,'message'=>'Gagal: '.$db->error]);
        }
        break;

    case 'hapus':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success'=>false,'message'=>'ID tidak valid!']); exit; }
        if ($id == $_SESSION['user_id']) { echo json_encode(['success'=>false,'message'=>'Tidak dapat menghapus akun sendiri!']); exit; }
        if ($db->query("DELETE FROM users WHERE id=$id")) {
            echo json_encode(['success'=>true,'message'=>'User berhasil dihapus!']);
        } else {
            echo json_encode(['success'=>false,'message'=>'Gagal menghapus!']);
        }
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Aksi tidak dikenal!']);
}
?>
