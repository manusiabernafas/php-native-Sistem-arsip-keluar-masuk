<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array(user()['role'], ['admin', 'pimpinan'], true)) redirect('masuk');

$sid = (int) ($_POST['surat_masuk_id'] ?? 0);
$bagian = array_map('intval', $_POST['bagian'] ?? []);
if (!$bagian) {
    flash('Pilih minimal satu bagian tujuan disposisi.', 'error');
    redirect('masuk.detail', ['id' => $sid]);
}
db()->beginTransaction();
foreach ($bagian as $b) {
    q('INSERT INTO disposisi (surat_masuk_id, dari_user_id, ke_bagian_id, instruksi, catatan, batas_waktu) VALUES (?,?,?,?,?,?)', [
        $sid, user()['id'], $b, trim($_POST['instruksi'] ?? 'Tindak lanjuti'), trim($_POST['catatan'] ?? '') ?: null, $_POST['batas_waktu'] ?: null,
    ]);
}
q("UPDATE surat_masuk SET status = 'didisposisi' WHERE id = ? AND status = 'baru'", [$sid]);
db()->commit();
flash('Disposisi dikirim ke ' . count($bagian) . ' bagian.');
redirect('masuk.detail', ['id' => $sid]);
