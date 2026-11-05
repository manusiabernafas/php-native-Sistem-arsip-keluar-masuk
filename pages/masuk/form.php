<?php
if (!is_admin()) { flash('Hanya operator arsip yang dapat mencatat surat.', 'error'); redirect('masuk'); }

$id = (int) ($_GET['id'] ?? 0);
$s = $id ? q('SELECT * FROM surat_masuk WHERE id = ?', [$id])->fetch() : null;
if ($id && !$s) redirect('masuk');

$klasifikasi = q('SELECT * FROM klasifikasi ORDER BY kode')->fetchAll();
$data = $s ?: ['no_surat' => '', 'tgl_surat' => date('Y-m-d'), 'tgl_terima' => date('Y-m-d'), 'asal' => '', 'perihal' => '',
    'klasifikasi_id' => '', 'sifat' => 'Biasa', 'lampiran' => '', 'file' => null];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['no_surat', 'tgl_surat', 'tgl_terima', 'asal', 'perihal', 'klasifikasi_id', 'sifat', 'lampiran'] as $f) $data[$f] = trim($_POST[$f] ?? '');
    try {
        if ($data['no_surat'] === '' || $data['asal'] === '' || $data['perihal'] === '') throw new RuntimeException('Nomor surat, asal, dan perihal wajib diisi.');
        if ($data['tgl_terima'] < $data['tgl_surat']) throw new RuntimeException('Tanggal terima tidak boleh sebelum tanggal surat.');
        if (!in_array($data['sifat'], ['Biasa', 'Penting', 'Segera', 'Rahasia'], true)) $data['sifat'] = 'Biasa';
        $file = simpan_lampiran('file');
        if ($file) {
            if (!empty($s['file'])) @unlink(__DIR__ . '/../../uploads/' . $s['file']);
            $data['file'] = $file;
        }
        $vals = [$data['no_surat'], $data['tgl_surat'], $data['tgl_terima'], $data['asal'], $data['perihal'],
            $data['klasifikasi_id'] ?: null, $data['sifat'], $data['lampiran'], $data['file']];
        if ($id) {
            q('UPDATE surat_masuk SET no_surat=?, tgl_surat=?, tgl_terima=?, asal=?, perihal=?, klasifikasi_id=?, sifat=?, lampiran=?, file=? WHERE id=?', [...$vals, $id]);
            flash('Surat masuk diperbarui.');
        } else {
            $agenda = nomor_agenda($data['tgl_terima']);
            q('INSERT INTO surat_masuk (no_agenda, no_surat, tgl_surat, tgl_terima, asal, perihal, klasifikasi_id, sifat, lampiran, file, user_id)
               VALUES (?,?,?,?,?,?,?,?,?,?,?)', [$agenda, ...$vals, user()['id']]);
            $id = (int) db()->lastInsertId();
            flash("Surat masuk tercatat dengan nomor agenda $agenda. Silakan buat disposisi.");
        }
        redirect('masuk.detail', ['id' => $id]);
    } catch (RuntimeException $ex) {
        $error = $ex->getMessage();
    }
}

$title = $id ? 'Ubah Surat Masuk' : 'Catat Surat Masuk';
$eyebrow = 'Surat Masuk';
require __DIR__ . '/../../includes/header.php';
?>
<form method="post" enctype="multipart/form-data" class="panel" style="max-width:860px">
    <?= csrf_field() ?>
    <div class="panel-body">
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <div class="form-grid">
            <div class="full"><label>Asal Surat (Pengirim)</label><input type="text" name="asal" value="<?= e($data['asal']) ?>" placeholder="Sekretariat Daerah Kab. Sukamaju" required></div>
            <div><label>Nomor Surat</label><input type="text" name="no_surat" value="<?= e($data['no_surat']) ?>" placeholder="005/123/SETDA/X/2026" required></div>
            <div><label>Klasifikasi</label>
                <select name="klasifikasi_id"><option value="">Pilih klasifikasi</option>
                    <?php foreach ($klasifikasi as $k): ?><option value="<?= $k['id'] ?>" <?= $data['klasifikasi_id'] == $k['id'] ? 'selected' : '' ?>><?= e($k['kode'] . ' · ' . $k['nama']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div><label>Tanggal Surat</label><input type="date" name="tgl_surat" value="<?= e($data['tgl_surat']) ?>" required></div>
            <div><label>Tanggal Diterima</label><input type="date" name="tgl_terima" value="<?= e($data['tgl_terima']) ?>" required></div>
            <div class="full"><label>Perihal</label><input type="text" name="perihal" value="<?= e($data['perihal']) ?>" required></div>
            <div><label>Sifat</label><select name="sifat"><?php foreach (['Biasa', 'Penting', 'Segera', 'Rahasia'] as $o): ?><option <?= $data['sifat'] === $o ? 'selected' : '' ?>><?= $o ?></option><?php endforeach; ?></select></div>
            <div><label>Lampiran</label><input type="text" name="lampiran" value="<?= e($data['lampiran']) ?>" placeholder="1 berkas"></div>
            <div class="full"><label>Scan Surat (PDF/JPG/PNG, maks 5 MB)</label><input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png">
                <?php if ($data['file']): ?><div class="hint">Berkas saat ini: <a href="<?= url('berkas', ['f' => $data['file']]) ?>" target="_blank"><?= e($data['file']) ?></a>. Upload baru untuk mengganti.</div><?php endif; ?>
                <?php if (!$id): ?><div class="hint">Nomor agenda dibuat otomatis berurutan per tahun.</div><?php endif; ?>
            </div>
        </div>
        <div class="form-actions"><button class="btn btn-primary">Simpan</button><a class="btn" href="<?= url('masuk') ?>">Batal</a></div>
    </div>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
