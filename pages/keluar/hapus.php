<?php
require_admin();
$id = (int) ($_POST['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    $file = q('SELECT file FROM surat_keluar WHERE id = ?', [$id])->fetchColumn();
    q('DELETE FROM surat_keluar WHERE id = ?', [$id]);
    if ($file) @unlink(__DIR__ . '/../../uploads/' . $file);
    flash('Surat keluar dihapus. Nomor urutnya tidak dipakai ulang.');
}
redirect('keluar');
