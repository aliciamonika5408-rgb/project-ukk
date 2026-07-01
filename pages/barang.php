<?php
// pages/barang.php
$db = getDB();
$filter_menipis = isset($_GET['filter']) && $_GET['filter'] === 'menipis';

// Fetch data
$where = $filter_menipis ? 'WHERE b.stok <= b.stok_minimum' : '';
$rows = $db->query("
    SELECT b.*, k.nama_kategori
    FROM barang b
    LEFT JOIN kategori k ON b.id_kategori = k.id
    $where
    ORDER BY b.created_at DESC
");

$kategori_list = $db->query("SELECT * FROM kategori ORDER BY nama_kategori");
?>

<?php if ($filter_menipis): ?>
<div class="stok-alert" style="margin-bottom:16px;">
    <i class="fas fa-filter"></i>
    <div><h4>Filter Aktif: Stok Menipis</h4>
    <p>Menampilkan barang yang stoknya di bawah minimum.</p></div>
    <a href="index.php?page=barang" class="btn btn-secondary btn-sm" style="margin-left:auto;">Hapus Filter</a>
</div>
<?php endif; ?>

<div class="card">
    <div class="table-toolbar">
        <div class="table-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" id="searchBarang" placeholder="Cari barang..." oninput="filterTable()">
        </div>
        <div class="toolbar-actions">
            <select class="form-control" id="filterKategori" onchange="filterTable()" style="width:auto;padding:9px 32px 9px 12px;font-size:12px;">
                <option value="">Semua Kategori</option>
                <?php $kategori_list->data_seek(0); while($k = $kategori_list->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($k['nama_kategori']) ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                <?php endwhile; ?>
            </select>
            <a href="index.php?page=barang&filter=menipis" class="btn btn-warning btn-sm">
                <i class="fas fa-triangle-exclamation"></i> Stok Menipis
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="tabelBarang">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Foto</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="barangBody">
                <?php $no = 1; while($row = $rows->fetch_assoc()): ?>
                <tr data-name="<?= strtolower(htmlspecialchars($row['nama_barang'])) ?>"
                    data-kode="<?= strtolower(htmlspecialchars($row['kode_barang'])) ?>"
                    data-kategori="<?= htmlspecialchars($row['nama_kategori'] ?? '') ?>">
                    <td><?= $no++ ?></td>
                    <td>
                        <?php if ($row['foto'] && file_exists("assets/img/barang/{$row['foto']}")): ?>
                            <img src="assets/img/barang/<?= htmlspecialchars($row['foto']) ?>" class="item-photo" alt="">
                        <?php else: ?>
                            <div class="item-photo-placeholder"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-pink"><?= htmlspecialchars($row['kode_barang']) ?></span></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($row['nama_barang']) ?></td>
                    <td><span class="badge badge-blue"><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></span></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($row['satuan']) ?></td>
                    <td style="font-size:13px;"><?= formatRupiah($row['harga_beli']) ?></td>
                    <td style="font-size:13px;"><?= formatRupiah($row['harga_jual']) ?></td>
                    <td><strong><?= $row['stok'] ?></strong></td>
                    <td>
                        <?php if ($row['stok'] == 0): ?>
                            <span class="stock-status stock-empty"><span class="stock-dot"></span> Habis</span>
                        <?php elseif ($row['stok'] <= $row['stok_minimum']): ?>
                            <span class="stock-status stock-low"><span class="stock-dot"></span> Menipis</span>
                        <?php else: ?>
                            <span class="stock-status stock-ok"><span class="stock-dot"></span> Aman</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-secondary btn-icon" title="Edit"
                                onclick='editBarang(<?= json_encode($row) ?>)'>
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-icon" title="Hapus"
                                onclick="hapusBarang(<?= $row['id'] ?>, '<?= addslashes($row['nama_barang']) ?>')">
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
            <h3>Belum Ada Data Barang</h3>
            <p>Klik tombol "Tambah Barang" untuk menambahkan data barang pertama.</p>
            <button class="btn btn-primary" onclick="openModal('modalTambah')">
                <i class="fas fa-plus"></i> Tambah Barang
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah" style="display:none;">
    <div class="modal" style="max-width:620px;">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-plus-circle"></i> Tambah Barang</span>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fas fa-xmark"></i></button>
        </div>
        <form id="formTambah" onsubmit="submitBarang(event,'tambah')" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Kode Barang</label>
                        <input type="text" class="form-control" name="kode_barang" required
                            placeholder="Contoh: BRG001">
                    </div>
                    <div class="form-group">
                        <label class="required">Nama Barang</label>
                        <input type="text" class="form-control" name="nama_barang" required
                            placeholder="Nama barang">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select class="form-control" name="id_kategori">
                            <option value="">-- Pilih Kategori --</option>
                            <?php $kategori_list->data_seek(0); while($k=$kategori_list->fetch_assoc()): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="required">Satuan</label>
                        <input type="text" class="form-control" name="satuan" required
                            placeholder="pcs, kg, lusin, dll">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Harga Beli (Rp)</label>
                        <input type="number" class="form-control" name="harga_beli" required min="0">
                    </div>
                    <div class="form-group">
                        <label class="required">Harga Jual (Rp)</label>
                        <input type="number" class="form-control" name="harga_jual" required min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Stok Awal</label>
                        <input type="number" class="form-control" name="stok" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label>Stok Minimum</label>
                        <input type="number" class="form-control" name="stok_minimum" value="5" min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label>Foto Barang</label>
                    <div class="upload-area" id="uploadArea">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <p>Klik untuk upload foto</p>
                        <input type="file" name="foto" accept="image/*" onchange="previewFoto(this, 'preview1')">
                    </div>
                    <img id="preview1" class="upload-preview" src="" style="display:none;" alt="Preview">
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
    <div class="modal" style="max-width:620px;">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-pen"></i> Edit Barang</span>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fas fa-xmark"></i></button>
        </div>
        <form id="formEdit" onsubmit="submitBarang(event,'edit')" enctype="multipart/form-data">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Kode Barang</label>
                        <input type="text" class="form-control" name="kode_barang" id="edit_kode" required>
                    </div>
                    <div class="form-group">
                        <label class="required">Nama Barang</label>
                        <input type="text" class="form-control" name="nama_barang" id="edit_nama" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select class="form-control" name="id_kategori" id="edit_kategori">
                            <option value="">-- Pilih Kategori --</option>
                            <?php $kategori_list->data_seek(0); while($k=$kategori_list->fetch_assoc()): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="required">Satuan</label>
                        <input type="text" class="form-control" name="satuan" id="edit_satuan" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Harga Beli (Rp)</label>
                        <input type="number" class="form-control" name="harga_beli" id="edit_harga_beli" min="0">
                    </div>
                    <div class="form-group">
                        <label>Harga Jual (Rp)</label>
                        <input type="number" class="form-control" name="harga_jual" id="edit_harga_jual" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Stok Minimum</label>
                        <input type="number" class="form-control" name="stok_minimum" id="edit_stok_min" min="0">
                    </div>
                    <div class="form-group">
                        <label>Foto Barang (kosongkan jika tidak ganti)</label>
                        <input type="file" class="form-control" name="foto" accept="image/*" onchange="previewFoto(this,'preview2')">
                    </div>
                </div>
                <img id="preview2" class="upload-preview" src="" style="display:none;" alt="Preview">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterTable() {
    const q        = document.getElementById('searchBarang').value.toLowerCase();
    const kat      = document.getElementById('filterKategori').value.toLowerCase();
    const rows     = document.querySelectorAll('#barangBody tr');
    let visible    = 0;
    rows.forEach(r => {
        const name = r.dataset.name || '';
        const kode = r.dataset.kode || '';
        const rkat = (r.dataset.kategori || '').toLowerCase();
        const matchQ   = !q || name.includes(q) || kode.includes(q);
        const matchKat = !kat || rkat === kat;
        r.style.display = (matchQ && matchKat) ? '' : 'none';
        if (matchQ && matchKat) visible++;
    });
}

function editBarang(data) {
    document.getElementById('edit_id').value         = data.id;
    document.getElementById('edit_kode').value       = data.kode_barang;
    document.getElementById('edit_nama').value       = data.nama_barang;
    document.getElementById('edit_kategori').value   = data.id_kategori || '';
    document.getElementById('edit_satuan').value     = data.satuan;
    document.getElementById('edit_harga_beli').value = data.harga_beli;
    document.getElementById('edit_harga_jual').value = data.harga_jual;
    document.getElementById('edit_stok_min').value   = data.stok_minimum;
    const prev = document.getElementById('preview2');
    if (data.foto) {
        prev.src = 'assets/img/barang/' + data.foto;
        prev.style.display = 'block';
    } else {
        prev.style.display = 'none';
    }
    openModal('modalEdit');
}

function submitBarang(e, type) {
    e.preventDefault();
    const form = e.target;
    const fd   = new FormData(form);
    fd.append('action', type);
    fd.append('user_id', <?= $_SESSION['user_id'] ?>);

    showLoading();
    fetch('ajax/barang_ajax.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            hideLoading();
            if (res.success) {
                showToast(res.message, 'success');
                closeModal('modalTambah');
                closeModal('modalEdit');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(res.message || 'Terjadi kesalahan', 'error');
            }
        })
        .catch(() => { hideLoading(); showToast('Gagal terhubung ke server', 'error'); });
}

function hapusBarang(id, nama) {
    if (!confirm(`Yakin ingin menghapus barang "${nama}"?\nData yang terkait juga dapat terpengaruh.`)) return;
    showLoading();
    fetch('ajax/barang_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=hapus&id=${id}`
    })
    .then(r => r.json())
    .then(res => {
        hideLoading();
        showToast(res.message, res.success ? 'success' : 'error');
        if (res.success) setTimeout(() => location.reload(), 800);
    })
    .catch(() => { hideLoading(); showToast('Gagal menghapus', 'error'); });
}
</script>
