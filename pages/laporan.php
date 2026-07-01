<?php
// pages/laporan.php
$db = getDB();

// Date filter
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$jenis     = $_GET['jenis']     ?? 'masuk';

$tgl_m = clean($tgl_mulai);
$tgl_a = clean($tgl_akhir);

if ($jenis === 'masuk') {
    $data = $db->query("
        SELECT bm.no_transaksi, bm.tanggal, s.nama_supplier,
               b.kode_barang, b.nama_barang, b.satuan,
               bm.jumlah, bm.harga_satuan,
               (bm.jumlah * bm.harga_satuan) as total,
               bm.keterangan
        FROM barang_masuk bm
        JOIN barang b ON bm.id_barang = b.id
        LEFT JOIN supplier s ON bm.id_supplier = s.id
        WHERE bm.tanggal BETWEEN '$tgl_m' AND '$tgl_a'
        ORDER BY bm.tanggal ASC, bm.no_transaksi ASC
    ");
    $label_tabel = 'Laporan Barang Masuk';
    $headers     = ['No. Transaksi','Tanggal','Supplier','Kode Barang','Nama Barang','Satuan','Jumlah','Harga Satuan','Total','Keterangan'];
} else {
    $data = $db->query("
        SELECT bk.no_transaksi, bk.tanggal,
               b.kode_barang, b.nama_barang, b.satuan,
               bk.jumlah, bk.harga_satuan,
               (bk.jumlah * bk.harga_satuan) as total,
               bk.tujuan, bk.keterangan
        FROM barang_keluar bk
        JOIN barang b ON bk.id_barang = b.id
        WHERE bk.tanggal BETWEEN '$tgl_m' AND '$tgl_a'
        ORDER BY bk.tanggal ASC, bk.no_transaksi ASC
    ");
    $label_tabel = 'Laporan Barang Keluar';
    $headers     = ['No. Transaksi','Tanggal','Kode Barang','Nama Barang','Satuan','Jumlah','Harga Satuan','Total','Tujuan','Keterangan'];
}

$rows_data = [];
$grand_total = 0;
while ($r = $data->fetch_assoc()) {
    $rows_data[] = $r;
    $grand_total += $r['total'];
}
?>

<!-- Filter Form -->
<div class="card" style="margin-bottom:18px;">
    <div class="card-body" style="padding:18px 22px;">
        <form method="GET" action="" style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap;">
            <input type="hidden" name="page" value="laporan">
            <div class="form-group" style="margin:0;flex:1;min-width:150px;">
                <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-light);">Jenis Laporan</label>
                <select class="form-control" name="jenis" style="margin-top:6px;">
                    <option value="masuk"  <?= $jenis === 'masuk'  ? 'selected' : '' ?>>Barang Masuk</option>
                    <option value="keluar" <?= $jenis === 'keluar' ? 'selected' : '' ?>>Barang Keluar</option>
                </select>
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:150px;">
                <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-light);">Tanggal Mulai</label>
                <input type="date" class="form-control" name="tgl_mulai" value="<?= $tgl_mulai ?>" style="margin-top:6px;">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:150px;">
                <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-light);">Tanggal Akhir</label>
                <input type="date" class="form-control" name="tgl_akhir" value="<?= $tgl_akhir ?>" style="margin-top:6px;">
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-magnifying-glass"></i> Tampilkan
                </button>
                <button type="button" class="btn btn-success" onclick="exportExcel()">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="stats-grid" style="margin-bottom:18px;grid-template-columns:repeat(3,1fr);">
    <div class="stat-card pink">
        <div class="stat-header">
            <div>
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value"><?= count($rows_data) ?></div>
            </div>
            <div class="stat-icon pink"><i class="fas fa-receipt"></i></div>
        </div>
        <div class="stat-trend neutral"><i class="fas fa-hashtag"></i> Periode ini</div>
    </div>
    <div class="stat-card green">
        <div class="stat-header">
            <div>
                <div class="stat-label">Total Barang</div>
                <div class="stat-value"><?= array_sum(array_column($rows_data,'jumlah')) ?></div>
            </div>
            <div class="stat-icon green"><i class="fas fa-boxes-stacked"></i></div>
        </div>
        <div class="stat-trend up"><i class="fas fa-cubes"></i> Unit <?= $jenis ?></div>
    </div>
    <div class="stat-card blue">
        <div class="stat-header">
            <div>
                <div class="stat-label">Total Nilai</div>
                <div class="stat-value small"><?= formatRupiah($grand_total) ?></div>
            </div>
            <div class="stat-icon blue"><i class="fas fa-sack-dollar"></i></div>
        </div>
        <div class="stat-trend neutral"><i class="fas fa-coins"></i> Total transaksi</div>
    </div>
</div>

<!-- Data Table -->
<div class="card" id="laporanCard">
    <div class="card-header">
        <span class="card-title">
            <i class="fas fa-file-chart-column"></i>
            <?= $label_tabel ?> | <?= formatDate($tgl_mulai) ?> – <?= formatDate($tgl_akhir) ?>
        </span>
        <span style="font-size:12px;color:var(--text-light);"><?= count($rows_data) ?> data</span>
    </div>
    <div class="table-responsive">
        <table class="data-table" id="tabelLaporan">
            <thead>
                <tr>
                    <th>#</th>
                    <?php foreach ($headers as $h): ?>
                    <th><?= $h ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows_data)): ?>
                <tr>
                    <td colspan="<?= count($headers) + 1 ?>">
                        <div class="empty-state">
                            <img src="assets/img/cat-sleep.png" class="empty-cat" alt="Empty">
                            <h3>Tidak Ada Data</h3>
                            <p>Tidak ada transaksi pada periode yang dipilih.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                    <?php $no=1; foreach ($rows_data as $r): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <?php if ($jenis === 'masuk'): ?>
                        <td><span class="badge badge-green"><?= htmlspecialchars($r['no_transaksi']) ?></span></td>
                        <td style="font-size:12px;"><?= formatDate($r['tanggal']) ?></td>
                        <td style="font-size:12px;"><?= htmlspecialchars($r['nama_supplier'] ?? '-') ?></td>
                        <td><span class="badge badge-pink"><?= htmlspecialchars($r['kode_barang']) ?></span></td>
                        <td style="font-weight:600;"><?= htmlspecialchars($r['nama_barang']) ?></td>
                        <td style="font-size:12px;"><?= htmlspecialchars($r['satuan']) ?></td>
                        <td><strong><?= $r['jumlah'] ?></strong></td>
                        <td style="font-size:12px;"><?= formatRupiah($r['harga_satuan']) ?></td>
                        <td style="font-weight:600;"><?= formatRupiah($r['total']) ?></td>
                        <td style="font-size:12px;color:var(--text-light);"><?= htmlspecialchars($r['keterangan'] ?: '-') ?></td>
                        <?php else: ?>
                        <td><span class="badge badge-red"><?= htmlspecialchars($r['no_transaksi']) ?></span></td>
                        <td style="font-size:12px;"><?= formatDate($r['tanggal']) ?></td>
                        <td><span class="badge badge-pink"><?= htmlspecialchars($r['kode_barang']) ?></span></td>
                        <td style="font-weight:600;"><?= htmlspecialchars($r['nama_barang']) ?></td>
                        <td style="font-size:12px;"><?= htmlspecialchars($r['satuan']) ?></td>
                        <td><strong><?= $r['jumlah'] ?></strong></td>
                        <td style="font-size:12px;"><?= formatRupiah($r['harga_satuan']) ?></td>
                        <td style="font-weight:600;"><?= formatRupiah($r['total']) ?></td>
                        <td style="font-size:12px;"><?= htmlspecialchars($r['tujuan'] ?? '-') ?></td>
                        <td style="font-size:12px;color:var(--text-light);"><?= htmlspecialchars($r['keterangan'] ?: '-') ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="background:var(--primary-lighter);font-weight:700;">
                        <td colspan="<?= $jenis === 'masuk' ? 9 : 9 ?>" style="text-align:right;padding-right:16px;">
                            GRAND TOTAL:
                        </td>
                        <td style="color:var(--primary-dark);font-size:14px;"><?= formatRupiah($grand_total) ?></td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function exportExcel() {
    const jenis = '<?= $jenis ?>';
    const tglM  = '<?= $tgl_mulai ?>';
    const tglA  = '<?= $tgl_akhir ?>';
    window.location = `ajax/laporan_ajax.php?action=excel&jenis=${jenis}&tgl_mulai=${tglM}&tgl_akhir=${tglA}`;
}
</script>
