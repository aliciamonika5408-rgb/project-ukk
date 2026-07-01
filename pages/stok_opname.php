<?php
// pages/stok_opname.php
$db = getDB();
$rows = $db->query("
    SELECT so.*, b.nama_barang, b.kode_barang, b.satuan, u.nama as nama_user
    FROM stok_opname so
    JOIN barang b ON so.id_barang = b.id
    LEFT JOIN users u ON so.id_user = u.id
    ORDER BY so.created_at DESC
");
$barang_list  = $db->query("SELECT id, kode_barang, nama_barang, satuan, stok FROM barang ORDER BY nama_barang");
$no_opname    = generateNoTransaksi('OPN', 'stok_opname', 'no_opname');
?>

<div class="card">
    <div class="table-toolbar">
        <div class="table-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" placeholder="Cari opname..." oninput="filterTbl(this,'tblOpname')">
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="tblOpname">
            <thead>
                <tr>
                    <th>#</th>
                    <th>No. Opname</th>
                    <th>Barang</th>
                    <th>Stok Sistem</th>
                    <th>Stok Fisik</th>
                    <th>Selisih</th>
                    <th>Tanggal</th>
                    <th>Petugas</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($row=$rows->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><span class="badge badge-purple"><?= htmlspecialchars($row['no_opname']) ?></span></td>
                    <td>
                        <div style="font-weight:600;font-size:12px;"><?= htmlspecialchars($row['nama_barang']) ?></div>
                        <div style="font-size:11px;color:var(--text-light);"><?= htmlspecialchars($row['kode_barang']) ?></div>
                    </td>
                    <td><?= $row['stok_sistem'] ?> <?= htmlspecialchars($row['satuan']) ?></td>
                    <td><?= $row['stok_fisik'] ?> <?= htmlspecialchars($row['satuan']) ?></td>
                    <td>
                        <?php if ($row['selisih'] == 0): ?>
                            <span class="badge badge-green">Sesuai (0)</span>
                        <?php elseif ($row['selisih'] > 0): ?>
                            <span class="badge badge-blue">+<?= $row['selisih'] ?></span>
                        <?php else: ?>
                            <span class="badge badge-red"><?= $row['selisih'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;"><?= formatDate($row['tanggal']) ?></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($row['nama_user'] ?? '-') ?></td>
                    <td style="font-size:12px;color:var(--text-light);"><?= htmlspecialchars($row['keterangan'] ?: '-') ?></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-danger btn-icon" onclick="hapusOpname(<?= $row['id'] ?>,'<?= htmlspecialchars($row['no_opname']) ?>')" title="Hapus">
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
            <h3>Belum Ada Data Stok Opname</h3>
            <p>Lakukan pengecekan fisik stok secara berkala.</p>
            <button class="btn btn-primary" onclick="openModal('modalTambah')"><i class="fas fa-plus"></i> Mulai Opname</button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah" style="display:none;">
    <div class="modal" style="max-width:560px;">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-clipboard-check"></i> Input Stok Opname</span>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fas fa-xmark"></i></button>
        </div>
        <form onsubmit="submitOpname(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label>No. Opname (Otomatis)</label>
                    <input type="text" class="form-control" name="no_opname" value="<?= $no_opname ?>" readonly
                        style="background:var(--bg-main);font-weight:600;color:var(--primary-dark);">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Barang</label>
                        <select class="form-control" name="id_barang" id="selectBarangOpname" required onchange="updateStokSistem()">
                            <option value="">-- Pilih Barang --</option>
                            <?php $barang_list->data_seek(0); while($b=$barang_list->fetch_assoc()): ?>
                            <option value="<?= $b['id'] ?>" data-stok="<?= $b['stok'] ?>">
                                <?= htmlspecialchars("[{$b['kode_barang']}] {$b['nama_barang']}") ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="required">Tanggal Opname</label>
                        <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Stok Sistem</label>
                        <input type="number" class="form-control" name="stok_sistem" id="stokSistem" readonly value="0"
                            style="background:var(--bg-main);">
                    </div>
                    <div class="form-group">
                        <label class="required">Stok Fisik (Aktual)</label>
                        <input type="number" class="form-control" name="stok_fisik" id="stokFisik" required min="0" value="0" oninput="hitungSelisih()">
                    </div>
                </div>
                <div class="form-group">
                    <label>Selisih</label>
                    <input type="text" class="form-control" id="selisihDisplay" readonly value="0"
                        style="font-weight:700;">
                    <input type="hidden" name="selisih" id="selisihValue">
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea class="form-control" name="keterangan" rows="2" placeholder="Alasan selisih atau catatan"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambah')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Opname</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateStokSistem() {
    const sel  = document.getElementById('selectBarangOpname');
    const opt  = sel.options[sel.selectedIndex];
    const stok = parseInt(opt.dataset.stok) || 0;
    document.getElementById('stokSistem').value = stok;
    document.getElementById('stokFisik').value  = stok;
    hitungSelisih();
}
function hitungSelisih() {
    const sistem = parseInt(document.getElementById('stokSistem').value) || 0;
    const fisik  = parseInt(document.getElementById('stokFisik').value)  || 0;
    const selisih = fisik - sistem;
    const el = document.getElementById('selisihDisplay');
    el.value = selisih >= 0 ? `+${selisih}` : `${selisih}`;
    el.style.color = selisih === 0 ? 'var(--success)' : selisih > 0 ? 'var(--info)' : 'var(--danger)';
    document.getElementById('selisihValue').value = selisih;
}
function submitOpname(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('action','tambah');
    fd.append('id_user', <?= $_SESSION['user_id'] ?>);
    showLoading();
    fetch('ajax/stok_opname_ajax.php',{method:'POST',body:fd})
        .then(r=>r.json()).then(res=>{
            hideLoading(); showToast(res.message,res.success?'success':'error');
            if(res.success){ closeModal('modalTambah'); setTimeout(()=>location.reload(),800); }
        }).catch(()=>{ hideLoading(); showToast('Gagal','error'); });
}
function hapusOpname(id, no) {
    if(!confirm(`Hapus opname ${no}?`)) return;
    showLoading();
    fetch('ajax/stok_opname_ajax.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=hapus&id=${id}`})
        .then(r=>r.json()).then(res=>{ hideLoading(); showToast(res.message,res.success?'success':'error'); if(res.success) setTimeout(()=>location.reload(),800); })
        .catch(()=>{ hideLoading(); showToast('Gagal','error'); });
}
</script>
