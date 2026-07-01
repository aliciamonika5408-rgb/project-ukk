<?php
// pages/supplier.php
$db   = getDB();
$rows = $db->query("
    SELECT s.*, COUNT(bm.id) as total_transaksi
    FROM supplier s
    LEFT JOIN barang_masuk bm ON s.id = bm.id_supplier
    GROUP BY s.id ORDER BY s.created_at DESC
");
?>
<div style="margin-left:auto;">
    <button class="btn btn-primary" onclick="openModal('modalTambah')">
        <i class="fas fa-plus"></i> Tambah Supplier
    </button>
</div>

<div class="card">
    <div class="table-toolbar">
        <div class="table-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" placeholder="Cari supplier..." oninput="filterTbl(this,'tblSup')">
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="tblSup">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kode</th>
                    <th>Nama Supplier</th>
                    <th>Alamat</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Total Transaksi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($row=$rows->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><span class="badge badge-pink"><?= htmlspecialchars($row['kode_supplier']) ?></span></td>
                    <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($row['nama_supplier']) ?></div>
                    </td>
                    <td style="font-size:12px;max-width:160px;"><?= htmlspecialchars($row['alamat'] ?: '-') ?></td>
                    <td>
                        <a href="tel:<?= htmlspecialchars($row['telepon']) ?>" style="color:var(--primary-dark);font-weight:500;font-size:12px;">
                            <?= htmlspecialchars($row['telepon'] ?: '-') ?>
                        </a>
                    </td>
                    <td style="font-size:12px;">
                        <a href="mailto:<?= htmlspecialchars($row['email']) ?>" style="color:var(--info);">
                            <?= htmlspecialchars($row['email'] ?: '-') ?>
                        </a>
                    </td>
                    <td><span class="badge badge-green"><?= $row['total_transaksi'] ?> transaksi</span></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-secondary btn-icon" onclick='editSupplier(<?= json_encode($row) ?>)' title="Edit">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-icon" onclick="hapusSupplier(<?= $row['id'] ?>,'<?= addslashes($row['nama_supplier']) ?>')" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php if ($no === 1): ?>
        <div class="empty-state">
            <img src="assets/img/cat-sleep.png" class="empty-cat" alt="Empty">
            <h3>Belum Ada Supplier</h3>
            <p>Tambahkan data supplier untuk mencatat transaksi barang masuk.</p>
            <button class="btn btn-primary" onclick="openModal('modalTambah')"><i class="fas fa-plus"></i> Tambah Supplier</button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-plus-circle"></i> Tambah Supplier</span>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fas fa-xmark"></i></button>
        </div>
        <form onsubmit="submitSupplier(event,'tambah')">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Kode Supplier</label>
                        <input type="text" class="form-control" name="kode_supplier" required placeholder="Contoh: SUP005">
                    </div>
                    <div class="form-group">
                        <label class="required">Nama Supplier</label>
                        <input type="text" class="form-control" name="nama_supplier" required placeholder="Nama perusahaan/supplier">
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea class="form-control" name="alamat" rows="2" placeholder="Alamat lengkap supplier"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nomor Telepon</label>
                        <input type="text" class="form-control" name="telepon" placeholder="08xx-xxxx-xxxx">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" placeholder="email@supplier.com">
                    </div>
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
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-pen"></i> Edit Supplier</span>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fas fa-xmark"></i></button>
        </div>
        <form onsubmit="submitSupplier(event,'edit')">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Kode Supplier</label>
                        <input type="text" class="form-control" name="kode_supplier" id="edit_kode" required>
                    </div>
                    <div class="form-group">
                        <label class="required">Nama Supplier</label>
                        <input type="text" class="form-control" name="nama_supplier" id="edit_nama" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea class="form-control" name="alamat" id="edit_alamat" rows="2"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nomor Telepon</label>
                        <input type="text" class="form-control" name="telepon" id="edit_telp">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email">
                    </div>
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
function editSupplier(d) {
    document.getElementById('edit_id').value    = d.id;
    document.getElementById('edit_kode').value  = d.kode_supplier;
    document.getElementById('edit_nama').value  = d.nama_supplier;
    document.getElementById('edit_alamat').value= d.alamat || '';
    document.getElementById('edit_telp').value  = d.telepon || '';
    document.getElementById('edit_email').value = d.email || '';
    openModal('modalEdit');
}
function submitSupplier(e, type) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('action', type);
    showLoading();
    fetch('ajax/supplier_ajax.php',{method:'POST',body:fd})
        .then(r=>r.json()).then(res=>{
            hideLoading(); showToast(res.message,res.success?'success':'error');
            if(res.success){ closeModal('modalTambah'); closeModal('modalEdit'); setTimeout(()=>location.reload(),800); }
        }).catch(()=>{ hideLoading(); showToast('Gagal terhubung','error'); });
}
function hapusSupplier(id, nama) {
    if(!confirm(`Hapus supplier "${nama}"?`)) return;
    showLoading();
    fetch('ajax/supplier_ajax.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=hapus&id=${id}`})
        .then(r=>r.json()).then(res=>{ hideLoading(); showToast(res.message,res.success?'success':'error'); if(res.success) setTimeout(()=>location.reload(),800); })
        .catch(()=>{ hideLoading(); showToast('Gagal','error'); });
}
</script>
