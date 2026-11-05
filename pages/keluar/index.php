<?php
$title = 'Surat Keluar';
$eyebrow = 'Persuratan';
$actions = '<a href="' . url('keluar.form') . '" class="btn btn-primary">' . icon('plus', 16) . ' Buat Nomor Surat</a>';

$cari = trim($_GET['cari'] ?? '');
$bulan = $_GET['bulan'] ?? '';
$where = ['1=1'];
$params = [];
if ($cari !== '') {
    $where[] = '(s.nomor_surat LIKE ? OR s.tujuan LIKE ? OR s.perihal LIKE ?)';
    array_push($params, "%$cari%", "%$cari%", "%$cari%");
}
if (preg_match('/^\d{4}-\d{2}$/', $bulan)) { $where[] = "DATE_FORMAT(s.tgl_surat, '%Y-%m') = ?"; $params[] = $bulan; }
$rows = q('SELECT s.*, k.nama klas, b.nama bagian FROM surat_keluar s LEFT JOIN klasifikasi k ON k.id = s.klasifikasi_id
           LEFT JOIN bagian b ON b.id = s.bagian_id WHERE ' . implode(' AND ', $where) . ' ORDER BY s.tgl_surat DESC, s.no_urut DESC LIMIT 200', $params)->fetchAll();

require __DIR__ . '/../../includes/header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2><?= count($rows) ?> surat</h2>
        <form class="filters" method="get">
            <input type="hidden" name="page" value="keluar">
            <input type="search" name="cari" placeholder="Nomor, tujuan, perihal" value="<?= e($cari) ?>">
            <input type="month" name="bulan" value="<?= e($bulan) ?>" style="padding:9px 10px;border:1px solid var(--line)">
            <button class="btn"><?= icon('search', 16) ?></button>
        </form>
    </div>
    <div class="table-wrap"><table>
        <thead><tr><th>Nomor Surat</th><th>Tujuan</th><th>Perihal</th><th>Tanggal</th><th>Sifat</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $s): ?>
            <tr>
                <td class="mono"><strong><?= e($s['nomor_surat']) ?></strong></td>
                <td><?= e($s['tujuan']) ?><small><?= e($s['bagian'] ?? '') ?></small></td>
                <td><?= e($s['perihal']) ?><small><?= e($s['klas'] ?? '') ?><?= $s['file'] ? ' · berkas terlampir' : '' ?></small></td>
                <td><?= tgl($s['tgl_surat']) ?></td>
                <td><?= status_badge($s['sifat']) ?></td>
                <td class="actions">
                    <?php if ($s['file']): ?><a class="icon-btn" title="Lihat berkas" target="_blank" href="<?= url('berkas', ['f' => $s['file']]) ?>"><?= icon('paperclip', 16) ?></a><?php endif; ?>
                    <?php if (is_admin()): ?>
                        <a class="icon-btn" title="Ubah" href="<?= url('keluar.form', ['id' => $s['id']]) ?>"><?= icon('edit', 16) ?></a>
                        <form method="post" action="<?= url('keluar.hapus') ?>" data-confirm="Hapus surat <?= e($s['nomor_surat']) ?>?">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $s['id'] ?>"><button class="icon-btn" title="Hapus"><?= icon('trash', 16) ?></button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="6" class="empty">Tidak ada surat keluar.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
