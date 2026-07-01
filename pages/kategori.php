<?php
// pages/kategori.php
$db   = getDB();
$rows = $db->query("
    SELECT k.*, COUNT(b.id) as jumlah_barang
    FROM kategori k
    LEFT JOIN barang b ON k.id = b.id_kategori
    GROUP BY k.id ORDER BY k.created_at DESC
");
?>
<div style="margin-left:auto;">
    <button class="btn btn-primary" onclick="openModal('modalTambah')">
        <i class="fas fa-plus"></i> Tambah Kategori
    </button>
</div>

<div class="card">
    <div class="table-toolbar">
        <div class="table-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" placeholder="Cari kategori..." oninput="filterTbl(this,'tblKat')">
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="tblKat">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kode Kategori</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Barang</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($row=$rows->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><span class="badge badge-pink"><?= htmlspecialchars($row['kode_kategori']) ?></span></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($row['nama_kategori']) ?></td>
                    <td style="font-size:12px;color:var(--text-light);max-width:200px;">
                        <?= htmlspecialchars($row['deskripsi'] ?: '-') ?>
                    </td>
                    <td><span class="badge badge-blue"><?= $row['jumlah_barang'] ?> barang</span></td>
                    <td style="font-size:12px;color:var(--text-light);"><?= formatDate($row['created_at']) ?></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-secondary btn-icon" title="Edit"
                                onclick='editKategori(<?= json_encode($row) ?>)'>
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-icon" title="Hapus"
                                onclick="hapusKategori(<?= $row['id'] ?>, '<?= addslashes($row['nama_kategori']) ?>')">
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
            <h3>Belum Ada Kategori</h3>
            <p>Tambahkan kategori untuk mengorganisir barang Anda.</p>
            <button class="btn btn-primary" onclick="openModal('modalTambah')"><i class="fas fa-plus"></i> Tambah Kategori</button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah" style="display:none;">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-plus-circle"></i> Tambah Kategori</span>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fas fa-xmark"></i></button>
        </div>
        <form onsubmit="submitKategori(event,'tambah')">
            <div class="modal-body">
                <div class="form-group">
                    <label class="required">Kode Kategori</label>
                    <input type="text" class="form-control" name="kode_kategori" required placeholder="Contoh: KAT006">
                </div>
                <div class="form-group">
                    <label class="required">Nama Kategori</label>
                    <input type="text" class="form-control" name="nama_kategori" required placeholder="Nama kategori">
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" rows="3" placeholder="Deskripsi kategori (opsional)"></textarea>
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
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-pen"></i> Edit Kategori</span>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fas fa-xmark"></i></button>
        </div>
        <form onsubmit="submitKategori(event,'edit')">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="required">Kode Kategori</label>
                    <input type="text" class="form-control" name="kode_kategori" id="edit_kode" required>
                </div>
                <div class="form-group">
                    <label class="required">Nama Kategori</label>
                    <input type="text" class="form-control" name="nama_kategori" id="edit_nama" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" id="edit_desk" rows="3"></textarea>
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
function editKategori(d) {
    document.getElementById('edit_id').value   = d.id;
    document.getElementById('edit_kode').value = d.kode_kategori;
    document.getElementById('edit_nama').value = d.nama_kategori;
    document.getElementById('edit_desk').value = d.deskripsi || '';
    openModal('modalEdit');
}
function submitKategori(e, type) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('action', type);
    showLoading();
    fetch('ajax/kategori_ajax.php', { method:'POST', body:fd })
        .then(r=>r.json()).then(res=>{
            hideLoading();
            showToast(res.message, res.success?'success':'error');
            if(res.success){ closeModal('modalTambah'); closeModal('modalEdit'); setTimeout(()=>location.reload(),800); }
        }).catch(()=>{ hideLoading(); showToast('Gagal terhubung','error'); });
}
function hapusKategori(id, nama) {
    if (!confirm(`Hapus kategori "${nama}"?`)) return;
    showLoading();
    fetch('ajax/kategori_ajax.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=hapus&id=${id}`})
        .then(r=>r.json()).then(res=>{ hideLoading(); showToast(res.message,res.success?'success':'error'); if(res.success) setTimeout(()=>location.reload(),800); })
        .catch(()=>{ hideLoading(); showToast('Gagal','error'); });
}
</script>
