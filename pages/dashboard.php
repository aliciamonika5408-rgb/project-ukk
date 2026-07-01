<?php
// pages/dashboard.php
$db    = getDB();
$stats = getDashboardStats();
$chart = getMonthlyData(date('Y'));

// Recent barang masuk
$recent_masuk = $db->query("
    SELECT bm.*, b.nama_barang, b.kode_barang, s.nama_supplier
    FROM barang_masuk bm
    JOIN barang b ON bm.id_barang = b.id
    LEFT JOIN supplier s ON bm.id_supplier = s.id
    ORDER BY bm.created_at DESC LIMIT 5
");

// Recent barang keluar
$recent_keluar = $db->query("
    SELECT bk.*, b.nama_barang, b.kode_barang
    FROM barang_keluar bk
    JOIN barang b ON bk.id_barang = b.id
    ORDER BY bk.created_at DESC LIMIT 5
");

// Stok menipis items
$stok_menipis_items = $db->query("
    SELECT b.*, k.nama_kategori 
    FROM barang b
    LEFT JOIN kategori k ON b.id_kategori = k.id
    WHERE b.stok <= b.stok_minimum
    ORDER BY b.stok ASC LIMIT 8
");

// Category summary for donut chart
$kat_labels = [];
$kat_data   = [];
$res_kat    = $db->query("
    SELECT k.nama_kategori, SUM(b.stok) as total_stok
    FROM barang b
    LEFT JOIN kategori k ON b.id_kategori = k.id
    GROUP BY b.id_kategori, k.nama_kategori
");
while ($r = $res_kat->fetch_assoc()) {
    $kat_labels[] = $r['nama_kategori'] ?: 'Tanpa Kategori';
    $kat_data[]   = (int)$r['total_stok'];
}

$chart_masuk  = json_encode($chart['masuk']);
$chart_keluar = json_encode($chart['keluar']);
$chart_kat_labels = json_encode($kat_labels);
$chart_kat_data   = json_encode($kat_data);

// Get current month name in Indonesian
$bulan_indo = ['','Januari','Februari','Maret','April','Mei','Juni',
               'Juli','Agustus','September','Oktober','November','Desember'];
$current_month = $bulan_indo[(int)date('m')] . ' ' . date('Y');
?>

<!-- ===== HERO BANNER ===== -->
<div class="dashboard-hero">
    <div class="hero-date-wrapper">
        <div class="hero-date-box">
            <i class="fas fa-calendar-alt"></i>
            <span><?= $current_month ?></span>
            <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 6px; color: var(--text-light);"></i>
        </div>
    </div>
    <span class="hero-sparkles">✦ ✧ ✦</span>
    <div class="hero-main-content">
        <div class="hero-text-group">
            <h2>Stock Gudang</h2>
            <p class="hero-subtitle">Inventory Management System</p>
        </div>
        <div class="hero-mascot-wrapper">
            <img src="assets/img/cat-hello.png" class="hero-mascot" alt="Mascot">
        </div>
    </div>
</div>

<!-- ===== STAT CARDS ===== -->
<div class="stats-grid">
    <!-- Card 1: Rasio Kategori (Mini Donut Chart) -->
    <div class="stat-card purple" style="display: flex; flex-direction: column; justify-content: space-between; min-height: 140px; position: relative; padding-bottom: 12px;">
        <div class="stat-label">Rasio Kategori</div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 4px;">
            <div style="width: 70px; height: 70px; flex-shrink: 0; position: relative;">
                <canvas id="miniKategoriChart" width="70" height="70"></canvas>
                <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: var(--text-dark);">
                    <?php 
                    $total_stok_all = array_sum($kat_data);
                    echo ($total_stok_all > 0) ? round(($kat_data[0] / $total_stok_all) * 100) . '%' : '0%';
                    ?>
                </div>
            </div>
            <img src="assets/img/cat-peek.png" alt="cat" style="width: 40px; height: auto; object-fit: contain; margin-bottom: -10px; margin-right: -4px;">
        </div>
        <div style="font-size: 10px; color: var(--text-light); margin-top: 6px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            Dominan: <span style="font-weight: 700; color: var(--primary-dark);"><?= !empty($kat_labels) ? htmlspecialchars($kat_labels[0]) : '-' ?></span>
        </div>
    </div>

    <!-- Card 2: Total Barang -->
    <div class="stat-card pink" style="display: flex; flex-direction: column; justify-content: space-between; min-height: 140px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div class="stat-label">Total Barang</div>
            <div class="stat-icon pink" style="margin-top: -4px; margin-right: -4px;">
                <i class="fas fa-box"></i>
            </div>
        </div>
        <div class="stat-value" style="font-size: 26px; font-weight: 700; color: var(--text-dark); margin-top: 4px;"><?= number_format($stats['total_barang']) ?></div>
        <div class="mini-bar-chart" style="margin-top: 8px;">
            <div class="bar pink" style="height: 35%"></div>
            <div class="bar pink" style="height: 55%"></div>
            <div class="bar pink" style="height: 45%"></div>
            <div class="bar pink active" style="height: 85%"></div>
            <div class="bar pink" style="height: 65%"></div>
            <div class="bar pink active" style="height: 95%"></div>
        </div>
    </div>

    <!-- Card 3: Total Supplier -->
    <div class="stat-card rose" style="display: flex; flex-direction: column; justify-content: space-between; min-height: 140px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div class="stat-label">Total Supplier</div>
            <div class="stat-icon rose" style="margin-top: -4px; margin-right: -4px;">
                <i class="fas fa-truck"></i>
            </div>
        </div>
        <div class="stat-value" style="font-size: 26px; font-weight: 700; color: var(--text-dark); margin-top: 4px;"><?= number_format($stats['total_supplier']) ?></div>
        <div class="mini-bar-chart" style="margin-top: 8px;">
            <div class="bar active" style="height: 40%"></div>
            <div class="bar" style="height: 65%"></div>
            <div class="bar active" style="height: 85%"></div>
            <div class="bar" style="height: 55%"></div>
            <div class="bar active" style="height: 75%"></div>
            <div class="bar" style="height: 45%"></div>
        </div>
    </div>

    <!-- Card 4: Total Barang Masuk -->
    <div class="stat-card green" style="display: flex; flex-direction: column; justify-content: space-between; min-height: 140px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div class="stat-label">Barang Masuk</div>
            <div class="stat-icon green" style="margin-top: -4px; margin-right: -4px;">
                <i class="fas fa-arrow-down-to-bracket"></i>
            </div>
        </div>
        <div class="stat-value" style="font-size: 26px; font-weight: 700; color: var(--text-dark); margin-top: 4px;"><?= number_format($stats['total_masuk']) ?></div>
        <div class="mini-bar-chart" style="margin-top: 8px;">
            <div class="bar green active" style="height: 50%"></div>
            <div class="bar green" style="height: 35%"></div>
            <div class="bar green active" style="height: 75%"></div>
            <div class="bar green" style="height: 60%"></div>
            <div class="bar green active" style="height: 90%"></div>
            <div class="bar green active" style="height: 80%"></div>
        </div>
    </div>

    <!-- Card 5: Total Barang Keluar -->
    <div class="stat-card orange" style="text-align: center; display: flex; flex-direction: column; justify-content: space-between; min-height: 140px;">
        <div>
            <div class="stat-label" style="text-align: center;">Barang Keluar</div>
            <div class="stat-value" style="font-size: 28px; font-weight: 700; color: var(--text-dark); margin-top: 4px;"><?= number_format($stats['total_keluar']) ?></div>
            <div style="font-size: 11px; color: var(--text-light); margin-top: 2px;">Unit dikeluarkan</div>
        </div>
        <div class="mini-cats-row" style="justify-content: center;">
            <img src="assets/img/cat-peek.png" alt="cat">
            <img src="assets/img/cat-peek.png" alt="cat">
            <img src="assets/img/cat-peek.png" alt="cat">
            <img src="assets/img/cat-peek.png" alt="cat">
            <img src="assets/img/cat-peek.png" alt="cat">
        </div>
    </div>
</div>

<!-- ===== ROW 2: 3 COLUMNS (Masuk Terbaru, Keluar Terbaru, Donut Chart) ===== -->
<div class="dashboard-row-3">
    <!-- Recent Barang Masuk -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-arrow-down-to-bracket"></i> Masuk Terbaru</span>
            <a href="index.php?page=barang_masuk" class="btn btn-secondary btn-sm">Semua</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Jml</th>
                        <th>Tanggal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $recent_masuk->data_seek(0);
                    $cnt = 0; 
                    while ($row = $recent_masuk->fetch_assoc()): $cnt++; 
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:12px;"><?= htmlspecialchars($row['nama_barang']) ?></div>
                            <div style="font-size:10px;color:var(--text-light);"><?= htmlspecialchars($row['nama_supplier'] ?? '-') ?></div>
                        </td>
                        <td><span class="badge badge-green">+<?= $row['jumlah'] ?></span></td>
                        <td style="font-size:11px;color:var(--text-light);"><?= date('d/m/y', strtotime($row['tanggal'])) ?></td>
                        <td style="text-align: right; width: 30px; padding-right: 8px; vertical-align: middle;">
                            <img src="assets/img/cat-peek.png" alt="cat" style="width: 18px; height: 18px; object-fit: contain; opacity: 0.85;">
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($cnt === 0): ?>
                    <tr><td colspan="4">
                        <div class="empty-state" style="padding:20px 10px;">
                            <img src="assets/img/cat-sleep.png" class="empty-cat" alt="Empty" style="width:50px;">
                            <p style="font-size:10px;">Belum ada transaksi masuk</p>
                        </div>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Barang Keluar -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-arrow-up-from-bracket"></i> Keluar Terbaru</span>
            <a href="index.php?page=barang_keluar" class="btn btn-secondary btn-sm">Semua</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Jml</th>
                        <th>Tanggal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $recent_keluar->data_seek(0);
                    $cnt2 = 0; 
                    while ($row = $recent_keluar->fetch_assoc()): $cnt2++; 
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:12px;"><?= htmlspecialchars($row['nama_barang']) ?></div>
                            <div style="font-size:10px;color:var(--text-light);"><?= htmlspecialchars($row['tujuan'] ?? '-') ?></div>
                        </td>
                        <td><span class="badge badge-red">-<?= $row['jumlah'] ?></span></td>
                        <td style="font-size:11px;color:var(--text-light);"><?= date('d/m/y', strtotime($row['tanggal'])) ?></td>
                        <td style="text-align: right; width: 30px; padding-right: 8px; vertical-align: middle;">
                            <img src="assets/img/cat-peek.png" alt="cat" style="width: 18px; height: 18px; object-fit: contain; opacity: 0.85;">
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($cnt2 === 0): ?>
                    <tr><td colspan="4">
                        <div class="empty-state" style="padding:20px 10px;">
                            <img src="assets/img/cat-sleep.png" class="empty-cat" alt="Empty" style="width:50px;">
                            <p style="font-size:10px;">Belum ada transaksi keluar</p>
                        </div>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Donut Chart: Category Summary -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">
                <i class="fas fa-chart-pie"></i> Stok per Kategori
            </span>
        </div>
        <div class="card-body" style="padding: 14px 18px;">
            <div style="display: flex; align-items: center; gap: 14px; margin-top: 4px;">
                <div style="width: 110px; height: 110px; flex-shrink: 0; position: relative;">
                    <canvas id="kategoriChart" width="110" height="110"></canvas>
                </div>
                <div id="kategoriLegend" style="flex: 1; max-height: 120px; overflow-y: auto; padding-right: 4px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- ===== ROW 3: 3 COLUMNS (Line Chart Bulanan span-2, Stok Menipis) ===== -->
<div class="dashboard-row-3">
    <!-- Line Chart: Monthly Overview -->
    <div class="card span-2">
        <div class="card-header">
            <span class="card-title">
                <i class="fas fa-chart-line"></i> Grafik Transaksi Bulanan <?= date('Y') ?>
            </span>
            <div style="display:flex; gap:14px; font-size:12px;">
                <span style="display:flex;align-items:center;gap:5px;">
                    <span style="width:12px;height:3px;background:var(--primary);display:inline-block;border-radius:2px;"></span>Masuk
                </span>
                <span style="display:flex;align-items:center;gap:5px;">
                    <span style="width:12px;height:3px;background:var(--pink);display:inline-block;border-radius:2px;"></span>Keluar
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Barang Stok Menipis Card -->
    <div class="card">
        <div class="card-header">
            <span class="card-title" style="color:var(--warning);">
                <i class="fas fa-triangle-exclamation"></i> Stok Menipis
            </span>
            <a href="index.php?page=barang_masuk" class="btn btn-warning btn-sm" style="padding: 4px 8px; font-size:10px;">
                + Stok
            </a>
        </div>
        <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Stok</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stok_menipis_items->data_seek(0);
                    $cnt3 = 0;
                    while ($row = $stok_menipis_items->fetch_assoc()): $cnt3++; 
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:12px;"><?= htmlspecialchars($row['nama_barang']) ?></div>
                            <div style="font-size:10px;color:var(--text-light);"><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></div>
                        </td>
                        <td>
                            <span class="badge <?= $row['stok'] == 0 ? 'badge-red' : 'badge-yellow' ?>" style="font-weight:700;">
                                <?= $row['stok'] ?> / <?= $row['stok_minimum'] ?>
                            </span>
                        </td>
                        <td style="text-align: right; width: 30px; padding-right: 8px; vertical-align: middle;">
                            <img src="assets/img/cat-peek.png" alt="cat" style="width: 18px; height: 18px; object-fit: contain; opacity: 0.85;">
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($cnt3 === 0): ?>
                    <tr><td colspan="3">
                        <div class="empty-state" style="padding:30px 10px;">
                            <img src="assets/img/cat-sleep.png" class="empty-cat" alt="Empty" style="width:50px;">
                            <p style="font-size:10px; color:var(--text-light);">Semua stok aman!</p>
                        </div>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Monthly Line Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const masukData  = <?= $chart_masuk ?>;
const keluarData = <?= $chart_keluar ?>;
const months     = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [
            {
                label: 'Barang Masuk',
                data: masukData,
                borderColor: '#C4B0E0',
                backgroundColor: 'rgba(196,176,224,0.08)',
                tension: 0.4,
                fill: true,
                borderWidth: 2.5,
                pointBackgroundColor: '#C4B0E0',
                pointRadius: 4,
                pointHoverRadius: 6,
            },
            {
                label: 'Barang Keluar',
                data: keluarData,
                borderColor: '#E8A0BF',
                backgroundColor: 'rgba(232,160,191,0.08)',
                tension: 0.4,
                fill: true,
                borderWidth: 2.5,
                pointBackgroundColor: '#E8A0BF',
                pointRadius: 4,
                pointHoverRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#fff',
                titleColor: '#3A2D50',
                bodyColor: '#6B5D7B',
                borderColor: '#E4D9F0',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 10,
            }
        },
        scales: {
            x: {
                grid: { color: 'rgba(228,217,240,0.4)', drawBorder: false },
                ticks: { font: { family: 'Poppins', size: 11 }, color: '#9B8FB0' }
            },
            y: {
                grid: { color: 'rgba(228,217,240,0.4)', drawBorder: false },
                ticks: { font: { family: 'Poppins', size: 11 }, color: '#9B8FB0' },
                beginAtZero: true
            }
        }
    }
});

// Category Donut Chart
const katLabels = <?= $chart_kat_labels ?>;
const katData   = <?= $chart_kat_data ?>;
const katColors = ['#B8A9D4','#E8A0BF','#8B7BB5','#6BBF8A','#EDB95E','#7BA7D7','#D4C8E8','#F2C4D6'];

if (katLabels.length > 0 && document.getElementById('kategoriChart')) {
    const katCtx = document.getElementById('kategoriChart').getContext('2d');
    new Chart(katCtx, {
        type: 'doughnut',
        data: {
            labels: katLabels,
            datasets: [{
                data: katData,
                backgroundColor: katColors.slice(0, katLabels.length),
                borderWidth: 3,
                borderColor: '#fff',
                hoverBorderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#3A2D50',
                    bodyColor: '#6B5D7B',
                    borderColor: '#E4D9F0',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8
                }
            }
        }
    });

    // Custom legend
    const legend = document.getElementById('kategoriLegend');
    katLabels.forEach((label, i) => {
        const pct = katData.reduce((a,b)=>a+b,0) > 0
            ? Math.round(katData[i]/katData.reduce((a,b)=>a+b,0)*100)
            : 0;
        legend.innerHTML += `<div style="display:flex;align-items:center;gap:7px;margin-bottom:5px;">
            <span style="width:10px;height:10px;border-radius:50%;background:${katColors[i]};flex-shrink:0;"></span>
            <span style="font-size:11px;color:#6B5D7B;flex:1;">${label}</span>
            <span style="font-size:11px;font-weight:700;color:#3A2D50;">${pct}%</span>
        </div>`;
    });
}

// Mini Kategori Chart
if (katLabels.length > 0 && document.getElementById('miniKategoriChart')) {
    const miniCtx = document.getElementById('miniKategoriChart').getContext('2d');
    new Chart(miniCtx, {
        type: 'doughnut',
        data: {
            labels: katLabels,
            datasets: [{
                data: katData,
                backgroundColor: katColors.slice(0, katLabels.length),
                borderWidth: 1.5,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });
}
</script>
