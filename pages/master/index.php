<?php
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    try {
        switch ($aksi) {
            case 'instansi':
                q('UPDATE pengaturan SET nama_instansi=?, kode_instansi=?, alamat=?, telepon=?, pimpinan=?, jabatan_pimpinan=? WHERE id=1', [
                    trim($_POST['nama_instansi']), strtoupper(preg_replace('/[^A-Za-z0-9.\-]/', '', $_POST['kode_instansi'])),
                    trim($_POST['alamat']), trim($_POST['telepon']), trim($_POST['pimpinan']), trim($_POST['jabatan_pimpinan']),
                ]);
                break;
            case 'bagian':
                if (trim($_POST['nama']) === '') throw new RuntimeException('Nama bagian wajib diisi.');
                q('INSERT INTO bagian (nama) VALUES (?)', [trim($_POST['nama'])]);
                break;
            case 'klasifikasi':
                if (!preg_match('/^[0-9.]{1,10}$/', $_POST['kode']) || trim($_POST['nama']) === '') throw new RuntimeException('Kode klasifikasi berupa angka dan nama wajib diisi.');
                q('INSERT INTO klasifikasi (kode, nama) VALUES (?,?)', [$_POST['kode'], trim($_POST['nama'])]);
                break;
            case 'user':
                if (strlen($_POST['password']) < 6 || trim($_POST['username']) === '') throw new RuntimeException('Username wajib dan password minimal 6 karakter.');
                $role = in_array($_POST['role'], ['admin', 'pimpinan', 'staf'], true) ? $_POST['role'] : 'staf';
                q('INSERT INTO users (nama, username, password, role, bagian_id) VALUES (?,?,?,?,?)', [
                    trim($_POST['nama']), trim($_POST['username']), password_hash($_POST['password'], PASSWORD_DEFAULT), $role, ((int) $_POST['bagian_id']) ?: null,
                ]);
                break;
        }
        flash('Data tersimpan.');
    } catch (PDOException $ex) {
        flash('Gagal menyimpan: data sudah ada atau sedang dipakai.', 'error');
    } catch (RuntimeException $ex) {
        flash($ex->getMessage(), 'error');
    }
    redirect('master');
}

$set = setting();
$bagian = q('SELECT b.*, COUNT(u.id) jml FROM bagian b LEFT JOIN users u ON u.bagian_id = b.id GROUP BY b.id ORDER BY b.nama')->fetchAll();
$klasifikasi = q('SELECT * FROM klasifikasi ORDER BY kode')->fetchAll();
$users = q('SELECT u.*, b.nama bagian FROM users u LEFT JOIN bagian b ON b.id = u.bagian_id ORDER BY u.role, u.nama')->fetchAll();

$title = 'Data Master';
$eyebrow = 'Sistem';
require __DIR__ . '/../../includes/header.php';
?>
<form method="post" class="panel">
    <?= csrf_field() ?><input type="hidden" name="aksi" value="instansi">
    <div class="panel-head"><h2>Profil Instansi</h2><span class="muted">Muncul di kop lembar disposisi & nomor surat</span></div>
    <div class="panel-body">
        <div class="form-grid">
            <div class="full"><label>Nama Instansi</label><input type="text" name="nama_instansi" value="<?= e($set['nama_instansi']) ?>" required></div>
            <div><label>Kode Instansi (untuk nomor surat)</label><input type="text" name="kode_instansi" value="<?= e($set['kode_instansi']) ?>" required></div>
            <div><label>Telepon</label><input type="text" name="telepon" value="<?= e($set['telepon']) ?>"></div>
            <div class="full"><label>Alamat</label><input type="text" name="alamat" value="<?= e($set['alamat']) ?>"></div>
            <div><label>Nama Pimpinan</label><input type="text" name="pimpinan" value="<?= e($set['pimpinan']) ?>"></div>
            <div><label>Jabatan Pimpinan</label><input type="text" name="jabatan_pimpinan" value="<?= e($set['jabatan_pimpinan']) ?>"></div>
        </div>
        <div class="form-actions"><button class="btn btn-primary">Simpan Profil</button></div>
    </div>
</form>

<div class="cols mt">
    <div class="panel">
        <div class="panel-head"><h2>Klasifikasi Surat</h2></div>
        <div class="table-wrap" style="max-height:360px;overflow:auto"><table>
            <thead><tr><th>Kode</th><th>Nama</th></tr></thead>
            <tbody><?php foreach ($klasifikasi as $k): ?><tr><td class="mono"><?= e($k['kode']) ?></td><td><?= e($k['nama']) ?></td></tr><?php endforeach; ?></tbody>
        </table></div>
        <form method="post" class="panel-body filters" style="border-top:1px solid var(--line)">
            <?= csrf_field() ?><input type="hidden" name="aksi" value="klasifikasi">
            <input type="text" name="kode" placeholder="Kode" style="width:90px" required>
            <input type="text" name="nama" placeholder="Nama klasifikasi" required>
            <button class="btn"><?= icon('plus', 16) ?></button>
        </form>
    </div>
    <div class="panel">
        <div class="panel-head"><h2>Bagian / Bidang</h2></div>
        <div class="table-wrap"><table>
            <tbody><?php foreach ($bagian as $b): ?><tr><td><?= e($b['nama']) ?></td><td class="text-right muted"><?= $b['jml'] ?> akun</td></tr><?php endforeach; ?></tbody>
        </table></div>
        <form method="post" class="panel-body filters" style="border-top:1px solid var(--line)">
            <?= csrf_field() ?><input type="hidden" name="aksi" value="bagian">
            <input type="text" name="nama" placeholder="Nama bagian baru" required>
            <button class="btn"><?= icon('plus', 16) ?></button>
        </form>
    </div>
</div>

<div class="panel mt">
    <div class="panel-head"><h2>Pengguna</h2></div>
    <div class="table-wrap"><table>
        <thead><tr><th>Nama</th><th>Username</th><th>Peran</th><th>Bagian</th></tr></thead>
        <tbody><?php foreach ($users as $x): ?><tr><td><?= e($x['nama']) ?></td><td class="mono"><?= e($x['username']) ?></td><td><?= e(ucfirst($x['role'])) ?></td><td><?= e($x['bagian'] ?? '-') ?></td></tr><?php endforeach; ?></tbody>
    </table></div>
    <form method="post" class="panel-body" style="border-top:1px solid var(--line)">
        <?= csrf_field() ?><input type="hidden" name="aksi" value="user">
        <div class="form-grid" style="grid-template-columns:repeat(5,1fr)">
            <div><label>Nama</label><input type="text" name="nama" required></div>
            <div><label>Username</label><input type="text" name="username" required></div>
            <div><label>Password</label><input type="password" name="password" minlength="6" required></div>
            <div><label>Peran</label><select name="role"><option value="staf">Staf</option><option value="pimpinan">Pimpinan</option><option value="admin">Admin</option></select></div>
            <div><label>Bagian</label><select name="bagian_id"><option value="">-</option><?php foreach ($bagian as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['nama']) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="form-actions"><button class="btn btn-primary"><?= icon('plus', 16) ?> Tambah Pengguna</button></div>
    </form>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
