<?php
$id = (int) ($_GET['id'] ?? 0);
$s = $id ? q('SELECT * FROM surat_keluar WHERE id = ?', [$id])->fetch() : null;
if ($id && (!$s || !is_admin())) redirect('keluar');

$klasifikasi = q('SELECT * FROM klasifikasi ORDER BY kode')->fetchAll();
$bagian = q('SELECT * FROM bagian ORDER BY nama')->fetchAll();
$data = $s ?: ['tgl_surat' => date('Y-m-d'), 'tujuan' => '', 'perihal' => '', 'klasifikasi_id' => '', 'sifat' => 'Biasa',
    'bagian_id' => user()['bagian_id'] ?? '', 'file' => null, 'nomor_surat' => ''];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Saat ubah, tanggal & klasifikasi dikunci karena sudah membentuk nomor surat
    foreach ($id ? ['tujuan', 'perihal', 'sifat', 'bagian_id'] : ['tgl_surat', 'tujuan', 'perihal', 'klasifikasi_id', 'sifat', 'bagian_id'] as $f) $data[$f] = trim($_POST[$f] ?? '');
    try {
        if ($data['tujuan'] === '' || $data['perihal'] === '' || !$data['klasifikasi_id']) throw new RuntimeException('Tujuan, perihal, dan klasifikasi wajib diisi.');
        $kode = q('SELECT kode FROM klasifikasi WHERE id = ?', [(int) $data['klasifikasi_id']])->fetchColumn();
        if (!$kode) throw new RuntimeException('Klasifikasi tidak valid.');
        $file = simpan_lampiran('file');
        if ($file) {
            if (!empty($s['file'])) @unlink(__DIR__ . '/../../uploads/' . $s['file']);
            $data['file'] = $file;
        }
        if ($id) {
            q('UPDATE surat_keluar SET tujuan=?, perihal=?, sifat=?, bagian_id=?, file=? WHERE id=?',
                [$data['tujuan'], $data['perihal'], $data['sifat'], $data['bagian_id'] ?: null, $data['file'], $id]);
            flash('Surat keluar diperbarui.');
        } else {
            // Kunci tabel lewat transaksi agar dua user tidak mendapat nomor urut yang sama
            db()->beginTransaction();
            q('SELECT id FROM surat_keluar WHERE YEAR(tgl_surat) = ? FOR UPDATE', [date('Y', strtotime($data['tgl_surat']))]);
            [$urut, $nomor] = nomor_surat_keluar($data['tgl_surat'], $kode);
            q('INSERT INTO surat_keluar (no_urut, nomor_surat, tgl_surat, tujuan, perihal, klasifikasi_id, sifat, bagian_id, file, user_id) VALUES (?,?,?,?,?,?,?,?,?,?)',
                [$urut, $nomor, $data['tgl_surat'], $data['tujuan'], $data['perihal'], (int) $data['klasifikasi_id'], $data['sifat'], $data['bagian_id'] ?: null, $data['file'], user()['id']]);
            db()->commit();
            flash("Nomor surat dibuat: $nomor");
        }
        redirect('keluar');
    } catch (RuntimeException $ex) {
        if (db()->inTransaction()) db()->rollBack();
        $error = $ex->getMessage();
    }
}

$title = $id ? 'Ubah Surat Keluar' : 'Buat Nomor Surat Keluar';
$eyebrow = 'Surat Keluar';
require __DIR__ . '/../../includes/header.php';
?>
<form method="post" enctype="multipart/form-data" class="panel" style="max-width:860px">
    <?= csrf_field() ?>
    <div class="panel-body">
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <?php if ($id): ?><p class="mono" style="font-size:16px;margin:0 0 20px">Nomor: <strong style="display:inline"><?= e($data['nomor_surat']) ?></strong></p><?php endif; ?>
        <div class="form-grid">
            <div><label>Tanggal Surat</label><input type="date" name="tgl_surat" value="<?= e($data['tgl_surat']) ?>" <?= $id ? 'disabled' : '' ?> required></div>
            <div><label>Klasifikasi</label>
                <select name="klasifikasi_id" <?= $id ? 'disabled' : '' ?> required><option value="">Pilih klasifikasi</option>
                    <?php foreach ($klasifikasi as $k): ?><option value="<?= $k['id'] ?>" <?= $data['klasifikasi_id'] == $k['id'] ? 'selected' : '' ?>><?= e($k['kode'] . ' · ' . $k['nama']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="full"><label>Tujuan</label><input type="text" name="tujuan" value="<?= e($data['tujuan']) ?>" placeholder="Kepala Dinas Pendidikan Kab. Sukamaju" required></div>
            <div class="full"><label>Perihal</label><input type="text" name="perihal" value="<?= e($data['perihal']) ?>" required></div>
            <div><label>Sifat</label><select name="sifat"><?php foreach (['Biasa', 'Penting', 'Segera', 'Rahasia'] as $o): ?><option <?= $data['sifat'] === $o ? 'selected' : '' ?>><?= $o ?></option><?php endforeach; ?></select></div>
            <div><label>Bagian Pembuat</label><select name="bagian_id"><option value="">-</option><?php foreach ($bagian as $b): ?><option value="<?= $b['id'] ?>" <?= $data['bagian_id'] == $b['id'] ? 'selected' : '' ?>><?= e($b['nama']) ?></option><?php endforeach; ?></select></div>
            <div class="full"><label>Arsip Surat (PDF/JPG/PNG, opsional)</label><input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png">
                <?php if (!$id): ?><div class="hint">Format nomor otomatis: <span class="mono">urut/kode klasifikasi/<?= e(setting()['kode_instansi']) ?>/bulan romawi/tahun</span>, contoh <span class="mono">008/005/<?= e(setting()['kode_instansi']) ?>/<?= romawi((int) date('n')) ?>/<?= date('Y') ?></span>.</div><?php endif; ?>
            </div>
        </div>
        <div class="form-actions"><button class="btn btn-primary"><?= $id ? 'Simpan' : 'Buat Nomor & Simpan' ?></button><a class="btn" href="<?= url('keluar') ?>">Batal</a></div>
    </div>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
