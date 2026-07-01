<?php
// pages/user.php
if (!isAdmin()) { header('Location: ../index.php?page=dashboard'); exit(); }
$db   = getDB();
$rows = $db->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<div style="margin-left:auto;">
    <button class="btn btn-primary" onclick="openModal('modalTambah')">
        <i class="fas fa-plus"></i> Tambah User
    </button>
</div>

<div class="card">
    <div class="table-toolbar">
        <div class="table-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" placeholder="Cari user..." oninput="filterTbl(this,'tblUser')">
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="tblUser">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Foto</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($row=$rows->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <?php if ($row['foto'] && file_exists("assets/img/users/{$row['foto']}")): ?>
                            <img src="assets/img/users/<?= htmlspecialchars($row['foto']) ?>" class="item-photo" style="border-radius:50%;" alt="">
                        <?php else: ?>
                            <div class="user-avatar" style="width:42px;height:42px;font-size:16px;">
                                <?= strtoupper(substr($row['nama'],0,1)) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:600;"><?= htmlspecialchars($row['nama']) ?></td>
                    <td>
                        <span class="badge badge-pink">@<?= htmlspecialchars($row['username']) ?></span>
                    </td>
                    <td>
                        <?php if ($row['role'] === 'admin'): ?>
                            <span class="badge badge-red"><i class="fas fa-crown"></i> Admin</span>
                        <?php elseif ($row['role'] === 'gudang'): ?>
                            <span class="badge badge-blue">Gudang</span>
                        <?php else: ?>
                            <span class="badge badge-yellow">Viewer</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:var(--text-light);"><?= formatDate($row['created_at']) ?></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-secondary btn-icon" onclick='editUser(<?= json_encode($row) ?>)' title="Edit">
                                <i class="fas fa-pen"></i>
                            </button>
                            <?php if ($row['id'] != $_SESSION['user_id']): ?>
                            <button class="btn btn-danger btn-icon" onclick="hapusUser(<?= $row['id'] ?>,'<?= addslashes($row['nama']) ?>')" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah" style="display:none;">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-user-plus"></i> Tambah User</span>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fas fa-xmark"></i></button>
        </div>
        <form onsubmit="submitUser(event,'tambah')" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" required placeholder="Nama lengkap">
                    </div>
                    <div class="form-group">
                        <label class="required">Username</label>
                        <input type="text" class="form-control" name="username" required placeholder="username">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Password</label>
                        <input type="password" class="form-control" name="password" required placeholder="Min. 6 karakter">
                    </div>
                    <div class="form-group">
                        <label class="required">Role</label>
                        <select class="form-control" name="role" required>
                            <option value="gudang">Gudang</option>
                            <option value="admin">Admin</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Foto Profil</label>
                    <input type="file" class="form-control" name="foto" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambah')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="modalEdit" style="display:none;">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-user-pen"></i> Edit User</span>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fas fa-xmark"></i></button>
        </div>
        <form onsubmit="submitUser(event,'edit')" enctype="multipart/form-data">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" id="edit_nama" required>
                    </div>
                    <div class="form-group">
                        <label class="required">Username</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Password Baru <span style="color:var(--text-light);font-weight:400;">(kosongkan jika tidak ganti)</span></label>
                        <input type="password" class="form-control" name="password" placeholder="Password baru">
                    </div>
                    <div class="form-group">
                        <label class="required">Role</label>
                        <select class="form-control" name="role" id="edit_role" required>
                            <option value="gudang">Gudang</option>
                            <option value="admin">Admin</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Foto Profil (kosongkan jika tidak ganti)</label>
                    <input type="file" class="form-control" name="foto" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function editUser(d) {
    document.getElementById('edit_id').value       = d.id;
    document.getElementById('edit_nama').value     = d.nama;
    document.getElementById('edit_username').value = d.username;
    document.getElementById('edit_role').value     = d.role;
    openModal('modalEdit');
}
function submitUser(e, type) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('action', type);
    showLoading();
    fetch('ajax/user_ajax.php',{method:'POST',body:fd})
        .then(r=>r.json()).then(res=>{
            hideLoading(); showToast(res.message,res.success?'success':'error');
            if(res.success){ closeModal('modalTambah'); closeModal('modalEdit'); setTimeout(()=>location.reload(),800); }
        }).catch(()=>{ hideLoading(); showToast('Gagal','error'); });
}
function hapusUser(id, nama) {
    if(!confirm(`Hapus user "${nama}"?`)) return;
    showLoading();
    fetch('ajax/user_ajax.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=hapus&id=${id}`})
        .then(r=>r.json()).then(res=>{ hideLoading(); showToast(res.message,res.success?'success':'error'); if(res.success) setTimeout(()=>location.reload(),800); })
        .catch(()=>{ hideLoading(); showToast('Gagal','error'); });
}
</script>
