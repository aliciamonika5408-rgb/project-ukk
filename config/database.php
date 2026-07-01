<?php
// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'stock_gudang');
define('APP_NAME', 'Stock Gudang');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/ukk%20cia/');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create database connection
function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

$conn = getDB();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit();
    }
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function isAdmin() {
    return hasRole('admin');
}

// Format currency IDR
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Format date to Indonesian
function formatDate($date) {
    if (!$date) return '-';
    $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $d = date('d', strtotime($date));
    $m = (int)date('m', strtotime($date));
    $y = date('Y', strtotime($date));
    return "$d {$months[$m]} $y";
}

// Generate transaction number
function generateNoTransaksi($prefix, $table, $column) {
    $db = getDB();
    $year = date('Y');
    $month = date('m');
    $pattern = "$prefix-$year$month-%";
    $result = $db->query("SELECT COUNT(*) as total FROM $table WHERE $column LIKE '$pattern'");
    $row = $result->fetch_assoc();
    $count = str_pad($row['total'] + 1, 4, '0', STR_PAD_LEFT);
    return "$prefix-$year$month-$count";
}

// Sanitize input
function clean($input) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($input))));
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $id = (int)$_SESSION['user_id'];
    $result = $db->query("SELECT * FROM users WHERE id = $id LIMIT 1");
    return $result ? $result->fetch_assoc() : null;
}

// Dashboard stats
function getDashboardStats() {
    $db = getDB();
    $stats = [];
    
    $stats['total_barang'] = $db->query("SELECT COUNT(*) as c FROM barang")->fetch_assoc()['c'];
    $stats['total_supplier'] = $db->query("SELECT COUNT(*) as c FROM supplier")->fetch_assoc()['c'];
    $stats['total_masuk'] = $db->query("SELECT COALESCE(SUM(jumlah),0) as c FROM barang_masuk")->fetch_assoc()['c'];
    $stats['total_keluar'] = $db->query("SELECT COALESCE(SUM(jumlah),0) as c FROM barang_keluar")->fetch_assoc()['c'];
    $stats['total_stok'] = $db->query("SELECT COALESCE(SUM(stok),0) as c FROM barang")->fetch_assoc()['c'];
    $stats['stok_menipis'] = $db->query("SELECT COUNT(*) as c FROM barang WHERE stok <= stok_minimum")->fetch_assoc()['c'];
    $stats['total_nilai'] = $db->query("SELECT COALESCE(SUM(stok * harga_beli),0) as c FROM barang")->fetch_assoc()['c'];
    
    return $stats;
}

// Get monthly chart data
function getMonthlyData($year = null) {
    $db = getDB();
    if (!$year) $year = date('Y');
    
    $masuk = array_fill(0, 12, 0);
    $keluar = array_fill(0, 12, 0);
    
    $res = $db->query("SELECT MONTH(tanggal) as m, SUM(jumlah) as total 
                       FROM barang_masuk WHERE YEAR(tanggal) = $year 
                       GROUP BY MONTH(tanggal)");
    while ($row = $res->fetch_assoc()) {
        $masuk[$row['m'] - 1] = (int)$row['total'];
    }
    
    $res = $db->query("SELECT MONTH(tanggal) as m, SUM(jumlah) as total 
                       FROM barang_keluar WHERE YEAR(tanggal) = $year 
                       GROUP BY MONTH(tanggal)");
    while ($row = $res->fetch_assoc()) {
        $keluar[$row['m'] - 1] = (int)$row['total'];
    }
    
    return ['masuk' => $masuk, 'keluar' => $keluar];
}

