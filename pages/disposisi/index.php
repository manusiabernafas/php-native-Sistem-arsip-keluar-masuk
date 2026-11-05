<?php
$u = user();
$status = $_GET['status'] ?? 'aktif';
$where = [];
$params = [];
if ($u['role'] === 'staf') { $where[] = 'd.ke_bagian_id = ?'; $params[] = (int) $u['bagian_id']; }
if ($status === 'aktif') $where[] = "d.status <> 'selesai'";
elseif (in_array($status, ['belum', 'dibaca', 'selesai'], true)) { $where[] = 'd.status = ?'; $params[] = $status; }

$rows = q('SELECT d.*, b.nama bagian, s.perihal, s.asal, s.no_agenda, s.sifat, s.id sid FROM disposisi d
           JOIN bagian b ON b.id = d.ke_bagian_id JOIN surat_masuk s ON s.id = d.surat_masuk_id'
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY d.status = \'selesai\', d.batas_waktu, d.id DESC LIMIT 200', $params)->fetchAll();

$title = $u['role'] === 'staf' ? 'Disposisi untuk Bagian Saya' : 'Disposisi';
$eyebrow = 'Persuratan';
require __DIR__ . '/../../includes/header.php';
?>
<div class="panel">
    <div class="panel-head">
        <h2><?= count($rows) ?> disposisi</h2>
        <div class="filters">
            <?php foreach (['aktif' => 'Perlu tindakan', 'belum' => 'Belum dibaca', 'selesai' => 'Selesai', 'semua' => 'Semua'] as $k => $v): ?>
                <a class="btn btn-sm <?= $status === $k ? 'btn-primary' : '' ?>" href="<?= url('disposisi', ['status' => $k]) ?>"><?= $v ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="table-wrap"><table>
        <thead><tr><th>Surat</th><th>Kepada</th><th>Instruksi</th><th>Batas</th><th>Status</th><th>Tindak Lanjut</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $d): $lewat = $d['status'] !== 'selesai' && $d['batas_waktu'] && $d['batas_waktu'] < date('Y-m-d'); ?>
            <tr>
                <td><a href="<?= url('masuk.detail', ['id' => $d['sid']]) ?>"><strong><?= e($d['perihal']) ?></strong></a><small><span class="mono"><?= e($d['no_agenda']) ?></span> · <?= e($d['asal']) ?></small></td>
                <td><?= e($d['bagian']) ?></td>
                <td><?= e($d['instruksi']) ?><?php if ($d['catatan']): ?><small><?= e($d['catatan']) ?></small><?php endif; ?></td>
                <td style="<?= $lewat ? 'color:var(--red);font-weight:600' : '' ?>"><?= tgl($d['batas_waktu']) ?></td>
                <td><?= status_badge($d['status']) ?></td>
                <td>
                    <?php if ($d['status'] !== 'selesai' && ($u['role'] !== 'staf' || $u['bagian_id'] == $d['ke_bagian_id'])): ?>
                        <form method="post" action="<?= url('disposisi.respon') ?>" style="display:flex;gap:6px;min-width:260px">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $d['id'] ?>">
                            <input type="text" name="tanggapan" placeholder="Tanggapan / hasil tindak lanjut" required>
                            <button class="btn btn-sm btn-primary" title="Tandai selesai"><?= icon('check', 14) ?></button>
                        </form>
                    <?php else: ?>
                        <small><?= e($d['tanggapan'] ?? '-') ?></small>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="6" class="empty">Tidak ada disposisi pada filter ini.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>
<?php
// Disposisi yang ditampilkan ke staf otomatis ditandai "dibaca"
if ($u['role'] === 'staf') q("UPDATE disposisi SET status = 'dibaca' WHERE status = 'belum' AND ke_bagian_id = ?", [(int) $u['bagian_id']]);
require __DIR__ . '/../../includes/footer.php';
