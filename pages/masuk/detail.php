<?php
$id = (int) ($_GET['id'] ?? 0);
$s = q('SELECT s.*, k.kode, k.nama klas, u.nama pencatat FROM surat_masuk s LEFT JOIN klasifikasi k ON k.id = s.klasifikasi_id
        LEFT JOIN users u ON u.id = s.user_id WHERE s.id = ?', [$id])->fetch();
if (!$s) redirect('masuk');

$disposisi = q('SELECT d.*, b.nama bagian, u.nama dari FROM disposisi d JOIN bagian b ON b.id = d.ke_bagian_id
                LEFT JOIN users u ON u.id = d.dari_user_id WHERE d.surat_masuk_id = ? ORDER BY d.id', [$id])->fetchAll();
$bagian = q('SELECT * FROM bagian ORDER BY nama')->fetchAll();
$bisaDisposisi = in_array(user()['role'], ['admin', 'pimpinan'], true) && $s['status'] !== 'selesai';
$instruksi = ['Tindak lanjuti', 'Hadiri dan laporkan', 'Pelajari dan beri saran', 'Siapkan bahan', 'Untuk diketahui', 'Koordinasikan dengan bidang terkait', 'Arsipkan'];

$title = $s['perihal'];
$eyebrow = 'Surat Masuk · Agenda ' . $s['no_agenda'];
$actions = '<a class="btn" href="' . url('masuk.lembar', ['id' => $id]) . '" target="_blank">' . icon('printer', 16) . ' Lembar Disposisi</a>'
    . ($s['file'] ? '<a class="btn" href="' . url('berkas', ['f' => $s['file']]) . '" target="_blank">' . icon('paperclip', 16) . ' Lihat Scan</a>' : '');
require __DIR__ . '/../../includes/header.php';
?>
<div class="cols">
    <div>
        <div class="panel">
            <div class="panel-head"><h2>Data Surat</h2><span><?= status_badge($s['sifat']) ?> <?= status_badge($s['status']) ?></span></div>
            <div class="panel-body">
                <dl class="meta">
                    <dt>No. Agenda</dt><dd class="mono"><?= e($s['no_agenda']) ?></dd>
                    <dt>Nomor Surat</dt><dd class="mono"><?= e($s['no_surat']) ?></dd>
                    <dt>Asal</dt><dd><?= e($s['asal']) ?></dd>
                    <dt>Perihal</dt><dd><?= e($s['perihal']) ?></dd>
                    <dt>Klasifikasi</dt><dd><?= e(trim($s['kode'] . ' · ' . $s['klas'], ' ·')) ?: '-' ?></dd>
                    <dt>Tanggal Surat</dt><dd><?= tgl($s['tgl_surat']) ?></dd>
                    <dt>Diterima</dt><dd><?= tgl($s['tgl_terima']) ?> · dicatat oleh <?= e($s['pencatat'] ?? '-') ?></dd>
                    <dt>Lampiran</dt><dd><?= e($s['lampiran'] ?: '-') ?></dd>
                </dl>
            </div>
        </div>

        <div class="panel mt">
            <div class="panel-head"><h2>Riwayat Disposisi</h2><span class="muted"><?= count($disposisi) ?> catatan</span></div>
            <div class="panel-body">
                <?php if (!$disposisi): ?><p class="empty" style="padding:10px">Belum ada disposisi untuk surat ini.</p><?php endif; ?>
                <ul class="timeline">
                    <?php foreach ($disposisi as $d): ?>
                        <li>
                            <strong><?= e($d['dari'] ?? 'Pimpinan') ?> → <?= e($d['bagian']) ?></strong>
                            <small class="muted"><?= tgl($d['created_at']) ?><?= $d['batas_waktu'] ? ' · batas ' . tgl($d['batas_waktu']) : '' ?></small> <?= status_badge($d['status']) ?>
                            <p class="quote">“<?= e($d['instruksi']) ?><?= $d['catatan'] ? ': ' . e($d['catatan']) : '' ?>”</p>
                            <?php if ($d['tanggapan']): ?><p style="margin:8px 0 0"><span class="muted">Tanggapan:</span> <?= e($d['tanggapan']) ?></p><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div>
        <?php if ($bisaDisposisi): ?>
            <form method="post" action="<?= url('disposisi.simpan') ?>" class="panel">
                <?= csrf_field() ?><input type="hidden" name="surat_masuk_id" value="<?= $id ?>">
                <div class="panel-head"><h2>Buat Disposisi</h2></div>
                <div class="panel-body">
                    <label>Diteruskan ke</label>
                    <?php foreach ($bagian as $b): ?>
                        <label style="text-transform:none;letter-spacing:0;font-size:14px;color:var(--ink);font-weight:400;display:flex;gap:8px;align-items:center;margin:6px 0">
                            <input type="checkbox" name="bagian[]" value="<?= $b['id'] ?>"> <?= e($b['nama']) ?></label>
                    <?php endforeach; ?>
                    <div class="mt" style="margin-top:18px"><label>Instruksi</label>
                        <select name="instruksi"><?php foreach ($instruksi as $i): ?><option><?= $i ?></option><?php endforeach; ?></select></div>
                    <div style="margin-top:14px"><label>Catatan</label><textarea name="catatan" rows="3"></textarea></div>
                    <div style="margin-top:14px"><label>Batas Waktu</label><input type="date" name="batas_waktu" value="<?= date('Y-m-d', strtotime('+5 days')) ?>"></div>
                    <button class="btn btn-primary btn-block" style="margin-top:20px"><?= icon('share', 16) ?> Kirim Disposisi</button>
                </div>
            </form>
        <?php endif; ?>
        <?php if (is_admin()): ?>
            <div class="panel <?= $bisaDisposisi ? 'mt' : '' ?>">
                <div class="panel-head"><h2>Tindakan</h2></div>
                <div class="panel-body" style="display:flex;gap:8px;flex-wrap:wrap">
                    <a class="btn" href="<?= url('masuk.form', ['id' => $id]) ?>"><?= icon('edit', 16) ?> Ubah</a>
                    <form method="post" action="<?= url('masuk.hapus') ?>" data-confirm="Hapus surat agenda <?= e($s['no_agenda']) ?> beserta disposisinya?">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
                        <button class="btn"><?= icon('trash', 16) ?> Hapus</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
