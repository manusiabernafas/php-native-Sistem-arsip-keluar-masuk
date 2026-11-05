<?php
$id = (int) ($_POST['id'] ?? 0);
$d = q('SELECT * FROM disposisi WHERE id = ?', [$id])->fetch();
$u = user();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$d || ($u['role'] === 'staf' && $u['bagian_id'] != $d['ke_bagian_id'])) redirect('disposisi');

db()->beginTransaction();
q("UPDATE disposisi SET status = 'selesai', tanggapan = ? WHERE id = ?", [trim($_POST['tanggapan'] ?? ''), $id]);
// Surat dianggap selesai bila semua disposisinya sudah ditindaklanjuti
$sisa = (int) q("SELECT COUNT(*) FROM disposisi WHERE surat_masuk_id = ? AND status <> 'selesai'", [$d['surat_masuk_id']])->fetchColumn();
if ($sisa === 0) q("UPDATE surat_masuk SET status = 'selesai' WHERE id = ?", [$d['surat_masuk_id']]);
db()->commit();
flash('Tindak lanjut disposisi tersimpan.' . ($sisa === 0 ? ' Surat ditandai selesai.' : ''));
redirect('disposisi');
