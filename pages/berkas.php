<?php
// File lampiran disimpan di luar akses publik (uploads/.htaccess) dan hanya bisa dibuka setelah login
$f = basename($_GET['f'] ?? '');
$path = __DIR__ . '/../uploads/' . $f;
$terdaftar = q('SELECT 1 FROM surat_masuk WHERE file = ? UNION SELECT 1 FROM surat_keluar WHERE file = ?', [$f, $f])->fetchColumn();
if ($f === '' || !$terdaftar || !is_file($path)) {
    http_response_code(404);
    exit('Berkas tidak ditemukan.');
}
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $f . '"');
header('X-Content-Type-Options: nosniff');
readfile($path);
