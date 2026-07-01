<?php
// pages/barang_masuk.php
$db = getDB();
$rows = $db->query("
    SELECT bm.*, b.nama_barang, b.kode_barang, b.satuan, s.nama_supplier
    FROM barang_masuk bm
    JOIN barang b ON bm.id_barang = b.id
    LEFT JOIN supplier s ON bm.id_supplier = s.id
    ORDER BY bm.created_at DESC
");
$barang_list   = $db->query("SELECT id, kode_barang, nama_barang, satuan, harga_beli, stok FROM barang ORDER BY nama_barang");
$supplier_list = $db->query("SELECT id, kode_supplier, nama_supplier FROM supplier ORDER BY nama_supplier");
$no_transaksi  = generateNoTransaksi('BM', 'barang_masuk', 'no_transaksi');
?>

<div class="card">
    <div class="table-toolbar">
        <div class="table-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" placeholder="Cari transaksi..." oninput="filterTbl(this,'tblBM')">
        </div>
        <div style="display:flex;gap:8px;">
            <input type="date" class="form-control" id="filterTgl" onchange="filterByDate()" style="width:auto;padding:9px 12px;font-size:12px;" title="Filter tanggal">
            <button class="btn btn-secondary btn-sm" onclick="document.getElementById('filterTgl').value='';filterByDate()">
                <i class="fas fa-rotate"></i> Reset
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="tblBM">
            <thead>
                <tr>
                    <th>#</th>
                    <th>No. Transaksi</th>
                    <th>Supplier</th>
                    <th>Barang</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Total</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tblBMbody">
                <?php $no=1; while($row=$rows->fetch_assoc()): ?>
                <tr data-tgl="<?= $row['tanggal'] ?>">
                    <td><?= $no++ ?></td>
                    <td><span class="badge badge-green"><?= htmlspecialchars($row['no_transaksi']) ?></span></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($row['nama_supplier'] ?? '-') ?></td>
                    <td>
                        <div style="font-weight:600;font-size:12px;"><?= htmlspecialchars($row['nama_barang']) ?></div>
                        <div style="font-size:11px;color:var(--text-light);"><?= htmlspecialchars($row['kode_barang']) ?></div>
                    </td>
                    <td><strong style="color:var(--success);">+<?= $row['jumlah'] ?></strong> <?= htmlspecialchars($row['satuan']) ?></td>
                    <td style="font-size:12px;"><?= formatRupiah($row['harga_satuan']) ?></td>
                    <td style="font-weight:600;"><?= formatRupiah($row['jumlah'] * $row['harga_satuan']) ?></td>
                    <td style="font-size:12px;"><?= formatDate($row['tanggal']) ?></td>
                    <td style="font-size:12px;color:var(--text-light);"><?= htmlspecialchars($row['keterangan'] ?: '-') ?></td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-danger btn-icon" onclick="hapusBM(<?= $row['id'] ?>,'<?= htmlspecialchars($row['no_transaksi']) ?>')" title="Hapus">
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
            <h3>Belum Ada Transaksi Masuk</h3>
            <p>Catat penerimaan barang dari supplier.</p>
            <button class="btn btn-primary" onclick="openModal('modalTambah')"><i class="fas fa-plus"></i> Tambah Sekarang</button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah" style="display:none;">
    <div class="modal" style="max-width:600px;">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-arrow-down-to-bracket"></i> Catat Barang Masuk</span>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fas fa-xmark"></i></button>
        </div>
        <form onsubmit="submitBM(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label>No. Transaksi (Otomatis)</label>
                    <input type="text" class="form-control" name="no_transaksi" value="<?= $no_transaksi ?>" readonly
                        style="background:var(--bg-main);font-weight:600;color:var(--primary-dark);">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Supplier</label>
                        <select class="form-control" name="id_supplier">
                            <option value="">-- Pilih Supplier --</option>
                            <?php while($s=$supplier_list->fetch_assoc()): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_supplier']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="required">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="required">Barang</label>
                    <select class="form-control" name="id_barang" id="selectBarangBM" required onchange="updateHargaBM()">
                        <option value="">-- Pilih Barang --</option>
                        <?php $barang_list->data_seek(0); while($b=$barang_list->fetch_assoc()): ?>
                        <option value="<?= $b['id'] ?>"
                            data-harga="<?= $b['harga_beli'] ?>"
                            data-satuan="<?= htmlspecialchars($b['satuan']) ?>"
                            data-stok="<?= $b['stok'] ?>">
                            <?= htmlspecialchars("[{$b['kode_barang']}] {$b['nama_barang']}") ?> (Stok: <?= $b['stok'] ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Jumlah</label>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <input type="number" class="form-control" name="jumlah" id="jumlahBM" required min="1" value="1" oninput="hitungTotalBM()">
                            <span id="satuanBM" style="font-size:12px;color:var(--text-light);white-space:nowrap;min-width:40px;">pcs</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Harga Satuan (Rp)</label>
                        <input type="number" class="form-control" name="harga_satuan" id="hargaBM" value="0" min="0" oninput="hitungTotalBM()">
                    </div>
                </div>
                <div class="form-group">
                    <label>Total Nilai</label>
                    <input type="text" class="form-control" id="totalBM" readonly value="Rp 0"
                        style="background:var(--primary-lighter);font-weight:700;color:var(--primary-dark);">
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea class="form-control" name="keterangan" rows="2" placeholder="Keterangan transaksi (opsional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambah')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateHargaBM() {
    const sel = document.getElementById('selectBarangBM');
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('hargaBM').value  = opt.dataset.harga || 0;
    document.getElementById('satuanBM').textContent = opt.dataset.satuan || 'pcs';
    hitungTotalBM();
}
function hitungTotalBM() {
    const jml  = parseInt(document.getElementById('jumlahBM').value) || 0;
    const hrg  = parseInt(document.getElementById('hargaBM').value)  || 0;
    const total = jml * hrg;
    document.getElementById('totalBM').value = 'Rp ' + total.toLocaleString('id-ID');
}
function submitBM(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('action','tambah');
    fd.append('id_user', <?= $_SESSION['user_id'] ?>);
    showLoading();
    fetch('ajax/barang_masuk_ajax.php',{method:'POST',body:fd})
        .then(r=>r.json()).then(res=>{
            hideLoading(); showToast(res.message,res.success?'success':'error');
            if(res.success){ closeModal('modalTambah'); setTimeout(()=>location.reload(),800); }
        }).catch(()=>{ hideLoading(); showToast('Gagal','error'); });
}
function hapusBM(id, no) {
    if(!confirm(`Hapus transaksi ${no}?\nStok barang akan dikembalikan.`)) return;
    showLoading();
    fetch('ajax/barang_masuk_ajax.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=hapus&id=${id}`})
        .then(r=>r.json()).then(res=>{ hideLoading(); showToast(res.message,res.success?'success':'error'); if(res.success) setTimeout(()=>location.reload(),800); })
        .catch(()=>{ hideLoading(); showToast('Gagal','error'); });
}
function filterByDate() {
    const tgl  = document.getElementById('filterTgl').value;
    const rows = document.querySelectorAll('#tblBMbody tr');
    rows.forEach(r => {
        r.style.display = (!tgl || r.dataset.tgl === tgl) ? '' : 'none';
    });
}
</script>
