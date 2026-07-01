<?php
// ajax/laporan_ajax.php — Excel export
require_once '../config/database.php';
requireLogin();

$action   = $_GET['action'] ?? '';
$jenis    = $_GET['jenis']     ?? 'masuk';
$tgl_m    = clean($_GET['tgl_mulai'] ?? date('Y-m-01'));
$tgl_a    = clean($_GET['tgl_akhir'] ?? date('Y-m-d'));

if ($action === 'excel') {
    // Disable error reporting to prevent notices/warnings from corrupting the Excel file
    error_reporting(0);
    ini_set('display_errors', 0);

    // Clear any previous output buffers to avoid "headers already sent" issues and corrupt file downloads
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $filename = "Laporan_Barang_" . ucfirst($jenis) . "_" . date('Ymd') . ".xls";
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Transfer-Encoding: binary");

    $db = getDB();

    echo "<html xmlns:o='urn:schemas-microsoft-com:office:office'
    xmlns:x='urn:schemas-microsoft-com:office:excel'
    xmlns='http://www.w3.org/TR/REC-html40'>
    <head><meta charset='utf-8'></head><body>";
    echo "<table border='1'>";
    echo "<tr><th colspan='10' style='background:#F8D7E8;font-size:16px;font-weight:bold;text-align:center;'>
        LAPORAN BARANG " . strtoupper($jenis) . " — {$tgl_m} s/d {$tgl_a}
    </th></tr>";

    if ($jenis === 'masuk') {
        echo "<tr style='background:#F4B6D2;font-weight:bold;'>
            <th>#</th><th>No. Transaksi</th><th>Tanggal</th><th>Supplier</th>
            <th>Kode Barang</th><th>Nama Barang</th><th>Satuan</th>
            <th>Jumlah</th><th>Harga Satuan</th><th>Total</th><th>Keterangan</th>
        </tr>";
        $data = $db->query("
            SELECT bm.no_transaksi,bm.tanggal,s.nama_supplier,b.kode_barang,b.nama_barang,b.satuan,bm.jumlah,bm.harga_satuan,(bm.jumlah*bm.harga_satuan) as total,bm.keterangan
            FROM barang_masuk bm JOIN barang b ON bm.id_barang=b.id LEFT JOIN supplier s ON bm.id_supplier=s.id
            WHERE bm.tanggal BETWEEN '$tgl_m' AND '$tgl_a' ORDER BY bm.tanggal
        ");
        $grand=0; $no=1;
        while($r=$data->fetch_assoc()) {
            $grand += $r['total'];
            echo "<tr>
                <td>{$no}</td>
                <td>{$r['no_transaksi']}</td>
                <td>{$r['tanggal']}</td>
                <td>{$r['nama_supplier']}</td>
                <td>{$r['kode_barang']}</td>
                <td>{$r['nama_barang']}</td>
                <td>{$r['satuan']}</td>
                <td>{$r['jumlah']}</td>
                <td>" . number_format($r['harga_satuan'],0,',','.') . "</td>
                <td>" . number_format($r['total'],0,',','.') . "</td>
                <td>{$r['keterangan']}</td>
            </tr>";
            $no++;
        }
        echo "<tr style='font-weight:bold;background:#F8D7E8;'>
            <td colspan='9' style='text-align:right;'>GRAND TOTAL:</td>
            <td>" . number_format($grand,0,',','.') . "</td><td></td>
        </tr>";
    } else {
        echo "<tr style='background:#F4B6D2;font-weight:bold;'>
            <th>#</th><th>No. Transaksi</th><th>Tanggal</th>
            <th>Kode Barang</th><th>Nama Barang</th><th>Satuan</th>
            <th>Jumlah</th><th>Harga Satuan</th><th>Total</th><th>Tujuan</th><th>Keterangan</th>
        </tr>";
        $data = $db->query("
            SELECT bk.no_transaksi,bk.tanggal,b.kode_barang,b.nama_barang,b.satuan,bk.jumlah,bk.harga_satuan,(bk.jumlah*bk.harga_satuan) as total,bk.tujuan,bk.keterangan
            FROM barang_keluar bk JOIN barang b ON bk.id_barang=b.id
            WHERE bk.tanggal BETWEEN '$tgl_m' AND '$tgl_a' ORDER BY bk.tanggal
        ");
        $grand=0; $no=1;
        while($r=$data->fetch_assoc()) {
            $grand += $r['total'];
            echo "<tr>
                <td>{$no}</td>
                <td>{$r['no_transaksi']}</td>
                <td>{$r['tanggal']}</td>
                <td>{$r['kode_barang']}</td>
                <td>{$r['nama_barang']}</td>
                <td>{$r['satuan']}</td>
                <td>{$r['jumlah']}</td>
                <td>" . number_format($r['harga_satuan'],0,',','.') . "</td>
                <td>" . number_format($r['total'],0,',','.') . "</td>
                <td>{$r['tujuan']}</td>
                <td>{$r['keterangan']}</td>
            </tr>";
            $no++;
        }
        echo "<tr style='font-weight:bold;background:#F8D7E8;'>
            <td colspan='8' style='text-align:right;'>GRAND TOTAL:</td>
            <td>" . number_format($grand,0,',','.') . "</td><td></td><td></td>
        </tr>";
    }
    echo "</table></body></html>";
    exit;
}

echo json_encode(['success'=>false,'message'=>'Aksi tidak dikenal!']);
