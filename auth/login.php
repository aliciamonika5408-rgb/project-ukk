<?php
require_once '../config/database.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password tidak boleh kosong.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['nama']      = $user['nama'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['foto']      = $user['foto'];
            $_SESSION['login_time'] = time();
            
            header('Location: ../index.php');
            exit();
        } else {
            $error = 'Username atau password salah. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Stock Gudang</title>
    <meta name="description" content="Login ke sistem manajemen stok gudang">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">
</head>
<body class="login-page">

<div class="login-container">
    <div class="login-logo">
        <img src="../assets/img/cat-hi.png" class="logo-img" alt="Logo">
        <h1>Stock Gudang</h1>
        <p>Sistem Manajemen Stok Gudang 🐾</p>
    </div>

    <?php if ($error): ?>
    <div class="alert-error">
        <i class="fas fa-circle-exclamation"></i>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="" id="loginForm" novalidate>
        <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrapper">
                <i class="fas fa-user input-icon"></i>
                <input type="text" class="form-control" id="username" name="username"
                    placeholder="Masukkan username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    required autocomplete="username">
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" class="form-control" id="password" name="password"
                    placeholder="Masukkan password"
                    required autocomplete="current-password">
                <button type="button" class="toggle-password" id="togglePwd" tabindex="-1">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-login" id="btnLogin">
            <i class="fas fa-right-to-bracket"></i>
            Masuk ke Sistem
        </button>
    </form>

    <div class="login-hint">
        <i class="fas fa-circle-info" style="color:var(--primary-dark)"></i>
        Default: <strong>admin</strong> / <strong>password</strong>
    </div>
</div>

<!-- Loading overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display:none">
    <div class="spinner"></div>
    <span class="loading-text">Memverifikasi akun...</span>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePwd').addEventListener('click', function() {
    const pwd = document.getElementById('password');
    const icon = this.querySelector('i');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        pwd.type = 'password';
        icon.className = 'fas fa-eye';
    }
});

// Show loading on submit
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    if (username && password) {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }
});
</script>
</body>
</html>
