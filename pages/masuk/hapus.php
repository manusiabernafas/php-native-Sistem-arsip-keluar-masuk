<?php
require_admin();
$id = (int) ($_POST['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    $file = q('SELECT file FROM surat_masuk WHERE id = ?', [$id])->fetchColumn();
    q('DELETE FROM surat_masuk WHERE id = ?', [$id]); // disposisi ikut terhapus (ON DELETE CASCADE)
    if ($file) @unlink(__DIR__ . '/../../uploads/' . $file);
    flash('Surat masuk dihapus.');
}
redirect('masuk');
