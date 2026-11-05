<?php
$u = user();
$disposisiBaru = (int) q("SELECT COUNT(*) FROM disposisi WHERE status = 'belum'" . ($u['role'] === 'staf' ? ' AND ke_bagian_id = ' . (int) $u['bagian_id'] : ''))->fetchColumn();
$menu = [
    'Ringkasan' => [['dashboard', 'Beranda', 'home', 0]],
    'Persuratan' => [
        ['masuk', 'Surat Masuk', 'inbox', 0],
        ['keluar', 'Surat Keluar', 'send', 0],
        ['disposisi', 'Disposisi', 'share', $disposisiBaru],
        ['agenda', 'Buku Agenda', 'book', 0],
    ],
];
if (is_admin()) $menu['Sistem'] = [['master', 'Data Master', 'folder', 0]];
$current = explode('.', $_GET['page'] ?? 'dashboard')[0];
$flash = flash();
$set = setting();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Beranda') ?> · Arsip Surat</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="mark">Arsip.</div>
            <strong><?= e($set['nama_instansi']) ?></strong>
            <small>Sistem Persuratan</small>
        </div>
        <nav>
            <?php foreach ($menu as $section => $items): ?>
                <div class="nav-section"><?= $section ?></div>
                <?php foreach ($items as [$key, $label, $ic, $count]): ?>
                    <a href="<?= url($key) ?>" class="<?= $current === $key ? 'active' : '' ?>"><?= icon($ic, 17) ?> <?= $label ?>
                        <?php if ($count): ?><span class="count"><?= $count ?></span><?php endif; ?></a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </nav>
        <div class="grow"></div>
        <div class="user-box">
            <div class="grow"><strong><?= e($u['nama']) ?></strong><small><?= e(ucfirst($u['role'])) ?></small></div>
            <a href="<?= url('logout') ?>" class="icon-btn" title="Keluar"><?= icon('logout', 17) ?></a>
        </div>
    </aside>
    <main class="main">
        <header class="topbar">
            <div>
                <button class="icon-btn menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><?= icon('menu') ?></button>
                <div class="eyebrow"><?= e($eyebrow ?? 'Sistem Arsip Surat') ?></div>
                <h1><?= e($title ?? 'Beranda') ?></h1>
            </div>
            <div class="topbar-actions"><?= $actions ?? '' ?></div>
        </header>
        <div class="content">
            <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div><?php endif; ?>
