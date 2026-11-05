<?php
$title = 'Selamat datang, ' . explode(',', user()['nama'])[0];
$eyebrow = tgl(date('Y-m-d'));
$actions = is_admin() ? '<a href="' . url('masuk.form') . '" class="btn btn-primary">' . icon('plus', 16) . ' Catat Surat Masuk</a>' : '';

$masukBulan = (int) q("SELECT COUNT(*) FROM surat_masuk WHERE DATE_FORMAT(tgl_terima,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')")->fetchColumn();
$keluarBulan = (int) q("SELECT COUNT(*) FROM surat_keluar WHERE DATE_FORMAT(tgl_surat,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')")->fetchColumn();
$belumDisposisi = (int) q("SELECT COUNT(*) FROM surat_masuk WHERE status = 'baru'")->fetchColumn();
$disposisiAktif = (int) q("SELECT COUNT(*) FROM disposisi WHERE status <> 'selesai'")->fetchColumn();
$terlambat = (int) q("SELECT COUNT(*) FROM disposisi WHERE status <> 'selesai' AND batas_waktu < CURDATE()")->fetchColumn();

// 6 bulan terakhir: masuk vs keluar
$bulan = [];
for ($i = 5; $i >= 0; $i--) $bulan[date('Y-m', strtotime("first day of -$i month"))] = ['in' => 0, 'out' => 0];
foreach (q("SELECT DATE_FORMAT(tgl_terima,'%Y-%m') m, COUNT(*) n FROM surat_masuk GROUP BY m")->fetchAll() as $r) if (isset($bulan[$r['m']])) $bulan[$r['m']]['in'] = (int) $r['n'];
foreach (q("SELECT DATE_FORMAT(tgl_surat,'%Y-%m') m, COUNT(*) n FROM surat_keluar GROUP BY m")->fetchAll() as $r) if (isset($bulan[$r['m']])) $bulan[$r['m']]['out'] = (int) $r['n'];
$max = max(1, ...array_values(array_map(fn ($b) => max($b), $bulan)));
$nama = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

$terbaru = q('SELECT * FROM surat_masuk ORDER BY tgl_terima DESC, id DESC LIMIT 6')->fetchAll();
$perBagian = q("SELECT b.nama, SUM(d.status <> 'selesai') aktif, COUNT(d.id) total FROM bagian b
                LEFT JOIN disposisi d ON d.ke_bagian_id = b.id GROUP BY b.id ORDER BY aktif DESC, total DESC")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="figures">
    <div class="figure"><small>Surat masuk bulan ini</small><strong><?= $masukBulan ?></strong><span>tercatat di buku agenda</span></div>
    <div class="figure"><small>Surat keluar bulan ini</small><strong><?= $keluarBulan ?></strong><span>nomor diterbitkan</span></div>
    <div class="figure accent"><small>Menunggu disposisi</small><strong><?= $belumDisposisi ?></strong><span>surat belum diteruskan</span></div>
    <div class="figure"><small>Disposisi berjalan</small><strong><?= $disposisiAktif ?></strong><span><?= $terlambat ?> melewati batas waktu</span></div>
</div>

<div class="cols">
    <div class="panel">
        <div class="panel-head"><h2>Arus Surat, 6 Bulan</h2>
            <div class="legend"><span><i style="background:var(--accent)"></i>Masuk</span><span><i style="background:var(--ink)"></i>Keluar</span></div></div>
        <div class="panel-body">
            <div class="bars">
                <?php foreach ($bulan as $m => $b): ?>
                    <div class="bar-col" title="<?= $b['in'] ?> masuk, <?= $b['out'] ?> keluar">
                        <div class="bar-pair">
                            <div class="bar in" style="height:<?= round($b['in'] / $max * 100) ?>%"></div>
                            <div class="bar out" style="height:<?= round($b['out'] / $max * 100) ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="bar-labels"><?php foreach ($bulan as $m => $b): ?><span><?= $nama[(int) substr($m, 5)] ?></span><?php endforeach; ?></div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><h2>Beban Disposisi</h2><a href="<?= url('disposisi') ?>">Lihat semua</a></div>
        <div class="table-wrap"><table>
            <tbody>
            <?php foreach ($perBagian as $b): ?>
                <tr><td><?= e($b['nama']) ?></td><td class="text-right"><strong style="display:inline;font-family:var(--serif);font-size:18px"><?= (int) $b['aktif'] ?></strong> <small style="display:inline">/ <?= (int) $b['total'] ?></small></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div>
</div>

<div class="panel mt">
    <div class="panel-head"><h2>Surat Masuk Terbaru</h2><a href="<?= url('masuk') ?>">Buka arsip</a></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Agenda</th><th>Perihal</th><th>Asal</th><th>Diterima</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($terbaru as $s): ?>
            <tr>
                <td class="mono"><?= e($s['no_agenda']) ?></td>
                <td><a href="<?= url('masuk.detail', ['id' => $s['id']]) ?>"><?= e($s['perihal']) ?></a></td>
                <td><?= e($s['asal']) ?></td>
                <td><?= tgl($s['tgl_terima']) ?></td>
                <td><?= status_badge($s['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
