<?php
$id = (int) ($_GET['id'] ?? 0);
$s = q('SELECT s.*, k.kode, k.nama klas FROM surat_masuk s LEFT JOIN klasifikasi k ON k.id = s.klasifikasi_id WHERE s.id = ?', [$id])->fetch();
if (!$s) redirect('masuk');
$disposisi = q('SELECT d.*, b.nama bagian FROM disposisi d JOIN bagian b ON b.id = d.ke_bagian_id WHERE d.surat_masuk_id = ? ORDER BY d.id', [$id])->fetchAll();
$set = setting();
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Lembar Disposisi <?= e($s['no_agenda']) ?></title><link rel="stylesheet" href="assets/css/style.css"></head>
<body style="background:#fff;padding:30px 16px" onload="window.print()">
<div class="sheet">
    <div class="sheet-head">
        <h2><?= e($set['nama_instansi']) ?></h2>
        <div><?= e($set['alamat']) ?> · Telp. <?= e($set['telepon']) ?></div>
    </div>
    <h3>LEMBAR DISPOSISI</h3>
    <table>
        <tr><td style="width:50%">Surat dari: <b><?= e($s['asal']) ?></b></td><td>Diterima tgl: <b><?= tgl($s['tgl_terima']) ?></b></td></tr>
        <tr><td>No. Surat: <b><?= e($s['no_surat']) ?></b></td><td>No. Agenda: <b><?= e($s['no_agenda']) ?></b></td></tr>
        <tr><td>Tgl. Surat: <b><?= tgl($s['tgl_surat']) ?></b></td><td>Sifat: <b><?= e($s['sifat']) ?></b></td></tr>
        <tr><td colspan="2">Perihal: <b><?= e($s['perihal']) ?></b></td></tr>
    </table>
    <table style="margin-top:14px">
        <thead><tr><th style="width:40%">Diteruskan kepada</th><th>Isi disposisi</th><th style="width:20%">Batas waktu</th></tr></thead>
        <tbody>
        <?php foreach ($disposisi as $d): ?>
            <tr><td><?= e($d['bagian']) ?></td><td><?= e($d['instruksi']) ?><?= $d['catatan'] ? '<br>' . e($d['catatan']) : '' ?></td><td><?= tgl($d['batas_waktu']) ?></td></tr>
        <?php endforeach; ?>
        <?php for ($i = count($disposisi); $i < 4; $i++): ?><tr><td style="height:38px"></td><td></td><td></td></tr><?php endfor; ?>
        </tbody>
    </table>
    <div style="margin-top:36px;margin-left:auto;width:280px;text-align:center">
        <?= e($set['jabatan_pimpinan']) ?>,<br><br><br><br>
        <b style="text-decoration:underline"><?= e($set['pimpinan']) ?></b>
    </div>
</div>
</body>
</html>
