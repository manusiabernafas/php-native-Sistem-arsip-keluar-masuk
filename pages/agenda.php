<?php
$jenis = ($_GET['jenis'] ?? 'masuk') === 'keluar' ? 'keluar' : 'masuk';
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

if ($jenis === 'masuk') {
    $rows = q('SELECT s.*, k.kode FROM surat_masuk s LEFT JOIN klasifikasi k ON k.id = s.klasifikasi_id
               WHERE s.tgl_terima BETWEEN ? AND ? ORDER BY s.tgl_terima, s.no_agenda', [$dari, $sampai])->fetchAll();
} else {
    $rows = q('SELECT s.*, k.kode FROM surat_keluar s LEFT JOIN klasifikasi k ON k.id = s.klasifikasi_id
               WHERE s.tgl_surat BETWEEN ? AND ? ORDER BY s.tgl_surat, s.no_urut', [$dari, $sampai])->fetchAll();
}

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"agenda-surat-$jenis-$dari-sd-$sampai.csv\"");
    $out = fopen('php://output', 'w');
    if ($jenis === 'masuk') {
        fputcsv($out, ['No Agenda', 'Tgl Terima', 'Nomor Surat', 'Tgl Surat', 'Asal', 'Perihal', 'Klasifikasi', 'Sifat', 'Status'], ';', '"', '');
        foreach ($rows as $r) fputcsv($out, [$r['no_agenda'], $r['tgl_terima'], $r['no_surat'], $r['tgl_surat'], $r['asal'], $r['perihal'], $r['kode'], $r['sifat'], $r['status']], ';', '"', '');
    } else {
        fputcsv($out, ['Nomor Surat', 'Tanggal', 'Tujuan', 'Perihal', 'Klasifikasi', 'Sifat'], ';', '"', '');
        foreach ($rows as $r) fputcsv($out, [$r['nomor_surat'], $r['tgl_surat'], $r['tujuan'], $r['perihal'], $r['kode'], $r['sifat']], ';', '"', '');
    }
    exit;
}

$title = 'Buku Agenda Surat ' . ucfirst($jenis);
$eyebrow = tgl($dari) . ' — ' . tgl($sampai);
$actions = '<a class="btn" href="' . url('agenda', ['jenis' => $jenis, 'dari' => $dari, 'sampai' => $sampai, 'export' => 'csv']) . '">Ekspor CSV</a>'
    . '<button class="btn btn-primary" onclick="window.print()">' . icon('printer', 16) . ' Cetak</button>';
require __DIR__ . '/../includes/header.php';
?>
<form class="panel no-print" method="get" style="margin-bottom:24px">
    <div class="panel-body filters" style="align-items:end">
        <input type="hidden" name="page" value="agenda">
        <div><label>Jenis</label><select name="jenis"><option value="masuk">Surat Masuk</option><option value="keluar" <?= $jenis === 'keluar' ? 'selected' : '' ?>>Surat Keluar</option></select></div>
        <div><label>Dari</label><input type="date" name="dari" value="<?= e($dari) ?>"></div>
        <div><label>Sampai</label><input type="date" name="sampai" value="<?= e($sampai) ?>"></div>
        <button class="btn btn-primary">Tampilkan</button>
    </div>
</form>
<div class="panel">
    <div class="table-wrap"><table>
        <?php if ($jenis === 'masuk'): ?>
            <thead><tr><th>No.</th><th>Diterima</th><th>Nomor & Tgl Surat</th><th>Asal</th><th>Perihal</th><th>Kode</th></tr></thead>
            <tbody><?php foreach ($rows as $r): ?>
                <tr><td class="mono"><?= e($r['no_agenda']) ?></td><td><?= tgl($r['tgl_terima']) ?></td><td class="mono"><?= e($r['no_surat']) ?><small><?= tgl($r['tgl_surat']) ?></small></td>
                    <td><?= e($r['asal']) ?></td><td><?= e($r['perihal']) ?></td><td class="mono"><?= e($r['kode']) ?></td></tr>
            <?php endforeach; ?></tbody>
        <?php else: ?>
            <thead><tr><th>Nomor Surat</th><th>Tanggal</th><th>Tujuan</th><th>Perihal</th><th>Sifat</th></tr></thead>
            <tbody><?php foreach ($rows as $r): ?>
                <tr><td class="mono"><?= e($r['nomor_surat']) ?></td><td><?= tgl($r['tgl_surat']) ?></td><td><?= e($r['tujuan']) ?></td><td><?= e($r['perihal']) ?></td><td><?= e($r['sifat']) ?></td></tr>
            <?php endforeach; ?></tbody>
        <?php endif; ?>
        <?php if (!$rows): ?><tbody><tr><td colspan="6" class="empty">Tidak ada surat pada periode ini.</td></tr></tbody><?php endif; ?>
    </table></div>
</div>
<p class="muted" style="margin-top:14px">Total <?= count($rows) ?> surat.</p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
