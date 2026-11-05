# Sistem Arsip Surat Masuk & Keluar + Disposisi (PHP Native + MySQL)

Sistem Arsip Surat Masuk dan Surat Keluar lengkap dengan alur disposisi, dibuat dengan PHP Native dan MySQL. Cocok untuk kantor dinas, sekolah, kelurahan, desa, kampus, maupun perusahaan yang masih mencatat surat di buku agenda manual. Setiap surat masuk mendapat nomor agenda otomatis, bisa dilampiri scan PDF, lalu didisposisikan pimpinan ke bagian terkait dan dipantau sampai selesai ditindaklanjuti.

Nomor surat keluar dibuat otomatis mengikuti format resmi (nomor urut/kode klasifikasi/kode instansi/bulan romawi/tahun), sehingga tidak ada lagi nomor ganda atau loncat. Lembar disposisi bisa dicetak lengkap dengan kop instansi, dan buku agenda bisa diekspor ke Excel. Tampilannya minimalis dan rapi, cocok untuk dipakai sehari-hari atau sebagai bahan tugas kuliah dan skripsi sistem informasi persuratan.

## Fitur

- Pencatatan surat masuk dengan nomor agenda otomatis (berurutan per tahun)
- Upload scan surat (PDF/JPG/PNG maks 5 MB) dengan validasi tipe file
- File lampiran terlindungi: hanya bisa dibuka setelah login
- Disposisi ke satu atau beberapa bagian sekaligus, dengan instruksi, catatan, dan batas waktu
- Kotak disposisi per bagian dengan penanda jumlah disposisi baru
- Status disposisi Belum, Dibaca, Selesai, dan tanggapan tindak lanjut dari staf
- Surat otomatis Selesai saat semua disposisinya sudah ditindaklanjuti
- Penanda disposisi yang melewati batas waktu
- Cetak lembar disposisi lengkap dengan kop instansi dan tanda tangan pimpinan
- Surat keluar dengan nomor otomatis format resmi: 001/005/KODE/X/2026
- Nomor urut surat keluar reset tiap tahun dan aman dari nomor ganda (transaksi database)
- Klasifikasi surat (kode arsip) dan sifat surat (Biasa, Penting, Segera, Rahasia)
- Pencarian dan filter surat per bulan, sifat, dan status
- Buku agenda surat masuk dan keluar per periode, bisa dicetak dan diekspor ke CSV (Excel)
- Dashboard: statistik bulan ini, grafik arus surat 6 bulan, beban disposisi per bagian
- 3 peran: Operator Arsip, Pimpinan, Staf Bagian
- Data master: profil instansi, kode instansi, bagian, klasifikasi, pengguna
- Keamanan: PDO prepared statement, password_hash, proteksi CSRF, validasi MIME file
- Data contoh 6 bulan (150+ surat masuk, 100+ surat keluar)
- Tampilan minimalis editorial yang bersih dan responsif

## Instalasi

**Kebutuhan:** PHP 8.0+ (ekstensi `fileinfo` aktif, bawaan XAMPP sudah aktif) dan MySQL/MariaDB.

1. Ekstrak ZIP, lalu pindahkan folder `arsip-surat` ke:
   - XAMPP: `C:\xampp\htdocs\arsip-surat`
   - Laragon: `C:\laragon\www\arsip-surat`

2. Jalankan **Apache** dan **MySQL**.

3. Buka `http://localhost/phpmyadmin` → **Import** → pilih `database/arsip_surat.sql` → **Import**.

   Atau lewat terminal:
   ```bash
   mysql -u root -p < database/arsip_surat.sql
   ```

4. Sesuaikan `config/database.php` jika user/password MySQL kamu berbeda:
   ```php
   return [
       'host'     => 'localhost',
       'port'     => 3306,
       'database' => 'arsip_surat',
       'username' => 'root',
       'password' => '',
   ];
   ```

5. Buka `http://localhost/arsip-surat` dan login (password semua akun: `admin123`):
   - `admin`: Operator Arsip (mencatat surat, data master)
   - `kadis`: Pimpinan (membuat disposisi)
   - `aptika`: Staf Bidang Aplikasi Informatika (menindaklanjuti disposisi)

6. Masuk ke **Data Master** untuk mengganti nama instansi, kode instansi (dipakai di nomor surat), nama pimpinan, bagian, dan klasifikasi.

**Catatan keamanan:** folder `uploads/` sudah dilindungi `.htaccess` di Apache. Jika memakai Nginx, tambahkan aturan `deny all` untuk folder tersebut.

---
Source code ini dibagikan gratis oleh **Papicode** — https://papicode.id
