# **Dokumentasi Sistem Tugas Akhir Mahasiswa**

**🎯 Overview**

Sistem Manajemen Tugas Akhir adalah aplikasi web berbasis PHP dan MySQL yang dirancang untuk mengelola proses tugas akhir mahasiswa secara terintegrasi. Sistem ini mendukung tiga peran pengguna: **Admin**, **Dosen**, dan **Mahasiswa** dengan fitur dan akses yang berbeda sesuai kebutuhan.

**✨ Fitur**

**👨‍💼 Admin**

- **Manajemen Data Mahasiswa** - Tambah, edit, hapus data mahasiswa
- **Manajemen Data Dosen** - Kelola data dosen pembimbing dan penguji
- **Monitoring Progress** - Pantau perkembangan tugas akhir mahasiswa
- **Pengaturan Jadwal Ujian** - Jadwalkan ujian tugas akhir
- **Manajemen Ujian** - Kelola jadwal, penguji, dan status ujian

**👨‍🏫 Dosen**

- **Review Pengajuan** - Evaluasi dan beri persetujuan judul tugas akhir
- **Manajemen Bimbingan** - Kelola jadwal bimbingan dengan mahasiswa
- **Pemberian Komentar** - Berikan feedback dan catatan bimbingan
- **Penilaian Ujian** - Input nilai ujian tugas akhir

**👨‍🎓 Mahasiswa**

- **Pengajuan Judul** - Ajukan judul tugas akhir beserta proposal
- **Pengelolaan Bimbingan** - Buat janji bimbingan dengan dosen
- **Upload Berkas** - Unggah proposal dan laporan tugas akhir
- **Monitoring Progress** - Lihat status pengajuan dan bimbingan
- **Jadwal Ujian** - Akses informasi jadwal ujian

**🛠 Persyaratan Sistem**

**Server Requirements**

- **Web Server**: Apache 2.4+ atau Nginx
- **PHP**: 7.4 atau yang lebih baru (dengan ekstensi berikut):
  - PDO MySQL
  - MBString
  - Fileinfo
  - GD (untuk manipulasi gambar)
  - JSON
- **Database**: MySQL 5.7+ atau MariaDB 10.2+
- **Space Storage**: Minimal 100MB (tergantung jumlah berkas)

**Client Requirements**

- **Browser**: Chrome 70+, Firefox 65+, Safari 12+, Edge 79+
- **JavaScript**: Harus diaktifkan
- **Resolution**: Minimal 1024x768 pixels

**📥 Instalasi**

**1\. Clone atau Download Repository**

git clone <https://github.com/ciptomx/sitama>

cd sitama

**2\. Setup Database**

mysql -u root -p

CREATE DATABASE sitama;

USE sitama;

SOURCE database/ta_management.sql;

**3\. Konfigurasi Environment**

Edit file includes/config.php:

define('DB_HOST', 'localhost');

define('DB_NAME', 'sita.a');

define('DB_USER', 'username_database');

define('DB_PASS', 'password_database');

define('BASE_URL', '<http://localhost/sitama>');

**4\. Setup Folder Uploads**

mkdir -p assets/uploads

chmod 755 assets/uploads

**5\. Testing Instalasi**

Akses aplikasi melalui browser:

<http://localhost/sitama>

**🗃 Struktur Database**

**Tabel Utama**

- **users** - Data pengguna sistem
- **mahasiswa** - Data mahasiswa (terkait dengan users)
- **dosen** - Data dosen (terkait dengan users)
- **tugas_akhir** - Data pengajuan tugas akhir
- **bimbingan** - Jadwal dan catatan bimbingan
- **ujian** - Data jadwal ujian
- **penguji** - Daftar penguji ujian
- **penilaian** - Hasil penilaian ujian
- **system_logs** - Log aktivitas sistem

**Relasi Database**

users ← mahasiswa

users ← dosen

mahasiswa → tugas_akhir → ujian → penguji

↓

bimbingan

ujian → penilaian

**🚀 Penggunaan**

**Login Pertama Kali**

Gunakan kredensial default:

- **Admin**: username admin, password password
- **Dosen**: username dosen, password password
- **Mahasiswa**: username mahasiswa, password password

**Untuk Admin**

1. **Kelola Data Mahasiswa**
    - Akses: Modules → Admin → Mahasiswa
    - Tambah mahasiswa baru dengan NIM unik
    - Edit status mahasiswa (Aktif/Cuti/Lulus)
2. **Kelola Data Dosen**
    - Akses: Modules → Admin → Dosen
    - Tambah dosen dengan NIDN unik
    - Atur peran dosen (Pembimbing/Penguji/Keduanya)
3. **Jadwalkan Ujian**
    - Akses: Modules → Admin → Ujian
    - Pilih mahasiswa yang sudah siap ujian
    - Tentukan tanggal, waktu, dan ruangan
    - Pilih minimal 2 penguji

**Untuk Dosen**

1. **Review Pengajuan**
    - Akses: Modules → Dosen → Pengajuan
    - Review judul dan proposal mahasiswa
    - Berikan persetujuan atau komentar
2. **Kelola Bimbingan**
    - Akses: Modules → Dosen → Bimbingan
    - Konfirmasi jadwal bimbingan
    - Berikan catatan dan feedback
3. **Input Penilaian**
    - Akses: Modules → Dosen → Penilaian
    - Beri nilai untuk presentasi, makalah, dan responsi
    - Berikan catatan untuk mahasiswa

**Untuk Mahasiswa**

1. **Ajukan Judul TA**
    - Akses: Modules → Mahasiswa → Pengajuan
    - Isi form pengajuan judul
    - Upload proposal dalam format PDF (maks. 2MB)
    - Pilih dosen pembimbing
2. **Jadwalkan Bimbingan**
    - Akses: Modules → Mahasiswa → Bimbingan
    - Buat janji minimal 2 hari sebelumnya
    - Tentukan topik yang akan dibahas
3. **Lihat Progress**
    - Pantau status pengajuan di dashboard
    - Lihat jadwal ujian ketika sudah ditentukan

**⚙ Konfigurasi**

**Konfigurasi Email**

Edit fungsi sendUjianCompletionEmail di update_ujian_status.php:

_// Ganti dengan konfigurasi email server Anda_

$to = $email;

$subject = "Notifikasi Ujian Tugas Akhir";

$headers = "From: <no-reply@unmuhpnk.ac.id>\\r\\n";

$headers .= "Content-Type: text/html; charset=UTF-8\\r\\n";

mail($to, $subject, $message, $headers);

**Konfigurasi Upload**

Edit di includes/functions.php:

_// Ubah ukuran maksimal file_

$maxSize = 5 \* 1024 \* 1024; _// 5MB_

_// Tambah format file yang diizinkan_

$allowedTypes = \['pdf', 'doc', 'docx'\];

**Konfigurasi Session**

Edit session settings di includes/config.php:

_// Keamanan session_

ini_set('session.cookie_httponly', 1);

ini_set('session.use_only_cookies', 1);

ini_set('session.cookie_secure', 1); _// HTTPS only_

**🔧 Troubleshooting**

**Masalah Umum dan Solusi**

1. **Error Database Connection**
2. _\# Periksa kredensial database di config.php_
3. _\# Pastikan MySQL service berjalan_
4. sudo service mysql start
5. **Error Upload File**
6. _\# Periksa permission folder uploads_
7. chmod 755 assets/uploads
8. _\# Periksa ukuran file maksimal di php.ini_
9. upload_max_filesize = 10M
10. post_max_size = 10M
11. **Halaman Tidak Ditemukan (404)**
12. _\# Aktifkan mod_rewrite Apache_
13. sudo a2enmod rewrite
14. sudo service apache2 restart
15. **Error Session**
16. _\# Periksa permission folder session_
17. ls -la /var/lib/php/sessions/
18. _\# Bersihkan session lama_
19. sudo rm -f /var/lib/php/sessions/sess_\*

**Debug Mode**

Aktifkan debug mode di includes/config.php:

_// Ubah ke false untuk production_

define('DEBUG_MODE', true);

if (DEBUG_MODE) {

ini_set('display_errors', 1);

error_reporting(E_ALL);

} else {

ini_set('display_errors', 0);

error_reporting(0);

}

**🤝 Kontribusi**

**Cara Berkontribusi**

1. Fork repository ini
2. Buat branch fitur baru (git checkout -b fitur-baru)
3. Commit perubahan (git commit -am 'Menambah fitur baru')
4. Push ke branch (git push origin fitur-baru)
5. Buat Pull Request

**Pedoman Coding**

- Ikuti PSR-12 coding standard
- Gunakan comment yang jelas untuk fungsi kompleks
- Test perubahan secara menyeluruh
- Update dokumentasi sesuai kebutuhan

**Testing**

_\# Run basic tests_

php -f test_connection.php

_\# Test email functionality_

php -f test_email.php

**📄 Lisensi**

Proyek ini dilisensikan di bawah Lisensi MIT - lihat file [LICENSE] untuk detail lengkapnya.

**📞 Support**

Untuk pertanyaan dan dukungan:

- **Email**: [sucipto@unmuhpnk.ac.id](mailto:sucipto@unmuhpnk.ac.id)
- **Issues**: [GitHub Issues](https://github.com/ciptomx/sitama)
- **Documentation**: [Wiki](https://github.com/ciptomx/sitama/blob/main/README.md)
