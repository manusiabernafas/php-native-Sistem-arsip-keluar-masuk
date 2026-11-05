<?php
$title = 'Surat Masuk';
$eyebrow = 'Persuratan';
$actions = '<a href="' . url('masuk.form') . '" class="btn btn-primary">' . icon('plus', 16) . ' Catat Surat Masuk</a>';

$cari = trim($_GET['cari'] ?? '');
$status = $_GET['status'] ?? '';
$sifat = $_GET['sifat'] ?? '';
$bulan = $_GET['bulan'] ?? '';

$where = ['1=1'];
$params = [];
if ($cari !== '') {
    $where[] = '(s.no_surat LIKE ? OR s.asal LIKE ? OR s.perihal LIKE ? OR s.no_agenda = ?)';
    array_push($params, "%$cari%", "%$cari%", "%$cari%", $cari);
}
if (in_array($status, ['baru', 'didisposisi', 'selesai'], true)) { $where[] = 's.status = ?'; $params[] = $status; }
if (in_array($sifat, ['Biasa', 'Penting', 'Segera', 'Rahasia'], true)) { $where[] = 's.sifat = ?'; $params[] = $sifat; }
if (preg_match('/^\d{4}-\d{2}$/', $bulan)) { $where[] = "DATE_FORMAT(s.tgl_terima, '%Y-%m') = ?"; $params[] = $bulan; }

$rows = q('SELECT s.*, k.kode, k.nama klas FROM surat_masuk s LEFT JOIN klasifikasi k ON k.id = s.klasifikasi_id
           WHERE ' . implode(' AND ', $where) . ' ORDER BY s.tgl_terima DESC, s.id DESC LIMIT 200', $params)->fetchAll();

require __DIR__ . '/../../includes/header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2><?= count($rows) ?> surat</h2>
        <form class="filters" method="get">
            <input type="hidden" name="page" value="masuk">
            <input type="search" name="cari" placeholder="No. agenda, nomor, asal, perihal" value="<?= e($cari) ?>">
            <input type="month" name="bulan" value="<?= e($bulan) ?>" style="padding:9px 10px;border:1px solid var(--line)">
            <select name="sifat"><option value="">Semua sifat</option><?php foreach (['Biasa', 'Penting', 'Segera', 'Rahasia'] as $o): ?><option <?= $sifat === $o ? 'selected' : '' ?>><?= $o ?></option><?php endforeach; ?></select>
            <select name="status"><option value="">Semua status</option><?php foreach (['baru', 'didisposisi', 'selesai'] as $o): ?><option value="<?= $o ?>" <?= $status === $o ? 'selected' : '' ?>><?= ucfirst($o) ?></option><?php endforeach; ?></select>
            <button class="btn"><?= icon('search', 16) ?></button>
        </form>
    </div>
    <div class="table-wrap"><table>
        <thead><tr><th>Agenda</th><th>Asal & Nomor Surat</th><th>Perihal</th><th>Diterima</th><th>Sifat</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $s): ?>
            <tr>
                <td class="mono"><strong><?= e($s['no_agenda']) ?></strong></td>
                <td><strong><?= e($s['asal']) ?></strong><small class="mono"><?= e($s['no_surat']) ?></small></td>
                <td><a href="<?= url('masuk.detail', ['id' => $s['id']]) ?>"><?= e($s['perihal']) ?></a><small><?= e($s['kode'] . ' ' . $s['klas']) ?><?= $s['file'] ? ' · berkas terlampir' : '' ?></small></td>
                <td><?= tgl($s['tgl_terima']) ?></td>
                <td><?= status_badge($s['sifat']) ?></td>
                <td><?= status_badge($s['status']) ?></td>
                <td class="actions">
                    <a class="icon-btn" title="Detail & disposisi" href="<?= url('masuk.detail', ['id' => $s['id']]) ?>"><?= icon('eye', 16) ?></a>
                    <?php if (is_admin()): ?><a class="icon-btn" title="Ubah" href="<?= url('masuk.form', ['id' => $s['id']]) ?>"><?= icon('edit', 16) ?></a><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="7" class="empty">Tidak ada surat yang cocok.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
