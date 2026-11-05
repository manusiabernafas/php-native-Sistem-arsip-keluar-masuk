<?php
if (user()) redirect('dashboard');
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = q('SELECT * FROM users WHERE username = ?', [trim($_POST['username'] ?? '')])->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        unset($u['password']);
        $_SESSION['user'] = $u;
        redirect('dashboard');
    }
    $error = 'Username atau password salah.';
}
$set = setting();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk · Arsip Surat</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-art">
        <div class="mark">Arsip.</div>
        <p>Setiap surat tercatat, setiap disposisi terlacak, setiap arsip mudah ditemukan kembali.</p>
        <small><?= e($set['nama_instansi']) ?></small>
    </div>
    <div class="login-form">
        <form method="post">
            <?= csrf_field() ?>
            <h1>Masuk</h1>
            <p class="muted" style="margin:0 0 26px">Sistem Arsip Surat Masuk & Keluar</p>
            <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
            <div class="field"><label>Username</label><input type="text" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>"></div>
            <div class="field"><label>Password</label><input type="password" name="password" required></div>
            <button class="btn btn-primary btn-block">Masuk</button>
            <div class="demo-note">Akun demo (password <b>admin123</b>):<br>
                <b>admin</b> operator arsip · <b>kadis</b> pimpinan · <b>aptika</b> staf bidang</div>
        </form>
    </div>
</div>
</body>
</html>
